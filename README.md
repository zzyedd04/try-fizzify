# Fizzify (Laravel + Midtrans + WhatsApp)

Website minuman soda Fizzify sesuai prototype Figma: Home, About, Product, Portofolio, Contact,
login user/admin (+ Google), pembayaran Midtrans Snap, dan dashboard admin.

## 1. Menjalankan di Laragon (MySQL)

Kebutuhan: **Laragon** (PHP 8.2+, Composer, MySQL sudah termasuk).

1. Taruh folder project di `C:\laragon\www\fizzify-laravel`, lalu klik **Start All** di Laragon.
2. Buka **Terminal Laragon**, masuk ke folder project, lalu jalankan:
   ```bash
   composer install
   php artisan fizzify:install
   ```
   Atau cukup klik dua kali **`setup-laragon.bat`**.
   Perintah `fizzify:install` otomatis: membuat database `fizzify` (kalau belum ada), membuat semua tabel,
   mengisi akun admin + 6 produk, lalu mengecek semua tabel/kolom penting.
3. Buka **http://fizzify-laravel.test** (Laragon membuat alamat ini otomatis; kalau tidak muncul, klik kanan Laragon > Apache > Reload,
   atau jalankan `php artisan serve` lalu buka http://localhost:8000).

`.env` sudah memakai MySQL Laragon (`root`, tanpa password, `127.0.0.1:3306`, database `fizzify`).
Kalau `.env` hilang: `copy .env.example .env` lalu `php artisan key:generate`.

**Kalau ada error database** (tabel/kolom tidak ada, `Unknown column`, dll.) dan database ini boleh dikosongkan:
```bash
php artisan fizzify:install --fresh
```
(`--fresh` menghapus semua tabel di database `fizzify` lalu membuatnya ulang.)

**Akun admin bawaan:** username `admin`, password `admin123` (ganti sebelum dipakai sungguhan).
Login admin lewat tombol **Log In To Admin** di halaman login. User biasa daftar lewat **Sign Up**.

**Error `cURL error 60: SSL certificate problem`** (Midtrans/Google di Windows): di `.env` isi `MIDTRANS_VERIFY_SSL=false`
(hanya untuk lokal), atau perbaiki `curl.cainfo` di php.ini Laragon.

## Dashboard admin, produk, dan keranjang user

- **Dashboard / Orders / Products admin** otomatis mengecek status pembayaran terbaru ke Midtrans setiap dibuka,
  jadi pembelian yang sudah dibayar (termasuk lewat simulator sandbox) langsung menambah saldo, omzet, "Cans Sold",
  dan muncul di daftar Transactions beserta akun pembelinya (`@username`). Webhook tidak wajib di localhost.
- **Terjual** setiap produk mulai dari 0 dan hanya naik saat pembayaran Midtrans berhasil.
- **Keranjang** (ikon tas di kanan atas, di samping Login) menampilkan pesanan user: Dibayar / Menunggu pembayaran
  (ada tombol Bayar Sekarang) / Dikonfirmasi / Dibatalkan.

## 2. Midtrans (wajib diisi supaya tombol Buy jalan)

1. Daftar di https://dashboard.sandbox.midtrans.com lalu buka **Settings > Access Keys**.
2. Isi di `.env`:
   ```
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
3. Jalankan ulang `php artisan serve`. Login sebagai user, klik **Buy**, isi data, **Buy Now**, popup Midtrans muncul.

**Kartu tes (sandbox):** `4811 1111 1111 1114`, expiry bebas di masa depan, CVV `123`, OTP `112`.

Alur pembayaran:
- `POST /checkout` membuat pesanan `pending` + meminta Snap token (`CheckoutController`, `MidtransService`).
- Setelah popup selesai, browser memanggil `POST /checkout/{kode}/sync`. Server **bertanya langsung ke Midtrans**,
  jadi di localhost status tetap terbarui tanpa webhook.
- Webhook (opsional, untuk pembayaran Virtual Account yang selesai belakangan):
  isi **Payment Notification URL** di Midtrans dengan `https://domain-kamu/midtrans/notification`
  (di localhost pakai ngrok). Signature divalidasi di `MidtransService::hasValidSignature`.

Aturan angka (sesuai permintaan):
- Dashboard (Active Balance, Earning, Reward, Transactions) dihitung **hanya dari pesanan berstatus paid** dari Midtrans, jadi tetap **Rp. 0** sampai ada pembeli.
- Angka **Terjual** per produk dimulai dari angka contoh prototype (410, 848, dst.) dan hanya naik **sekali per pesanan yang sudah dibayar** (`SalesService`). Menjalankan seeder lagi tidak mereset angkanya.
- Admin membatalkan pesanan (tombol X): pesanan keluar dari omzet dan terjual berkurang. Refund uang dilakukan manual di dashboard Midtrans.

## 3. Login Google

1. https://console.cloud.google.com > APIs & Services > Credentials > **OAuth client ID** (Web application).
2. Authorized redirect URI: `http://localhost:8000/auth/google/callback`.
3. Isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di `.env`.
4. Untuk admin lewat Google, isi `ADMIN_EMAILS=emailkamu@gmail.com` (pisahkan koma).

Sebelum diisi, tombol Google menampilkan pesan bahwa fitur belum aktif.

## 4. WhatsApp (Contact)

Form Contact menyimpan pesan ke tabel `contact_messages`, lalu membuka WhatsApp admin dengan isi pesan terisi.
Ubah nomor di `.env`: `FIZZIFY_WHATSAPP=6286582391224` (awali 62, tanpa +). Nomor di footer juga membuka WhatsApp.

## 5. Struktur penting

| Bagian | File |
| --- | --- |
| Rute | `routes/web.php` |
| Midtrans | `app/Services/MidtransService.php`, `app/Http/Controllers/CheckoutController.php`, `MidtransNotificationController.php` |
| Hitung terjual / stok | `app/Services/SalesService.php` |
| Admin | `app/Http/Controllers/Admin/*`, `resources/views/admin/*` |
| Tampilan user | `resources/views/*.blade.php`, `public/assets/css/app.css`, `public/assets/js/app.js` |
| Tampilan admin | `public/assets/css/admin.css`, `public/assets/js/admin.js` |
| Teks footer / tagline | `resources/views/contact.blade.php` |
| Font & gambar | `public/assets/fonts`, `public/assets/img` (tanpa CDN) |

## 6. Catatan

- Popup pembayaran bergaya prototype, tetapi kolom kartu diganti data pengiriman. Data kartu diisi di popup resmi Midtrans (lebih aman).
- Tombol di dashboard: **Transfer Balance** (tarik langsung), **Request Balance** (saldo ditahan sampai ditandai selesai), toggle **PRIORITY** (hanya pesanan menunggu konfirmasi), pilihan bulan, centang/X di Transactions.
- Sebelum online: set `APP_ENV=production`, `APP_DEBUG=false`, ganti password admin, dan pakai key Midtrans production.
