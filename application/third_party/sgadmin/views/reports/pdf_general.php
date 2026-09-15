<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totals = array('orders' => 0, 'delivered' => 0, 'not_delivered' => 0, 'expected' => 0, 'collected' => 0);
foreach ($rows as $row) {
    $totals['orders'] += (int)$row->total_orders;
    $totals['delivered'] += (int)$row->delivered;
    $totals['not_delivered'] += (int)$row->not_delivered;
    $totals['expected'] += (float)$row->expected_amount;
    $totals['collected'] += (float)$row->collected_amount;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111114; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 16px 0 6px; color: #111114; }
        .header { border-bottom: 3px solid #DC2626; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; }
        .brand { font-weight: 700; font-size: 13px; }
        .brand em { font-style: normal; color: #DC2626; }
        .meta { color: #52525B; font-size: 9px; }
        .summary td { padding: 2px 10px 2px 0; font-size: 10px; }
        .summary td:last-child { font-weight: 600; }
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
            <div class="brand">SG<em>Mensajeria</em> · Reporte general de rutas</div>
            <div class="meta">Periodo: <?php echo fmt_date($filters['date_from']); ?> al <?php echo fmt_date($filters['date_to']); ?></div>
        </div>
        <div class="meta">Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <table class="summary">
        <tr><td>Total de rutas</td><td><?php echo count($rows); ?></td><td>Pedidos</td><td><?php echo $totals['orders']; ?></td></tr>
        <tr><td>Entregados</td><td><?php echo $totals['delivered']; ?></td><td>No entregados</td><td><?php echo $totals['not_delivered']; ?></td></tr>
        <tr><td>Monto esperado</td><td><?php echo money($totals['expected']); ?></td><td>Monto cobrado</td><td><?php echo money($totals['collected']); ?></td></tr>
    </table>

    <h2>Detalle por ruta</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th><th>Ruta</th><th>Turno</th><th>Bodega</th><th>Mensajero</th>
                <th class="num">Ped.</th><th class="num">Ent.</th><th class="num">No ent.</th><th class="num">Pend.</th>
                <th class="num">Esperado</th><th class="num">Cobrado</th><th class="num">Pendiente</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo fmt_date($row->route_date); ?></td>
                    <td><?php echo html_escape($row->route_number); ?></td>
                    <td><?php echo html_escape($row->shift_name); ?></td>
                    <td><?php echo html_escape($row->warehouse_name); ?></td>
                    <td><?php echo html_escape($row->courier_name); ?></td>
                    <td class="num"><?php echo (int)$row->total_orders; ?></td>
                    <td class="num"><?php echo (int)$row->delivered; ?></td>
                    <td class="num"><?php echo (int)$row->not_delivered; ?></td>
                    <td class="num"><?php echo (int)$row->pending; ?></td>
                    <td class="num"><?php echo number_format((float)$row->expected_amount, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->collected_amount, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->pending_amount, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot">
                <td colspan="5">Totales</td>
                <td class="num"><?php echo $totals['orders']; ?></td>
                <td class="num"><?php echo $totals['delivered']; ?></td>
                <td class="num"><?php echo $totals['not_delivered']; ?></td>
                <td class="num"></td>
                <td class="num"><?php echo number_format($totals['expected'], 2); ?></td>
                <td class="num"><?php echo number_format($totals['collected'], 2); ?></td>
                <td class="num"></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($breakdown)): ?>
    <h2>Resumen por forma de pago</h2>
    <table class="data">
        <thead><tr><th>Forma de pago</th><th class="num">Pagos</th><th class="num">Monto</th></tr></thead>
        <tbody>
            <?php foreach ($breakdown as $pb): ?>
                <tr>
                    <td><?php echo html_escape($pb->payment_name); ?></td>
                    <td class="num"><?php echo (int)$pb->total_payments; ?></td>
                    <td class="num"><?php echo number_format((float)$pb->total, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="meta" style="margin-top:16px;">SGMensajeria · <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
</body>
</html>
