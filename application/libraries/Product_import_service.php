<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Lectura y normalizacion de inventarios de productos desde Excel.
 */
class Product_import_service
{
	const SHEET_NAME = 'Sheet1';
	const MAX_DATA_ROWS = 5000;

	/**
	 * Lee el archivo y devuelve filas normalizadas junto con sus errores.
	 *
	 * @param string $path
	 * @return array
	 */
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
				log_message('error', 'Error al leer inventario XLSX: ' . get_class($e) . ': ' . $e->getMessage());
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
			$result['errors'][] = 'No se encontraron los encabezados requeridos para productos. Revise que incluya: Producto, Cantidad disponible, Venta y Costo.';
			$spreadsheet->disconnectWorksheets();
			return $result;
		}

		$result['header_row'] = $header['row'];
		$highestRow = $sheet->getHighestDataRow();
		if ($highestRow - $header['row'] > self::MAX_DATA_ROWS) {
			$result['errors'][] = 'El archivo supera el límite de ' . self::MAX_DATA_ROWS . ' filas de productos.';
			$spreadsheet->disconnectWorksheets();
			return $result;
		}

		for ($rowNumber = $header['row'] + 1; $rowNumber <= $highestRow; $rowNumber++) {
			$productType = $this->cell_text($sheet, $header['columns']['product_type'], $rowNumber);

			if ($productType === '') {
				continue;
			}
			if ($this->is_total_label($productType)) {
				continue;
			}

			$row = array(
				'source_row' => $rowNumber,
				'product_type' => $productType,
				'laboratory' => isset($header['columns']['laboratory'])
					? $this->optional_text($sheet, $header['columns']['laboratory'], $rowNumber)
					: '',
				'name' => isset($header['columns']['name'])
					? $this->optional_text($sheet, $header['columns']['name'], $rowNumber)
					: '',
				'weight' => isset($header['columns']['weight'])
					? $this->optional_text($sheet, $header['columns']['weight'], $rowNumber)
					: '',
				'servings' => isset($header['columns']['servings'])
					? $this->optional_text($sheet, $header['columns']['servings'], $rowNumber)
					: '',
				'flavor' => isset($header['columns']['flavor'])
					? $this->optional_text($sheet, $header['columns']['flavor'], $rowNumber)
					: '',
				'stock_qty' => $this->parse_number($sheet, $header['columns']['stock_qty'], $rowNumber),
				'unit_price' => $this->parse_number($sheet, $header['columns']['unit_price'], $rowNumber),
				'cost_price' => $this->parse_number($sheet, $header['columns']['cost_price'], $rowNumber),
				'errors' => array(),
			);

			if ($row['name'] === '') {
				$row['name'] = $row['product_type'];
			}

			if ($row['stock_qty'] !== null) {
				$row['stock_qty'] = (int)round($row['stock_qty']);
			}

			$this->validate_row($row);
			$result['rows'][] = $row;
		}

		if (empty($result['rows'])) {
			$result['errors'][] = 'El archivo no contiene productos para importar.';
		}

		$spreadsheet->disconnectWorksheets();
		return $result;
	}

	/**
	 * Busca la hoja "Sheet1" si existe y trae encabezados, pero acepta
	 * cualquier hoja que contenga los encabezados válidos.
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

	/**
	 * Busca una fila que contenga los encabezados requeridos
	 * (sku, producto, cantidad, venta y costo). Los demás son opcionales.
	 */
	private function find_header($sheet)
	{
		$aliases = array(
			'product_type' => array('producto', 'tipo de producto', 'tipo producto', 'categoria'),
			'laboratory' => array('laboratorio'),
			'name' => array('nombre', 'nombre del producto', 'descripcion', 'producto2'),
			'weight' => array('peso'),
			'servings' => array('servidas', 'servicios'),
			'flavor' => array('sabor'),
			'stock_qty' => array('cantidad disponible', 'cantidad', 'sum of cantidad', 'sum of cantidad disponible', 'existencia', 'stock', 'inventario'),
			'unit_price' => array('venta', 'precio de venta', 'precio venta', 'precio'),
			'cost_price' => array('costo', 'costo unitario', 'precio de costo'),
		);
		$required = array('product_type', 'stock_qty', 'unit_price', 'cost_price');

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
				return array('row' => $row, 'columns' => $columns);
			}
		}

		return null;
	}

	private function validate_row(&$row)
	{
		if ($row['product_type'] === '') {
			$row['errors'][] = 'Producto es requerido.';
		}

		$numeric = array(
			'stock_qty' => 'Cantidad disponible',
			'unit_price' => 'Venta',
			'cost_price' => 'Costo',
		);
		foreach ($numeric as $field => $label) {
			if ($row[$field] === null) {
				$row['errors'][] = $label . ' debe ser numérico.';
			} elseif ($row[$field] < 0) {
				$row['errors'][] = $label . ' no puede ser negativo.';
			}
		}

		$limits = array(
			'product_type' => 100,
			'laboratory' => 120,
			'name' => 150,
			'weight' => 50,
			'servings' => 50,
			'flavor' => 120,
		);
		foreach ($limits as $field => $limit) {
			if (mb_strlen($row[$field]) > $limit) {
				$row['errors'][] = ucfirst(str_replace('_', ' ', $field)) . ' excede ' . $limit . ' caracteres.';
			}
		}
	}

	private function cell_text($sheet, $column, $row)
	{
		$cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column) . $row);
		$value = $cell->getFormattedValue();
		$value = preg_replace('/\s+/u', ' ', trim((string)$value));
		return $value;
	}

	private function optional_text($sheet, $column, $row)
	{
		$value = $this->cell_text($sheet, $column, $row);
		return $value === '0' ? '' : $value;
	}

	private function parse_number($sheet, $column, $row)
	{
		$cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column) . $row);
		$value = $cell->getCalculatedValue();
		if (is_int($value) || is_float($value)) {
			return (float)$value;
		}

		$value = trim((string)$value);
		if ($value === '') {
			return null;
		}
		$value = preg_replace('/[^0-9,\.\-]/', '', $value);
		if (strpos($value, ',') !== false && strpos($value, '.') === false) {
			$value = str_replace(',', '.', $value);
		} else {
			$value = str_replace(',', '', $value);
		}
		return is_numeric($value) ? (float)$value : null;
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
