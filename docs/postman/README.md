# Cara Menjalankan Testing (Pertemuan 9)

Ada dua cara. Pilih salah satu — hasilnya sama, kedua-duanya sudah dijalankan dan lulus.

## Persiapan (sekali saja)

```bash
cd kantin-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve            # biarkan terminal ini terbuka (http://127.0.0.1:8000)
```

Akun seeder: `admin@kantin.test` / `riska@kantin.test` / `said@kantin.test` / `arjuna@kantin.test` / `dahrul@kantin.test` / `kevin@kantin.test` — password semua `password123`.

## Cara A — Postman Desktop (ini yang dipakai untuk screenshot)

1. Download Postman dari <https://www.postman.com/downloads/> (gratis, tidak perlu akun untuk import).
2. Buka Postman → **Import** → pilih **dua** file:
   - `docs/postman/KANTINKU_API_v1.postman_collection.json`
   - `docs/postman/KANTINKU_Local.postman_environment.json`
3. Kanan atas, pilih environment **KANTINKU Local**.
4. Jalankan folder **`0. Setup (jalankan pertama)`** — ini mengisi `{{token}}`, `{{adminToken}}`, dan `{{productId}}` otomatis.
5. Jalankan folder 1 → 8 berurutan. Tiap request punya test sendiri; hasilnya muncul di tab **Test Results** (hijau = lulus).
6. **Screenshot** tab Test Results tiap folder (minimal 10 gambar) → simpan ke `docs/screenshots/`.
7. (Opsional, paling cepat) klik **Run** di Collection → **Run KANTINKU API v1** → semua 47 request jalan sekali klik, hasilnya satu halaman ringkasan — screenshot halaman itu.

Kalau mau sekaligus: **Run collection** = 47 request, 94 assertion, semua hijau.

## Cara B — Tanpa install apa pun (newman)

```bash
npx newman run docs/postman/KANTINKU_API_v1.postman_collection.json
```

Hasil terakhir: **47 request, 94 assertion, 0 gagal, ~2,3 detik.**

Untuk laporan HTML yang bisa di-screenshot (pengganti tampilan Postman):

```bash
npx newman run docs/postman/KANTINKU_API_v1.postman_collection.json \
  --reporters cli,htmlextra --reporter-htmlextra-export docs/screenshots/newman-report.html
```

> Butuh `newman-reporter-htmlextra` sekali: `npm i -g newman-reporter-htmlextra`.

## Cara B+ — laporan HTML + screenshot per test (yang dipakai di repo ini)

Semua PNG di `docs/screenshots/` dibuat dari report JSON run newman, lewat
`docs/postman/make_evidence.py`. Isinya per test: method+URL, kode status, durasi,
request body, response body asli, dan status assertion.

```bash
npx newman run docs/postman/KANTINKU_API_v1.postman_collection.json \
  --reporters json --reporter-json-export /tmp/run.json
python3 docs/postman/make_evidence.py /tmp/run.json
```

## Cara C — Script Python (paling cepat untuk angka, bukan screenshot)

```bash
python3 docs/postman/run_tests.py http://127.0.0.1:8000/api/v1
```

Script ini menjalankan skenario yang sama lalu **menulis ulang** `docs/testing_results.md`
dari response asli (bukan tabel yang diketik manual). Hasil terakhir: **25/25 lulus**.

## Catatan

- Semua test menganggap database baru. Kalau diulang tanpa `migrate:fresh --seed`, beberapa test unik (email/kategori) akan gagal karena datanya sudah ada — bukan bug.
- `{{$timestamp}}` dipakai di nama/email supaya register tetap unik saat diulang.
- Base URL environment: `http://127.0.0.1:8000/api/v1`. Kalau `php artisan serve` pakai port lain, ubah nilai `baseUrl` di environment.
