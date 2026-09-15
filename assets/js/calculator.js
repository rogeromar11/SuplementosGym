(function () {
  'use strict';

  var form = document.getElementById('macroForm');
  if (!form) { return; }

  var result = document.getElementById('macroResult');
  var errorEl = document.getElementById('macroError');
  var el = function (id) { return document.getElementById(id); };
  var val = function (id) { var e = el(id); return e ? parseFloat(e.value) : NaN; };
  var fmt = function (n) {
    try { return Math.round(n).toLocaleString('es-CR'); } catch (e) { return String(Math.round(n)); }
  };

  var notes = {
    perder: 'Objetivo: perder grasa. Mantén la proteína alta para conservar tu masa muscular.',
    mantener: 'Objetivo: mantener tu peso y mejorar tu composición corporal.',
    ganar: 'Objetivo: ganar masa muscular con un ligero superávit de calorías.'
  };

  function calc() {
    var sex = (form.querySelector('input[name="sexo"]:checked') || {}).value || 'm';
    var age = val('edad');
    var weight = val('peso');
    var height = val('altura');
    var activity = val('actividad');
    var goal = el('objetivo').value;

    if (!(age > 0) || !(weight > 0) || !(height > 0) || !(activity > 0)) {
      result.classList.remove('show');
      if (errorEl) { errorEl.hidden = false; }
      return;
    }
    if (errorEl) { errorEl.hidden = true; }

    /* Metabolismo basal (Mifflin-St Jeor) */
    var bmr = (10 * weight) + (6.25 * height) - (5 * age) + (sex === 'm' ? 5 : -161);
    var tdee = bmr * activity;

    var factor = goal === 'perder' ? 0.80 : (goal === 'ganar' ? 1.15 : 1);
    var kcal = tdee * factor;

    /* Reparto de macros */
    var protein = weight * (goal === 'perder' ? 2.2 : 1.8);
    var fat = weight * (goal === 'perder' ? 0.9 : 1.0);
    var pKcal = protein * 4;
    var fKcal = fat * 9;

    if (pKcal + fKcal > kcal) {
      fat = Math.max(0.4 * weight, (kcal - pKcal) / 9);
      if (fat < 0) { fat = 0; }
      fKcal = fat * 9;
    }
    var carbs = Math.max(0, (kcal - pKcal - fKcal) / 4);

    var cKcal = carbs * 4;
    var total = pKcal + cKcal + fKcal || 1;
    var pPct = Math.round(pKcal / total * 100);
    var cPct = Math.round(cKcal / total * 100);
    var fPct = Math.round(fKcal / total * 100);

    el('macroKcal').textContent = fmt(kcal);
    el('macroTdee').textContent = fmt(tdee);
    el('macroGoalNote').textContent = notes[goal] || '';

    el('macroProtein').textContent = Math.round(protein);
    el('macroCarbs').textContent = Math.round(carbs);
    el('macroFat').textContent = Math.round(fat);

    el('pctProtein').textContent = pPct + '%';
    el('pctCarbs').textContent = cPct + '%';
    el('pctFat').textContent = fPct + '%';
    el('barProtein').style.width = pPct + '%';
    el('barCarbs').style.width = cPct + '%';
    el('barFat').style.width = fPct + '%';

    result.classList.add('show');
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    calc();
    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  });

  form.querySelectorAll('input, select').forEach(function (input) {
    input.addEventListener('change', calc);
  });

  calc();
})();
