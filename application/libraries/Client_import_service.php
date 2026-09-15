<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Lectura y normalizacion de clientes desde Excel.
 */
class Client_import_service
{
	const SHEET_NAME = 'Lista de Clientes';
	const MAX_DATA_ROWS = 5000;

	public function parse($path)
	{
		$result = array(
			'header_row' => null,
			'rows' => array(),
			'errors' => array(),
		);

		try {
			if (!is_readable($path)) {
				throw new \RuntimeException('El archivo temporal no se puede leer.');
			}
			if (!class_exists('ZipArchive')) {
				require_once APPPATH . 'libraries/Sgms_phar_zip_archive.php';
			}
			if (!class_exists('ZipArchive')) {
				throw new \RuntimeException('No hay un lector ZIP disponible en el servidor.');
			}
			$reader = IOFactory::createReader('Xlsx');
			$reader->setReadDataOnly(true);
			$reader->setReadEmptyCells(false);
			$spreadsheet = $reader->load($path);
		} catch (\Throwable $e) {
			if (function_exists('log_message')) {
				log_message('error', 'Error al leer clientes XLSX: ' . get_class($e) . ': ' . $e->getMessage());
			}
			if (strpos($e->getMessage(), 'lector ZIP') !== false) {
				$result['errors'][] = 'El servidor no dispone de soporte ZIP ni PharData para leer archivos .xlsx.';
			} elseif (strpos($e->getMessage(), 'temporal no se puede leer') !== false) {
				$result['errors'][] = 'El servidor no pudo leer el archivo temporal subido. Intente cargarlo nuevamente.';
			} else {
				$result['errors'][] = 'No fue posible abrir el archivo como XLSX. Vuelva a guardarlo desde Excel y cárguelo nuevamente.';
			}
			return $result;
		}

		$sheet = $this->find_sheet_with_header($spreadsheet);
		$header = $sheet ? $this->find_header($sheet) : null;
		if (!$header) {
			$result['errors'][] = 'No se encontraron los encabezados requeridos para clientes. Revise que incluya: Cliente, Celular, Zona, Dirección y Tipo de entrega.';
			$spreadsheet->disconnectWorksheets();
			return $result;
		}

		$result['header_row'] = $header['row'];
		$highestRow = $sheet->getHighestDataRow();
		if ($highestRow - $header['row'] > self::MAX_DATA_ROWS) {
			$result['errors'][] = 'El archivo supera el límite de ' . self::MAX_DATA_ROWS . ' filas de clientes.';
			$spreadsheet->disconnectWorksheets();
			return $result;
		}

		for ($rowNumber = $header['row'] + 1; $rowNumber <= $highestRow; $rowNumber++) {
			$name = $this->cell_text($sheet, $header['columns']['name'], $rowNumber);
			if ($name === '') {
				continue;
			}
			if ($this->is_total_label($name)) {
				continue;
			}

			$row = array(
				'source_row' => $rowNumber,
				'name' => $name,
				'phone' => $this->cell_text($sheet, $header['columns']['phone'], $rowNumber),
				'phone2' => isset($header['columns']['phone2'])
					? $this->cell_text($sheet, $header['columns']['phone2'], $rowNumber)
					: '',
				'zone' => $this->cell_text($sheet, $header['columns']['zone'], $rowNumber),
				'address' => $this->cell_text($sheet, $header['columns']['address'], $rowNumber),
				'delivery_type' => $this->cell_text($sheet, $header['columns']['delivery_type'], $rowNumber),
				'notes' => isset($header['columns']['notes'])
					? $this->cell_text($sheet, $header['columns']['notes'], $rowNumber)
					: '',
				'errors' => array(),
			);

			$this->validate_row($row);
			$result['rows'][] = $row;
		}

		if (empty($result['rows'])) {
			$result['errors'][] = 'El archivo no contiene clientes para importar.';
		}

		$spreadsheet->disconnectWorksheets();
		return $result;
	}

