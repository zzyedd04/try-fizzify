/* =========================================================
   Fizzify - script halaman admin
   ========================================================= */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  /* ---------- Modal ---------- */
  function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
      el.hidden = false;
      document.body.style.overflow = 'hidden';
      const first = $('input:not([type=hidden])', el);
      if (first) setTimeout(() => first.focus(), 50);
    }
  }

  function closeModals() {
    $$('.amodal').forEach((m) => (m.hidden = true));
    document.body.style.overflow = '';
  }

  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open]');
    if (opener) return openModal(opener.dataset.open);
    if (e.target.closest('[data-aclose]')) closeModals();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModals();
    if (e.key === 'Enter' && e.target.matches('.orow')) openModal(e.target.dataset.open);
  });

  /* ---------- Pilih bulan: langsung kirim form ---------- */
  $$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));

  /* ---------- PRIORITY: tampilkan hanya transaksi yang menunggu konfirmasi ---------- */
  const list = $('#txlist');
  const toggle = $('#priorityToggle');

  function renderTx() {
    if (!list) return;
    const onlyNew = toggle && toggle.getAttribute('aria-pressed') === 'true';
    let shown = 0;
    $$('.tx', list).forEach((row) => {
      const match = !onlyNew || row.dataset.new === '1';
      const visible = match && shown < 3;
      row.hidden = !visible;
      if (visible) shown++;
    });
  }

  if (toggle) {
    toggle.addEventListener('click', () => {
      const on = toggle.getAttribute('aria-pressed') !== 'true';
      toggle.setAttribute('aria-pressed', String(on));
      renderTx();
      if (on && window.fizzifyToast) window.fizzifyToast('Priority aktif: hanya pesanan yang menunggu konfirmasi.');
    });
  }
  renderTx();

  /* ---------- Grafik Earning (omzet per hari) ---------- */
  const spark = $('#spark');
  if (spark) {
    const series = JSON.parse(spark.dataset.series || '[]');
    const W = 400, H = 110, pad = 8;
    const max = Math.max(...series, 1);
    const step = series.length > 1 ? (W - pad * 2) / (series.length - 1) : 0;
    const pts = series.map((v, i) => [pad + i * step, H - pad - (v / max) * (H - pad * 2)]);
    const line = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
    const area = line + ` L${pts.length ? pts[pts.length - 1][0].toFixed(1) : pad} ${H - pad} L${pad} ${H - pad} Z`;

    spark.innerHTML =
      '<defs><linearGradient id="sg" x1="0" y1="0" x2="0" y2="1">' +
      '<stop offset="0" stop-color="#0027ed" stop-opacity=".28"/><stop offset="1" stop-color="#0027ed" stop-opacity="0"/></linearGradient></defs>' +
      '<path d="' + area + '" fill="url(#sg)"/>' +
      '<path d="' + line + '" fill="none" stroke="#0027ed" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>';
  }
})();
