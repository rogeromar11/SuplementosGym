<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$available = store_product_available($product);
$stock = store_product_stock($product);
$wa = store_product_whatsapp_url($product);
$max = ($stock !== NULL && $stock > 0) ? $stock : 99;
?>

<section class="section">
  <div class="container-x">
    <nav aria-label="Ruta" class="muted" style="font-size:.85rem;">
      <a href="<?php echo base_url(); ?>">Inicio</a> /
      <a href="<?php echo base_url('productos'); ?>">Productos</a> /
      <a href="<?php echo base_url('productos?categoria=' . store_category($product->product_type)); ?>"><?php echo html_escape(store_category_label(store_category($product->product_type))); ?></a> /
      <span><?php echo html_escape($product->name); ?></span>
    </nav>

    <div class="pd-layout mt-4">
      <div class="pd-media">
        <img src="<?php echo store_product_image($product); ?>" alt="<?php echo html_escape($product->name); ?>" width="480" height="360">
      </div>

      <div>
        <?php if ( ! empty($product->laboratory)): ?>
          <span class="product-lab"><?php echo html_escape($product->laboratory); ?></span>
        <?php endif; ?>
        <h1 class="pd-title"><?php echo html_escape($product->name); ?></h1>
        <p class="muted mb-0"><?php echo html_escape(store_category_label(store_category($product->product_type))); ?></p>

        <div class="pd-price"><?php echo store_price($product->unit_price); ?></div>

        <p>
          <?php if ( ! $available): ?>
            <span class="status status-cancelado">Agotado</span>
          <?php elseif ($stock !== NULL && $stock > 0 && $stock <= 5): ?>
            <span class="status status-pendiente">Pocas unidades: <?php echo (int) $stock; ?></span>
          <?php elseif ($stock !== NULL): ?>
            <span class="status status-pagado">Disponible: <?php echo (int) $stock; ?></span>
          <?php else: ?>
            <span class="status status-pagado">Disponible</span>
          <?php endif; ?>
        </p>

        <div class="pd-specs">
          <?php if ( ! empty($product->weight)): ?><div class="pd-spec"><strong>Peso</strong><?php echo html_escape($product->weight); ?></div><?php endif; ?>
          <?php if ( ! empty($product->servings)): ?><div class="pd-spec"><strong>Porciones</strong><?php echo html_escape($product->servings); ?></div><?php endif; ?>
          <?php if ( ! empty($product->flavor)): ?><div class="pd-spec"><strong>Sabor</strong><?php echo html_escape($product->flavor); ?></div><?php endif; ?>
          <?php if ( ! empty($product->sku)): ?><div class="pd-spec"><strong>Codigo</strong><?php echo html_escape($product->sku); ?></div><?php endif; ?>
        </div>

        <p><?php echo nl2br(html_escape($product->store_description ?: $product->description)); ?></p>

        <?php if ($available): ?>
          <div class="pd-actions">
            <div class="qty-control" data-qty-control>
              <button type="button" data-delta="-1" aria-label="Disminuir cantidad">−</button>
              <input type="number" value="1" min="1" max="<?php echo (int) $max; ?>" data-qty-input data-product-id="<?php echo (int) $product->id; ?>" aria-label="Cantidad">
              <button type="button" data-delta="1" aria-label="Aumentar cantidad">+</button>
            </div>
            <button type="button" class="btn-brand" data-add-to-cart data-product-id="<?php echo (int) $product->id; ?>">
              <i class="bi bi-cart-plus" aria-hidden="true"></i> Agregar al carrito
            </button>
            <button type="button" class="btn-dark" data-add-to-cart data-buy-now data-product-id="<?php echo (int) $product->id; ?>">
              Comprar ahora
            </button>
            <?php if ( ! empty($wa)): ?>
              <a class="btn-wa" href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="pd-actions">
            <button type="button" class="btn-ghost" disabled>Agotado</button>
            <?php if ( ! empty($wa)): ?>
              <a class="btn-wa" href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Consultar disponibilidad</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php if ( ! empty($related)): ?>
<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <h2 class="section-title">Productos relacionados</h2>
    </div>
    <div class="product-grid">
      <?php foreach ($related as $item): ?>
        <?php $this->load->view('store/partials/product_card', array('product' => $item)); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
