/* =========================================================
   Fizzify - script halaman user
   ========================================================= */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const rupiah = (n) => 'Rp. ' + Number(n).toLocaleString('id-ID');
  const csrf = () => ($('meta[name="csrf-token"]') || {}).content || '';

  /* ---------- Toast ---------- */
  const toastEl = $('#toast');
  let toastTimer;

  function toast(message, isError) {
    if (!toastEl || !message) return;
    toastEl.textContent = message;
    toastEl.classList.toggle('is-error', !!isError);
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 3600);
  }
  window.fizzifyToast = toast;

  // pesan flash dari server (login gagal, sukses, dll.)
  if (toastEl && toastEl.dataset.msg) {
    toast(toastEl.dataset.msg, toastEl.classList.contains('is-error'));
  }

  /* ---------- Modal ---------- */
  function openModal(el) {
    if (!el) return;
    el.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModals() {
    $$('.modal').forEach((m) => (m.hidden = true));
    document.body.style.overflow = '';
  }

  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-close]')) closeModals();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModals();
      closeLightbox();
    }
  });

  /* ---------- Checkout + Midtrans Snap ---------- */
  const checkout = $('#checkout');

  if (checkout && window.FZ) {
    const form = $('#checkoutForm');
    const qtyOut = $('#qtyOut');
    const dueOut = $('#dueOut');
    const payBtn = $('#payBtn');
    const state = { price: 0, qty: 1, stock: 1 };

    function refreshTotal() {
      qtyOut.textContent = state.qty;
      dueOut.textContent = rupiah(state.price * state.qty);
    }

    // tombol Buy di kartu produk
    $$('[data-buy]').forEach((btn) => {
      btn.addEventListener('click', () => {
        state.price = Number(btn.dataset.price);
        state.stock = Number(btn.dataset.stock);
        state.qty = 1;

        $('#payProductId').value = btn.dataset.id;
        $('#payImg').src = btn.dataset.image;
        $('#payImg').alt = btn.dataset.name;
        $('#payName').textContent = btn.dataset.name;
        $('#payName').style.color = getComputedStyle(btn.closest('.pcard')).getPropertyValue('--c');
        $('#payPrice').textContent = rupiah(state.price);

        refreshTotal();
        openModal(checkout);
      });
    });

    // tombol - / + jumlah
    $$('[data-qty]', checkout).forEach((btn) => {
      btn.addEventListener('click', () => {
        const next = state.qty + Number(btn.dataset.qty);
        state.qty = Math.min(Math.max(1, next), Math.min(50, state.stock));
        refreshTotal();
      });
    });

    function setBusy(busy) {
      payBtn.disabled = busy;
      payBtn.textContent = busy ? 'Memproses...' : 'Buy Now';
    }

    async function postJson(url, body) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body || {}),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
        throw new Error(firstError || data.message || 'Terjadi kesalahan, coba lagi.');
      }
      return data;
    }

    // setelah popup Snap selesai, cek status asli ke server Midtrans
    // reopen = true: popup ditutup tanpa bayar, kembalikan ke form supaya bisa dicoba lagi
    async function syncOrder(orderCode, reopen) {
      try {
        const result = await postJson(window.FZ.syncUrl + '/' + orderCode + '/sync');
        if (result.paid) {
          closeModals();
          openModal($('#paidOk'));
          form.reset();
          return;
        }
        toast('Pembayaran belum selesai. Selesaikan pembayaranmu, lalu status akan diperbarui otomatis.');
      } catch (err) {
        toast(err.message, true);
      }

      // belum lunas: jangan biarkan halaman terkunci (overflow: hidden) tanpa modal yang terlihat
      if (reopen) openModal(checkout);
      else closeModals();
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!window.FZ.midtransReady || !window.snap) {
        toast('Midtrans belum aktif. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.', true);
        return;
      }

      const data = Object.fromEntries(new FormData(form).entries());
      data.quantity = state.qty;

      setBusy(true);
      try {
        const order = await postJson(window.FZ.checkoutUrl, data);

        // sembunyikan modal kita supaya popup Snap tidak bertumpuk
        checkout.hidden = true;

        let settled = false;

        window.snap.pay(order.snap_token, {
          onSuccess: () => {
            settled = true;
            syncOrder(order.order_code);
          },
          onPending: () => {
            settled = true;
            syncOrder(order.order_code);
          },
          onError: () => {
            settled = true;
            toast('Pembayaran gagal. Silakan coba lagi.', true);
            openModal(checkout);
          },
          onClose: () => {
            // user menutup popup tanpa menyelesaikan; cek sekali siapa tahu sudah dibayar
            if (!settled) syncOrder(order.order_code, true);
          },
        });
      } catch (err) {
        toast(err.message, true);
      } finally {
        setBusy(false);
      }
    });
  }

  /* ---------- Lightbox portofolio ---------- */
  let lightbox;

  function closeLightbox() {
    if (lightbox) {
      lightbox.remove();
      lightbox = null;
    }
  }

  $$('[data-lightbox]').forEach((item) => {
    item.addEventListener('click', () => {
      closeLightbox();
      lightbox = document.createElement('div');
      lightbox.className = 'lightbox';
      lightbox.innerHTML = '<button type="button" aria-label="Tutup">&times;</button><img alt="">';
      lightbox.querySelector('img').src = item.dataset.lightbox;
      lightbox.addEventListener('click', (e) => {
        if (e.target.tagName !== 'IMG') closeLightbox();
      });
      document.body.appendChild(lightbox);
    });
  });
})();
