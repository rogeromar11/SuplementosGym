<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totalVentas = 0;
$totalVendido = 0.0;
foreach ($daily as $row) {
    $totalVentas += (int)$row->ventas;
    $totalVendido += (float)$row->total_vendido;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111114; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        .header { border-bottom: 3px solid #DC2626; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; }
        .brand { font-weight: 700; font-size: 13px; }
        .brand em { font-style: normal; color: #DC2626; }
        .meta { color: #52525B; font-size: 9px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #D4D4D8; padding: 5px 6px; text-align: left; }
        table.data th { background: #111114; color: #FFF; font-size: 9px; }
        table.data td { font-size: 9px; }
        .num { text-align: right; }
        .tfoot td { font-weight: 700; background: #F4F4F5; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">SG<em>Mensajeria</em> · Reporte de ventas por vendedor</div>
            <div class="meta">Periodo: <?php echo fmt_date($filters['date_from']); ?> al <?php echo fmt_date($filters['date_to']); ?></div>
        </div>
        <div class="meta">Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <h2>Resumen por vendedor</h2>
    <table class="data">
        <thead>
            <tr><th>Vendedor</th><th class="num">Ventas</th><th class="num">% de Ventas</th><th class="num">Total vendido</th><th class="num">% del Monto</th><th class="num">Ticket promedio</th><th class="num">Cobrado</th><th class="num">Pendiente</th><th class="num">Entregados</th><th class="num">Cancelados</th></tr>
        </thead>
        <tbody>
            <?php
            $grandTotal = 0.0;
            $grandVentas = 0;
            foreach ($summary as $row) {
                $grandTotal += (float)$row->total_vendido;
                $grandVentas += (int)$row->ventas;
            }
            foreach ($summary as $row): ?>
                <?php
                $avg = (int)$row->ventas > 0 ? round((float)$row->total_vendido / (int)$row->ventas, 2) : 0;
                $share = $grandTotal > 0 ? round(((float)$row->total_vendido / $grandTotal) * 100, 1) : 0;
                $shareVentas = $grandVentas > 0 ? round(((int)$row->ventas / $grandVentas) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo html_escape($row->seller_name ?: '-'); ?></td>
                    <td class="num"><?php echo (int)$row->ventas; ?></td>
                    <td class="num"><?php echo $shareVentas; ?>%</td>
                    <td class="num"><?php echo number_format((float)$row->total_vendido, 2); ?></td>
                    <td class="num"><?php echo $share; ?>%</td>
                    <td class="num"><?php echo number_format($avg, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->total_cobrado, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->pendiente_cobro, 2); ?></td>
                    <td class="num"><?php echo (int)$row->entregados; ?></td>
                    <td class="num"><?php echo (int)$row->cancelados; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Detalle por día y vendedor</h2>
    <table class="data">
        <thead>
            <tr><th>Fecha</th><th>Vendedor</th><th class="num">Ventas</th><th class="num">% de Ventas (día)</th><th class="num">Total vendido</th><th class="num">% del Monto (día)</th><th class="num">Cobrado</th><th class="num">Pendiente</th><th class="num">Entregados</th><th class="num">Cancelados</th></tr>
        </thead>
        <tbody>
            <?php
            $dayTotals = array();
            foreach ($daily as $d) {
                $key = (string)$d->sale_date;
                if (!isset($dayTotals[$key])) {
                    $dayTotals[$key] = array('ventas' => 0, 'monto' => 0.0);
                }
                $dayTotals[$key]['ventas'] += (int)$d->ventas;
                $dayTotals[$key]['monto'] += (float)$d->total_vendido;
            }
            foreach ($daily as $d): ?>
                <?php
                $day = $dayTotals[(string)$d->sale_date];
                $shareVentasDia = $day['ventas'] > 0 ? round(((int)$d->ventas / $day['ventas']) * 100, 1) : 0;
                $shareMontoDia = $day['monto'] > 0 ? round(((float)$d->total_vendido / $day['monto']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo fmt_date($d->sale_date); ?></td>
                    <td><?php echo html_escape($d->seller_name ?: '-'); ?></td>
                    <td class="num"><?php echo (int)$d->ventas; ?></td>
                    <td class="num"><?php echo $shareVentasDia; ?>%</td>
                    <td class="num"><?php echo number_format((float)$d->total_vendido, 2); ?></td>
                    <td class="num"><?php echo $shareMontoDia; ?>%</td>
                    <td class="num"><?php echo number_format((float)$d->total_cobrado, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$d->pendiente_cobro, 2); ?></td>
                    <td class="num"><?php echo (int)$d->entregados; ?></td>
                    <td class="num"><?php echo (int)$d->cancelados; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot">
                <td colspan="2">Total</td>
                <td class="num"><?php echo $totalVentas; ?></td>
                <td colspan="1"></td>
                <td class="num"><?php echo number_format($totalVendido, 2); ?></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <div class="meta" style="margin-top:16px;">SGMensajeria · <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
</body>
</html>