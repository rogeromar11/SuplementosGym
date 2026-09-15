<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * Servicio de generacion de Excel usando PhpSpreadsheet.
 */
class Excel_service
{
	/**
	 * Genera un Excel con una hoja de datos y lo descarga.
	 *
	 * @param string $filename
	 * @param array $headers Lista de encabezados
	 * @param array $rows Filas de datos (arreglos con los mismos indices de headers)
	 * @param array $options ['title' => string, 'sheet_name' => string, 'summary' => array]
	 */
	public function export($filename, $headers, $rows, $options = array())
	{
		if (function_exists('ini_set')) {
			@ini_set('memory_limit', '256M');
		}
		if (function_exists('set_time_limit')) {
			@set_time_limit(120);
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle(isset($options['sheet_name']) ? mb_substr($options['sheet_name'], 0, 31) : 'Datos');
		$this->build_sheet($sheet, $headers, $rows, isset($options['title']) ? $options['title'] : null, isset($options['summary']) ? $options['summary'] : null);

		// Hojas adicionales opcionales: [['name','headers','rows','title','summary'], ...]
		if (!empty($options['extra_sheets'])) {
			foreach ($options['extra_sheets'] as $extra) {
				$extraSheet = $spreadsheet->createSheet();
				$extraSheet->setTitle(mb_substr($extra['name'], 0, 31));
				$this->build_sheet($extraSheet, $extra['headers'], $extra['rows'], isset($extra['title']) ? $extra['title'] : null, isset($extra['summary']) ? $extra['summary'] : null);
			}
		}

		$writer = new Xlsx($spreadsheet);
		$this->send_headers($filename);
		$writer->save('php://output');
		exit;
	}

	/**
	 * Construye una hoja con encabezados estilizados, datos, autofiltro,
	 * anchos automaticos y fila congelada.
	 *
	 * @param object $sheet Hoja de PhpSpreadsheet
	 * @param array $headers Encabezados (clave => etiqueta)
	 * @param array $rows Filas (arreglos con las mismas claves de headers)
	 * @param string|null $title Titulo opcional
	 * @param array|null $summary Pares etiqueta => valor opcionales
	 */
	private function build_sheet($sheet, $headers, $rows, $title = null, $summary = null)
	{
		$rowIdx = 1;

		if ($title) {
			$sheet->setCellValue('A1', $title);
			$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
			$rowIdx = 2;
		}

		if ($summary) {
			foreach ($summary as $label => $value) {
				$sheet->setCellValue('A' . $rowIdx, $label);
				$sheet->setCellValue('B' . $rowIdx, $value);
				$rowIdx++;
			}
			$rowIdx++;
		}

		$headerRow = $rowIdx;
		$col = 1;
		foreach ($headers as $header) {
			$sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $headerRow, $header);
		}

		$lastCol = count($headers);
		$lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);

		foreach (range('A', $lastColLetter) as $letter) {
			$sheet->getStyle($letter . $headerRow)->getFill()
				->setFillType(Fill::FILL_SOLID)
				->getStartColor()->setRGB('DC2626');
			$sheet->getStyle($letter . $headerRow)->getFont()->getColor()->setRGB('FFFFFF');
			$sheet->getStyle($letter . $headerRow)->getFont()->setBold(true);
		}

		$dataStart = $headerRow + 1;
		foreach ($rows as $r => $row) {
			$col = 1;
			foreach ($headers as $key => $header) {
				$value = isset($row[$key]) ? $row[$key] : '';
				$sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . ($dataStart + $r), $value);
			}
		}

		if (!empty($rows)) {
			$sheet->setAutoFilter($lastColLetter . $headerRow . ':' . $lastColLetter . ($dataStart + count($rows) - 1));
		}
		foreach (range('A', $lastColLetter) as $letter) {
			$sheet->getColumnDimension($letter)->setAutoSize(true);
		}

		$sheet->freezePane('A' . ($headerRow + 1));
	}

	/**
	 * Genera una plantilla descargable para importacion con una hoja de
	 * ejemplo y una hoja de instrucciones.
	 *
	 * @param string $filename
	 * @param string $sheetName
	 * @param array $columns Lista de columnas: ['header' => string, 'required' => bool, 'example' => mixed, 'hint' => string]
	 * @return void
	 */
	public function import_template($filename, $sheetName, $columns)
	{
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle(mb_substr($sheetName, 0, 31));

		$lastCol = count($columns);
		$lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);

		// Encabezados
		foreach ($columns as $idx => $col) {
			$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . '1';
			$sheet->setCellValue($cell, $col['header']);
			$sheet->getStyle($cell)->getFill()
				->setFillType(Fill::FILL_SOLID)
				->getStartColor()->setRGB('1F4E79');
			$sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
			$sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		}

		// Fila de ejemplo
		foreach ($columns as $idx => $col) {
			$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1) . '2';
			$sheet->setCellValue($cell, isset($col['example']) ? $col['example'] : '');
			$sheet->getStyle($cell)->getFill()
				->setFillType(Fill::FILL_SOLID)
				->getStartColor()->setRGB('FFF4E0');
		}

		foreach ($columns as $idx => $col) {
			$sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1))->setAutoSize(true);
		}
		$sheet->setAutoFilter($lastColLetter . '1:' . $lastColLetter . '2');
		$sheet->freezePane('A2');

		// Hoja de instrucciones
		$instr = $spreadsheet->createSheet();
		$instr->setTitle('Instrucciones');
		$instr->setCellValue('A1', 'Cómo llenar el archivo');
		$instr->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('1F4E79');

		$required = array();
		foreach ($columns as $col) {
			if (!empty($col['required'])) {
				$required[] = $col['header'];
			}
		}
		$lines = array(
			'1- Use la hoja "' . mb_substr($sheetName, 0, 31) . '" para escribir sus datos.',
			'2- No modifique ni borre la fila 1 (encabezados).',
			'3- Columnas requeridas: ' . implode(', ', $required) . '.',
			'4- La fila 2 es solo un ejemplo; cámbiela por sus datos reales.',
			'5- Los valores numéricos (cantidades y precios) no deben llevar moneda.',
			'6- El sistema muestra una vista previa antes de guardar.',
		);
		$row = 3;
		foreach ($lines as $i => $line) {
			$instr->setCellValue('A' . $row++, ($i + 1) . ') ' . $line);
		}

		$writer = new Xlsx($spreadsheet);
		$this->send_headers($filename);
		$writer->save('php://output');
		exit;
	}

	/**
	 * Envia cabeceras HTTP para descarga de Excel.
	 *
	 * @param string $filename
	 */
	private function send_headers($filename)
	{
		$ci =& get_instance();
		$ci->load->helper('file');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
	}
}
