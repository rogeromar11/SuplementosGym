<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$has_products = ! empty($products);
$country_total = isset($country_total) ? (int) $country_total : 0;
$is_search = ! empty($filters['search']);
$is_category = ! empty($filters['category']);
?>

<section class="section" style="padding-bottom:1.5rem;">
  <div class="container-x">
    <nav aria-label="Ruta" class="muted" style="font-size:.85rem;">
      <a href="<?php echo base_url(); ?>">Inicio</a> / <span>Productos</span>
    </nav>
    <div class="section-head" style="margin-top:.75rem; margin-bottom:0;">
      <div>
        <span class="section-eyebrow">Catalogo</span>
        <h1 class="section-title">Productos</h1>
        <p class="section-subtitle">Catalogo disponible en <?php echo html_escape($current_country->name); ?>.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <?php if ($country_total === 0): ?>
      <div class="empty-state">
        <i class="bi bi-geo" aria-hidden="true"></i>
        <h3>Actualmente no hay productos disponibles para este pais.</h3>
        <p>Estamos trabajando para ampliar nuestro catalogo. Vuelve pronto.</p>
        <a class="btn-ghost mt-3" href="<?php echo base_url('contacto'); ?>">Contactar</a>
      </div>
    <?php else: ?>
      <div class="catalog-layout catalog-layout--right">
        <div class="catalog-main">
          <div class="catalog-toolbar">
            <span class="muted"><strong><?php echo count($products); ?></strong> producto(s)</span>
            <label class="muted" style="display:flex; align-items:center; gap:.5rem;">
              Ordenar por
              <select class="toolbar-select" onchange="var f=document.getElementById('catalogFilters'); f.orden.value=this.value; f.submit();" aria-label="Ordenar productos">
                <option value="relevance"<?php echo $filters['sort'] === 'relevance' ? ' selected' : ''; ?>>Relevancia</option>
                <option value="name"<?php echo $filters['sort'] === 'name' ? ' selected' : ''; ?>>Nombre</option>
                <option value="price_asc"<?php echo $filters['sort'] === 'price_asc' ? ' selected' : ''; ?>>Precio: menor a mayor</option>
                <option value="price_desc"<?php echo $filters['sort'] === 'price_desc' ? ' selected' : ''; ?>>Precio: mayor a menor</option>
              </select>
            </label>
          </div>

          <?php if ($has_products): ?>
            <div class="product-grid" data-product-grid>
              <?php foreach ($products as $product): ?>
                <?php $this->load->view('store/partials/product_card', array('product' => $product)); ?>
              <?php endforeach; ?>
            </div>
            <div class="empty-state d-none" data-search-empty style="margin-top:1rem;">
              <i class="bi bi-search" aria-hidden="true"></i>
              <h3>No encontramos productos que coincidan con tu busqueda.</h3>
              <p>Prueba con otro nombre, categoria o laboratorio.</p>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="bi bi-search" aria-hidden="true"></i>
              <?php if ($is_category): ?>
                <h3>No hay productos disponibles en esta categoria.</h3>
              <?php elseif ($is_search): ?>
                <h3>No encontramos productos que coincidan con tu busqueda.</h3>
                <p>Prueba con otro nombre, categoria o laboratorio.</p>
              <?php else: ?>
                <h3>No hay productos que coincidan con los filtros seleccionados.</h3>
              <?php endif; ?>
              <a class="btn-ghost mt-3" href="<?php echo base_url('productos'); ?>">Ver todos los productos</a>
            </div>
          <?php endif; ?>
        </div>

        <aside class="filter-panel catalog-aside" aria-label="Filtros de productos">
          <div class="filter-head">
            <h4 class="filter-title" style="margin:0;">Filtrar</h4>
            <a href="<?php echo base_url('productos'); ?>" class="filter-clear">Limpiar</a>
          </div>
          <form method="get" action="<?php echo base_url('productos'); ?>" id="catalogFilters">
            <div class="filter-group">
              <h4 class="filter-title">Buscar</h4>
              <input type="search" class="form-control" name="q" value="<?php echo html_escape($filters['search']); ?>" placeholder="Nombre, laboratorio o categoria" data-live-search aria-label="Buscar productos">
            </div>
            <div class="filter-group">
              <h4 class="filter-title">Categoria</h4>
              <select class="form-control" name="categoria" aria-label="Categoria">
                <option value="">Todas</option>
                <?php foreach ($categories as $key => $label): ?>
                  <option value="<?php echo html_escape($key); ?>"<?php echo $filters['category'] === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <h4 class="filter-title">Laboratorio</h4>
              <select class="form-control" name="laboratorio" aria-label="Laboratorio">
                <option value="">Todos</option>
                <?php foreach ($laboratories as $lab): ?>
                  <option value="<?php echo html_escape($lab); ?>"<?php echo strcasecmp((string) $filters['laboratory'], (string) $lab) === 0 ? ' selected' : ''; ?>><?php echo html_escape($lab); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <h4 class="filter-title">Precio</h4>
              <div style="display:flex; gap:.5rem;">
                <input type="number" class="form-control" name="min" min="0" step="1" placeholder="Min" value="<?php echo html_escape($filters['min_price']); ?>" aria-label="Precio minimo">
                <input type="number" class="form-control" name="max" min="0" step="1" placeholder="Max" value="<?php echo html_escape($filters['max_price']); ?>" aria-label="Precio maximo">
              </div>
            </div>
            <div class="filter-group">
              <label class="filter-option">
                <input type="checkbox" name="disponible" value="1"<?php echo ! empty($filters['available_only']) ? ' checked' : ''; ?>>
                Solo disponibles
              </label>
            </div>
            <input type="hidden" name="orden" value="<?php echo html_escape($filters['sort']); ?>">
            <button type="submit" class="btn-brand btn-block">Aplicar filtros</button>
          </form>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
