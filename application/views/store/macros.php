<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="page-band">
  <div class="container-x">
    <p class="eyebrow">Herramienta</p>
    <h1>Calculadora de macronutrientes</h1>
    <p>Ingresa tus datos y descubre cuantas calorías, proteinas, carbohidratos y grasas deberias consumir al día para alcanzar tu objetivo fisico.</p>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <div class="macro-layout">
      <form id="macroForm" class="form-card" novalidate>
        <h2 style="font-size:1.15rem; margin-bottom:1.2rem;">Tus datos</h2>

        <div class="form-group">
          <label>Sexo</label>
          <div class="macro-radio">
            <label class="pay-option"><input type="radio" name="sexo" value="m" checked> Hombre</label>
            <label class="pay-option"><input type="radio" name="sexo" value="f"> Mujer</label>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label for="edad">Edad (años)</label>
            <input class="form-control" type="number" id="edad" min="12" max="99" step="1" value="25">
          </div>
          <div class="form-group">
            <label for="peso">Peso (kg)</label>
            <input class="form-control" type="number" id="peso" min="30" max="300" step="0.1" value="70">
          </div>
        </div>

        <div class="form-group">
          <label for="altura">Altura (cm)</label>
          <input class="form-control" type="number" id="altura" min="120" max="230" step="1" value="170">
        </div>

        <div class="form-group">
          <label for="actividad">Nivel de actividad</label>
          <select class="form-control" id="actividad">
            <option value="1.2">Sedentario (poco o nada de ejercicio)</option>
            <option value="1.375">Ligero (1-3 días por semana)</option>
            <option value="1.55" selected>Moderado (3-5 días por semana)</option>
            <option value="1.725">Activo (6-7 días por semana)</option>
            <option value="1.9">Muy activo (atleta o trabajo fisico)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="objetivo">Objetivo</label>
          <select class="form-control" id="objetivo">
            <option value="perder">Perder grasa</option>
            <option value="mantener" selected>Mantener peso</option>
            <option value="ganar">Ganar masa muscular</option>
          </select>
        </div>

        <button type="submit" class="btn-brand btn-block">Calcular</button>
        <p class="muted mt-2 mb-0" style="font-size:.8rem;">Resultado orientativo basado en la formula Mifflin-St Jeor. No sustituye la valoracion de un médico o nutricionista.</p>
      </form>

      <div class="macro-result" id="macroResult" aria-live="polite">
        <div class="macro-kcal">
          <span class="eyebrow">Calorías diarias</span>
          <p class="macro-kcal-num"><span id="macroKcal">0</span> <small>kcal</small></p>
          <p class="muted mb-0">Gasto energetico estimado (TDEE): <strong id="macroTdee">0</strong> kcal</p>
          <p class="muted mb-0 mt-2" id="macroGoalNote"></p>
        </div>

        <div class="macro-cards">
          <div class="macro-card">
            <span class="macro-label">Proteínas</span>
            <p class="macro-val"><span id="macroProtein">0</span> g</p>
            <div class="macro-bar protein"><span id="barProtein"></span></div>
            <span class="macro-pct" id="pctProtein">0%</span>
          </div>
          <div class="macro-card">
            <span class="macro-label">Carbohidratos</span>
            <p class="macro-val"><span id="macroCarbs">0</span> g</p>
            <div class="macro-bar carbs"><span id="barCarbs"></span></div>
            <span class="macro-pct" id="pctCarbs">0%</span>
          </div>
          <div class="macro-card">
            <span class="macro-label">Grasas</span>
            <p class="macro-val"><span id="macroFat">0</span> g</p>
            <div class="macro-bar fat"><span id="barFat"></span></div>
            <span class="macro-pct" id="pctFat">0%</span>
          </div>
        </div>

        <p class="muted mt-3 mb-0" style="font-size:.82rem;" id="macroError" hidden>Completa edad, peso y altura con valores validos.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container-x">
    <div class="section-head">
      <div>
        <p class="eyebrow">Para principiantes</p>
        <h2 class="section-title">ENTIENDE TU <em>RESULTADO</em></h2>
        <p class="section-subtitle">Si es tu primera vez calculando macronutrientes, aquí tienes lo esencial explicado en palabras simples.</p>
      </div>
    </div>

    <div class="guide-grid">
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-fire" aria-hidden="true"></i></div>
        <h3>¿Qué son las calorías?</h3>
        <p>Es la energía que tu cuerpo usa para vivir, pensar y entrenar. Si comes más de lo que gastas, subes de peso; si comes menos, bajas. Tu resultado muestra cuantas calorías al día te acercan a tu meta.</p>
      </article>
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-droplet-half" aria-hidden="true"></i></div>
        <h3>Proteínas</h3>
        <p>Son los "ladrillos" de tus músculos: ayudan a recuperarte y a crecer. Están en carnes, huevos, lácteos, legumbres y en suplementos como la proteína de suero (whey).</p>
      </article>
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-lightning-charge" aria-hidden="true"></i></div>
        <h3>Carbohidratos</h3>
        <p>Son tu energía principal para entrenar fuerte. Están en arroz, pasta, pan, avena, frutas y verduras. No son el enemigo: son tu combustible diario.</p>
      </article>
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-moisture" aria-hidden="true"></i></div>
        <h3>Grasas</h3>
        <p>Son necesarias para tus hormonas y tu salud. Elige fuentes buenas como aguacate, frutos secos, aceite de oliva y pescado. Aportan mucha energía por cada gramo.</p>
      </article>
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></div>
        <h3>¿Cómo usar tu resultado?</h3>
        <p>Usa esas cantidades como meta diaria. Reparte los macros en 3 a 5 comidas con alimentos que disfrutes y revisa tu progreso cada 2 o 3 semanas para ajustar.</p>
      </article>
      <article class="guide-card">
        <div class="guide-icon"><i class="bi bi-lightbulb" aria-hidden="true"></i></div>
        <h3>Consejos rapidos</h3>
        <p>No busques la perfección: la constancia gana. Incluye proteína en cada comida, hidrátate, duerme bien y cambia las calorías poco a poco, no de golpe.</p>
      </article>
    </div>

    <div class="form-card mt-4">
      <p class="eyebrow">Importante</p>
      <p class="mb-0 muted">Esta calculadora es una estimación orientativa, no una receta medica. Cada persona es distinta: si tienes una condición de salud, estás embarazada o quieres un plan detallado, consulta a un médico o nutricionista. Recuerda que los suplementos son un complemento de tu alimentación, no un reemplazo.</p>
    </div>
  </div>
</section>

<script src="<?php echo base_url('assets/js/calculator.js'); ?>" defer></script>
