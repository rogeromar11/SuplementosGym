<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111114; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 16px 0 6px; }
        .header { border-bottom: 3px solid #DC2626; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; }
        .brand { font-weight: 700; font-size: 13px; }
        .brand em { font-style: normal; color: #DC2626; }
        .meta { color: #52525B; font-size: 9px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #D4D4D8; padding: 5px 6px; text-align: left; }
        table.data th { background: #111114; color: #FFF; font-size: 9px; }
        table.data td { font-size: 9px; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">SG<em>Mensajeria</em> · Reporte de mensajeros</div>
            <div class="meta">Periodo: <?php echo fmt_date($filters['date_from']); ?> al <?php echo fmt_date($filters['date_to']); ?></div>
        </div>
        <div class="meta">Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Mensajero</th>
                <th class="num">Rutas</th>
                <th class="num">Pedidos</th>
                <th class="num">Entregados</th>
                <th class="num">No entregados</th>
                <th class="num">% Éxito</th>
                <th class="num">Esperado</th>
                <th class="num">Cobrado</th>
                <th class="num">Pendiente</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo html_escape($row->courier_name); ?></td>
                    <td class="num"><?php echo (int)$row->routes; ?></td>
                    <td class="num"><?php echo (int)$row->total_orders; ?></td>
                    <td class="num"><?php echo (int)$row->delivered; ?></td>
                    <td class="num"><?php echo (int)$row->not_delivered; ?></td>
                    <td class="num"><?php echo round((float)$row->success_rate, 1); ?>%</td>
                    <td class="num"><?php echo number_format((float)$row->expected_amount, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->collected_amount, 2); ?></td>
                    <td class="num"><?php echo number_format((float)$row->pending_amount, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="meta" style="margin-top:16px;">SGMensajeria · <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
</body>
</html>
