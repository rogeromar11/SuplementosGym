<?php defined('BASEPATH') OR exit('No direct script access allowed');
$totals = array('orders' => 0, 'sold' => 0, 'collected' => 0, 'balance' => 0);
foreach ($rows as $row) {
    $totals['orders']++;
    if ($row->status !== 'cancelado') {
        $totals['sold'] += (float)$row->total;
        $totals['collected'] += (float)$row->paid_amount;
        $totals['balance'] += (float)$row->balance_amount;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111114; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .header { border-bottom: 3px solid #DC2626; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; }
        .brand { font-weight: 700; font-size: 13px; }
        .brand em { font-style: normal; color: #DC2626; }
        .meta { color: #52525B; font-size: 8px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #D4D4D8; padding: 4px 5px; text-align: left; }
        table.data th { background: #111114; color: #FFF; font-size: 8px; }
        table.data td { font-size: 8px; }
        .num { text-align: right; }
        .resumen { margin-top: 12px; font-size: 9px; }
        .resumen strong { color: #DC2626; }
        .filtros { color: #52525B; font-size: 8px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">SG<em>Mensajeria</em> · Reporte de pedidos</div>
            <div class="meta">Periodo: <?php echo fmt_date($filters['date_from']); ?> al <?php echo fmt_date($filters['date_to']); ?></div>
            <div class="filtros">
                <?php
                $labels = array();
                if (!empty($filters['status']) && isset($statuses[$filters['status']])) { $labels[] = 'Estado: ' . $statuses[$filters['status']]; }
                if (!empty($filters['origin'])) { $labels[] = 'Origen: ' . ($filters['origin'] === 'web' ? 'Web' : 'Manual'); }
                if (!empty($filters['search'])) { $labels[] = 'Búsqueda: ' . $filters['search']; }
                echo html_escape(implode(' · ', $labels));
                ?>
            </div>
        </div>
        <div class="meta">Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Origen</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Zona</th>
                <th>Bodega</th>
                <th>Vendedor</th>
                <th>Forma de pago</th>
                <th>Estado</th>
                <th class="num">Total</th>
                <th class="num">Pagado</th>
                <th class="num">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo html_escape($row->order_number); ?></td>
                    <td><?php echo fmt_datetime($row->created_at); ?></td>
                    <td><?php echo $row->origin === 'web' ? 'Web' : 'Manual'; ?></td>
                    <td><?php echo html_escape($row->customer_name); ?></td>
                    <td><?php echo html_escape($row->customer_phone); ?></td>
                    <td><?php echo html_escape($row->delivery_zone); ?></td>
                    <td><?php echo html_escape($row->warehouse_name); ?></td>
                    <td><?php echo html_escape(trim((string)$row->seller_name)); ?></td>
                    <td><?php echo html_escape($row->payment_name); ?></td>
                    <td><?php echo order_status_label($row->status); ?></td>
                    <td class="num"><?php echo number_format((float)$row->total, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->paid_amount, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->balance_amount, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="resumen">
        <strong><?php echo (int)$totals['orders']; ?></strong> pedidos ·
        Total vendido: <strong><?php echo number_format($totals['sold'], 2); ?></strong> ·
        Cobrado: <strong><?php echo number_format($totals['collected'], 2); ?></strong> ·
        Saldo pendiente: <strong><?php echo number_format($totals['balance'], 2); ?></strong>
    </div>

    <div class="meta" style="margin-top:16px;">SGMensajeria · <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
</body>
</html>
