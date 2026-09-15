<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statusLabels = array(
    'pendiente' => 'Pendiente de confirmar',
    'recibido' => 'Recibido',
    'entregado' => 'Entregado al administrador',
    'aprobado' => 'Aprobado',
);
$totalAmount = 0;
foreach ($rows as $row) {
    $totalAmount += (float)$row->amount;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111114; }
        h1 { font-size: 16px; margin: 0 0 4px; }
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
            <div class="brand">SG<em>Mensajeria</em> · Reporte de depósitos de efectivo</div>
            <div class="meta">Periodo: <?php echo fmt_date($filters['date_from']); ?> al <?php echo fmt_date($filters['date_to']); ?></div>
        </div>
        <div class="meta">Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>#</th><th>Fecha</th><th>Mensajero</th><th>Recibió</th><th>Entregado a</th>
                <th class="num">Rutas</th><th class="num">Monto</th><th>Estado</th><th>Visto bueno</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $d): ?>
                <tr>
                    <td><?php echo (int)$d->id; ?></td>
                    <td><?php echo fmt_datetime($d->created_at); ?></td>
                    <td><?php echo html_escape($d->courier_name); ?></td>
                    <td><?php echo html_escape($d->receiver_name); ?></td>
                    <td><?php echo $d->admin_receiver_user_id ? html_escape($d->admin_receiver_name) : '-'; ?></td>
                    <td class="num"><?php echo (int)$d->route_count; ?></td>
                    <td class="num"><?php echo number_format((float)$d->amount, 2); ?></td>
                    <td><?php echo html_escape(isset($statusLabels[$d->status]) ? $statusLabels[$d->status] : $d->status); ?></td>
                    <td><?php echo $d->confirmed_at ? html_escape($d->confirmed_by_name) . ' · ' . fmt_datetime($d->confirmed_at) : 'Pendiente'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot">
                <td colspan="5">Total</td>
                <td class="num"></td>
                <td class="num"><?php echo number_format($totalAmount, 2); ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="meta" style="margin-top:16px;">SGMensajeria · <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
</body>
</html>