	private function find_header($sheet)
	{
		$aliases = array(
			'name' => array('cliente', 'nombre', 'nombre del cliente'),
			'phone' => array('numero cel', 'numero celular', 'num celular', 'celular', 'telefono', 'telefono celular', 'telefono movil', 'tel'),
			'phone2' => array('celular secundario', 'telefono secundario', 'telefono 2', 'celular 2', 'otro telefono', 'otro celular', 'segundo telefono', 'segundo celular'),
			'zone' => array('zona'),
			'address' => array('direccion'),
			'delivery_type' => array('tipo de entrega', 'tipo entrega', 'tipo'),
			'notes' => array('notas', 'nota', 'observaciones', 'comentarios'),
		);
		$required = array('name', 'phone', 'zone', 'address', 'delivery_type');
		$highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
		$lastHeaderCandidate = min(50, $sheet->getHighestDataRow());

		for ($row = 1; $row <= $lastHeaderCandidate; $row++) {
			$columns = array();
			for ($col = 1; $col <= $highestColumn; $col++) {
				$value = $this->normalize_header($this->cell_text($sheet, $col, $row));
				if ($value === '') {
					continue;
				}
				foreach ($aliases as $field => $fieldAliases) {
					if (!isset($columns[$field]) && in_array($value, $fieldAliases, true)) {
						$columns[$field] = $col;
						break;
					}
				}
			}

			if (count(array_intersect($required, array_keys($columns))) === count($required)) {
				// El archivo proporcionado usa una columna G sin titulo para observaciones.
				if (!isset($columns['notes'])) {
					$candidate = $columns['delivery_type'] + 1;
					if ($candidate <= $highestColumn && $this->cell_text($sheet, $candidate, $row) === '') {
						$columns['notes'] = $candidate;
					}
				}
				return array('row' => $row, 'columns' => $columns);
			}
		}

		return null;
	}

	/**
	 * Acepta "Lista de Clientes" cuando existe, pero también cualquier hoja
	 * que contenga los encabezados válidos.
	 */
	private function find_sheet_with_header($spreadsheet)
	{
		$preferred = $spreadsheet->getSheetByName(self::SHEET_NAME);
		if ($preferred && $this->find_header($preferred)) {
			return $preferred;
		}
		foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
			if ($this->find_header($sheet)) {
				return $sheet;
			}
		}
		return null;
	}

	private function validate_row(&$row)
	{
		if ($row['name'] === '') {
			$row['errors'][] = 'Cliente es requerido.';
		}

		$limits = array(
			'name' => 150,
			'phone' => 30,
			'phone2' => 30,
			'zone' => 100,
			'delivery_type' => 50,
		);
		$labels = array(
			'name' => 'Cliente',
			'phone' => 'Número celular',
			'phone2' => 'Celular secundario',
			'zone' => 'Zona',
			'delivery_type' => 'Tipo de entrega',
		);
		foreach ($limits as $field => $limit) {
			if (mb_strlen($row[$field], 'UTF-8') > $limit) {
				$row['errors'][] = $labels[$field] . ' excede ' . $limit . ' caracteres.';
			}
		}
	}

	private function cell_text($sheet, $column, $row)
	{
		$cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column) . $row);
		return preg_replace('/\s+/u', ' ', trim((string)$cell->getFormattedValue()));
	}

	private function normalize_header($value)
	{
		$value = mb_strtolower(trim((string)$value), 'UTF-8');
		$value = strtr($value, array(
			'a' => 'a', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
			'ü' => 'u', 'ñ' => 'n',
		));
		$value = preg_replace('/[^a-z0-9]+/u', ' ', $value);
		return trim(preg_replace('/\s+/', ' ', $value));
	}

	private function is_total_label($value)
	{
		$value = $this->normalize_header($value);
		return strpos($value, 'total') === 0 || strpos($value, 'subtotal') === 0;
	}
}
