<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="page-band">
  <div class="container-x">
    <h1>Guia de suplementos</h1>
    <p>Entiende que hace cada suplemento, sus beneficios y como elegirlo segun tu objetivo. Recuerda que esta informacion es orientativa y no sustituye la opinion de un profesional de la salud.</p>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Aprende</span>
        <h2 class="section-title">Cada suplemento, explicado</h2>
        <p class="section-subtitle">Descubre que aporta cada tipo de suplemento.</p>
      </div>
    </div>
    <div class="guide-grid">
      <?php foreach ($guides as $key => $guide): ?>
        <article class="guide-card reveal">
          <div class="guide-icon"><i class="bi <?php echo html_escape($guide['icon']); ?>" aria-hidden="true"></i></div>
          <span class="guide-tagline"><?php echo html_escape($guide['tagline']); ?></span>
          <h3><?php echo html_escape($guide['name']); ?></h3>
          <p><?php echo html_escape($guide['description']); ?></p>
          <div class="guide-meta">
            <div><strong>Beneficio</strong><br><?php echo html_escape($guide['benefit']); ?></div>
            <div><strong>Uso</strong><br><?php echo html_escape($guide['usage']); ?></div>
          </div>
          <a class="btn-ghost btn-sm mt-2" href="<?php echo base_url('productos?categoria=' . $key); ?>">Ver productos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <span class="section-eyebrow">Tu objetivo</span>
        <h2 class="section-title">Elige segun tu meta</h2>
        <p class="section-subtitle">El punto de partida ideal para tu suplementacion.</p>
      </div>
    </div>
    <div class="goal-grid">
      <?php foreach ($goals as $goal): ?>
        <div class="goal-card reveal">
          <div class="gicon"><i class="bi <?php echo html_escape($goal['icon']); ?>" aria-hidden="true"></i></div>
          <h3><?php echo html_escape($goal['title']); ?></h3>
          <p><?php echo html_escape($goal['description']); ?></p>
          <div class="goal-rec"><strong>Recomendado:</strong> <?php echo html_escape($goal['recommended']); ?></div>
          <a class="btn-ghost btn-sm mt-2" href="<?php echo base_url('productos?categoria=' . $goal['link']); ?>">Explorar</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="form-card">
      <div class="bicon" style="width:46px;height:46px;border-radius:12px;background:var(--brand-soft);color:var(--brand);display:grid;place-items:center;font-size:1.3rem;margin-bottom:.9rem;"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></div>
      <h2 style="font-size:1.15rem; margin-top:0;">Recomendacion importante</h2>
      <p class="mb-0">La suplementacion es un complemento de una alimentacion equilibrada, una hidratacion adecuada y un entrenamiento planificado. Si tienes una condicion de salud, estas embarazada o tomas medicamentos, consulta a un medico o nutricionista antes de consumir cualquier suplemento. Sigue siempre las indicaciones del fabricante.</p>
    </div>
  </div>
</section>
