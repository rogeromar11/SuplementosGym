<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$product = $group->product;
$default = $group->default;
$flavors = $group->flavors;
$available = $group->available;
$multi = count($flavors) > 1;
$flavor_names = array();
$flavor_data = array();
foreach ($flavors as $f)
{
	$flavor_names[] = $f['flavor'];
	$flavor_data[] = array(
		'id'        => (int) $f['id'],
		'flavor'    => $f['flavor'],
		'available' => (bool) $f['available'],
		'price'     => store_price($f['product']->unit_price),
	);
}
$search_blob = implode(' ', array($product->name, $product->laboratory, $product->product_type, $product->weight, $product->servings, implode(' ', $flavor_names)));
$img = store_product_image($product);
$webp = store_product_webp($product);
$default_id = (int) $default->id;
?>
<article class="product-card reveal" data-search-item="<?php echo html_escape($search_blob); ?>">
  <div class="product-media">
    <?php if ( ! $available): ?>
      <span class="product-badge badge-out">Agotado</span>
    <?php elseif ((int) $product->featured === 1): ?>
      <span class="product-badge badge-featured">Destacado</span>
    <?php elseif (store_low_stock_notice_enabled() && (int) $default->stock_enabled === 1 && (int) $default->stock_qty < store_availability_min_stock()): ?>
      <span class="product-badge badge-low">Pocas unidades<?php echo store_stock_quantity_visible() ? ': ' . (int) $default->stock_qty : ''; ?></span>
    <?php endif; ?>
    <a href="<?php echo store_product_url($product); ?>" aria-label="<?php echo html_escape($product->name); ?>">
      <?php if ( ! empty($webp)): ?>
        <picture>
          <source srcset="<?php echo $webp; ?>" type="image/webp">
          <img src="<?php echo $img; ?>" alt="<?php echo html_escape($product->name); ?>" loading="lazy" decoding="async" width="240" height="180">
        </picture>
      <?php else: ?>
        <img src="<?php echo $img; ?>" alt="<?php echo html_escape($product->name); ?>" loading="lazy" decoding="async" width="240" height="180">
      <?php endif; ?>
    </a>
  </div>
  <div class="product-body">
    <?php if ( ! empty($product->laboratory)): ?>
      <span class="product-lab"><?php echo html_escape($product->laboratory); ?></span>
    <?php endif; ?>
    <h3 class="product-name"><a href="<?php echo store_product_url($product); ?>"><?php echo html_escape($product->name); ?></a></h3>
    <div class="product-meta">
      <span><?php echo html_escape(store_category_label(store_category($product->product_type))); ?></span>
      <?php if ( ! empty($product->weight)): ?><span><?php echo html_escape($product->weight); ?></span><?php endif; ?>
      <?php if ( ! empty($product->servings)): ?><span><?php echo html_escape($product->servings); ?></span><?php endif; ?>
    </div>

    <?php if ($multi): ?>
      <p class="product-flavors"><span class="flavor-label">Sabores:</span> <?php echo html_escape(implode(', ', $flavor_names)); ?></p>
    <?php elseif ( ! empty($product->flavor)): ?>
      <p class="product-flavors"><span class="flavor-label">Sabor:</span> <?php echo html_escape($product->flavor); ?></p>
    <?php endif; ?>

    <p class="product-desc"><?php echo html_escape(store_product_short_description($product, 80)); ?></p>
    <div class="product-foot">
      <span class="product-price" data-price><?php echo store_price($default->unit_price); ?></span>
      <div class="product-actions">
        <?php if ($available): ?>
          <button type="button" class="add-btn" data-add-to-cart
            data-product-id="<?php echo $default_id; ?>"
            data-product-name="<?php echo html_escape($product->name); ?>"
            <?php echo $multi ? 'data-flavor-picker="' . html_escape(json_encode($flavor_data)) . '"' : ''; ?>
            aria-label="Agregar <?php echo html_escape($product->name); ?> al carrito">
            <i class="bi bi-cart-plus" aria-hidden="true"></i>
          </button>
        <?php else: ?>
          <button type="button" class="btn-ghost btn-sm" disabled>Agotado</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</article>
