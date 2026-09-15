<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Adaptador de solo lectura para XLSX cuando ext-zip no esta disponible.
 * Implementa la porcion de ZipArchive utilizada por PhpSpreadsheet Reader.
 */
class SgmsPharZipArchive
{
	const FL_NOCASE = 1;
	const CHECKCONS = 4;

	/** @var PharData|null */
	private $archive;

	/** @var string|null */
	private $temporaryPath;

	/** @var array|null */
	private $caseInsensitiveNames;

	public function open($filename, $flags = 0)
	{
		$this->close();
		if (!class_exists('PharData') || !is_readable($filename)) {
			return false;
		}

		$base = tempnam(sys_get_temp_dir(), 'sgms_xlsx_');
		if ($base === false) {
			return false;
		}
		@unlink($base);
		$this->temporaryPath = $base . '.zip';
		if (!copy($filename, $this->temporaryPath)) {
			$this->cleanupTemporaryFile();
			return false;
		}

		try {
			$this->archive = new PharData($this->temporaryPath);
			return true;
		} catch (\Throwable $e) {
			$this->archive = null;
			$this->cleanupTemporaryFile();
			return false;
		}
	}

	public function close()
	{
		$this->archive = null;
		$this->caseInsensitiveNames = null;
		$this->cleanupTemporaryFile();
		return true;
	}

	public function locateName($name, $flags = 0)
	{
		$resolved = $this->resolveName($name, ($flags & self::FL_NOCASE) === self::FL_NOCASE);
		return $resolved === null ? false : 0;
	}

	public function getFromName($name, $length = 0, $flags = 0)
	{
		$resolved = $this->resolveName($name, ($flags & self::FL_NOCASE) === self::FL_NOCASE);
		if ($resolved === null || !$this->archive) {
			return false;
		}

		try {
			$entry = $this->archive[$resolved];
			// Evita inflar accidentalmente una entrada desproporcionada.
			if ($entry->getSize() > 64 * 1024 * 1024) {
				return false;
			}
			$content = $entry->getContent();
			return $length > 0 ? substr($content, 0, $length) : $content;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function __destruct()
	{
		$this->close();
	}

	private function resolveName($name, $ignoreCase)
	{
		if (!$this->archive) {
			return null;
		}
		$name = ltrim(str_replace('\\', '/', (string)$name), '/');
		if ($name === '' || strpos($name, '../') !== false) {
			return null;
		}
		if (isset($this->archive[$name])) {
			return $name;
		}
		if (!$ignoreCase) {
			return null;
		}

		if ($this->caseInsensitiveNames === null) {
			$this->caseInsensitiveNames = array();
			$iterator = new RecursiveIteratorIterator($this->archive);
			foreach ($iterator as $entry) {
				if ($entry->isDir()) {
					continue;
				}
				$entryName = str_replace('\\', '/', $iterator->getSubPathName());
				$this->caseInsensitiveNames[strtolower($entryName)] = $entryName;
			}
		}
		$key = strtolower($name);
		return isset($this->caseInsensitiveNames[$key]) ? $this->caseInsensitiveNames[$key] : null;
	}

	private function cleanupTemporaryFile()
	{
		if ($this->temporaryPath && is_file($this->temporaryPath)) {
			@unlink($this->temporaryPath);
		}
		$this->temporaryPath = null;
	}
}

if (!class_exists('ZipArchive', false)) {
	class_alias('SgmsPharZipArchive', 'ZipArchive');
}
