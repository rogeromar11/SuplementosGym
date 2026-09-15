<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Celda generica de columna en catalogo.
 * Soporta: value, badge, boolean, text, time.
 */
$val = isset($item->{$col['key']}) ? $item->{$col['key']} : '';
$type = isset($col['type']) ? $col['type'] : 'text';

switch ($type) {
    case 'badge':
        $label = isset($col['map']) && isset($col['map'][$val]) ? $col['map'][$val] : $val;
        $color = isset($col['colors']) && isset($col['colors'][$val]) ? $col['colors'][$val] : 'secondary';
        echo '<span class="badge text-bg-' . $color . '">' . html_escape($label) . '</span>';
        break;
    case 'boolean':
        $yes = isset($col['yes']) ? $col['yes'] : 'Si';
        $no = isset($col['no']) ? $col['no'] : 'No';
        echo $val ? '<span class="badge text-bg-success">' . html_escape($yes) . '</span>' : '<span class="badge text-bg-secondary">' . html_escape($no) . '</span>';
        break;
    case 'code':
        echo '<code>' . html_escape($val) . '</code>';
        break;
    case 'text':
    default:
        echo html_escape($val !== null && $val !== '' ? $val : '—');
}
