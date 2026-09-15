<?php defined('BASEPATH') OR exit('No direct script access allowed');
$o = $order;
?>
<div class="container" style="max-width:900px;margin:0 auto;padding:24px;">
    <div class="print-header">
        <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="Logo">
        <div class="flex-grow-1">
            <div class="brand">SG<em>Mensajeria</em></div>
            <div class="meta"><?php echo html_escape(isset($settings['company_address']) ? $settings['company_address'] : ''); ?></div>
            <div class="meta">Tel: <?php echo html_escape(isset($settings['company_phone']) ? $settings['company_phone'] : ''); ?></div>
        </div>
        <div class="text-end">
            <div class="print-title">Comprobante de pedido</div>
            <div class="print-subtitle"><?php echo html_escape($o->order_number); ?></div>
        </div>
    </div>

    <table class="print-summary mb-4">
        <tr><td>Cliente</td><td><?php echo html_escape($o->customer_name); ?></td></tr>
        <tr><td>Teléfono</td><td><?php echo html_escape($o->customer_phone); ?><?php if ($o->customer_phone2): ?><br><small>Secundario: <?php echo html_escape($o->customer_phone2); ?></small><?php endif; ?></td></tr>
        <tr><td>Dirección</td><td><?php echo html_escape($o->delivery_address); ?></td></tr>
        <tr><td>Referencias</td><td><?php echo html_escape($o->delivery_reference ?: '—'); ?></td></tr>
        <tr><td>Bodega</td><td><?php echo html_escape(isset($o->warehouse_name) ? $o->warehouse_name : '—'); ?></td></tr>
        <tr><td>Fecha del pedido</td><td><?php echo fmt_datetime($o->created_at); ?></td></tr>
        <tr><td>Estado</td><td><?php echo html_escape(order_status_label($o->status)); ?></td></tr>
    </table>

    <table class="print-table mb-4">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($o->items as $item): ?>
                <tr>
                    <td><?php echo html_escape($item->item_name); ?><?php if ($item->item_sku): ?> <small>(<?php echo html_escape($item->item_sku); ?>)</small><?php endif; ?></td>
                    <td><?php echo number_format((int)$item->quantity); ?></td>
                    <td><?php echo money($item->unit_price); ?></td>
                    <td><?php echo money($item->line_total); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" style="text-align:right;">Subtotal</td><td><?php echo money($o->subtotal); ?></td></tr>
            <?php if ((float)$o->discount > 0): ?>
                <tr><td colspan="3" style="text-align:right;">Descuento</td><td>- <?php echo money($o->discount); ?></td></tr>
            <?php endif; ?>
            <tr><td colspan="3" style="text-align:right;">Total</td><td><?php echo money($o->total); ?></td></tr>
            <tr><td colspan="3" style="text-align:right;">Pagado</td><td><?php echo money($o->paid_amount); ?></td></tr>
            <tr><td colspan="3" style="text-align:right;">Saldo</td><td><?php echo money($o->balance_amount); ?></td></tr>
        </tfoot>
    </table>

    <?php if ($o->includes_gifts): ?>
        <p><strong>Regalías:</strong> <?php echo html_escape($o->gift_description ?: 'Incluidas'); ?></p>
    <?php endif; ?>

    <p class="print-subtitle">Generado el <?php echo date('d/m/Y H:i'); ?></p>
</div>
