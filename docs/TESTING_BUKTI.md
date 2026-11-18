# Bukti Test Postman — KANTINKU API v1

Ada dua bukti, keduanya dari eksekusi nyata (bukan template):

## 1. Postman Desktop — Runner

Screenshot: `docs/screenshots/02-postman-runner-results.png`

- Ran today at **22:06:02**, environment: **none** (pakai collection variable)
- Iterations **1** · Duration **2s 253ms** · All tests **94**
- **Passed 94 · Failed 0 · Skipped 0 · Errors 0** · Avg response **32 ms**
- Hasil per request bisa dilihat di kartu di bawah ringkasan; tiap kartu punya dua assertion:
  `PASS HTTP <kode>` dan `PASS format (status,message,data|errors)`.

Cara reproduksi di Postman Desktop:

1. Import `docs/postman/KANTINKU_API_v1.postman_collection.json`
2. Klik kanan collection → **Run** (atau tab Runner → pilih collection)
3. **Start run**

## 2. Newman CLI (runner resmi Postman, headless)

```bash
npx newman run docs/postman/KANTINKU_API_v1.postman_collection.json
```

- Requests **47** · Assertions **94** · Failed **0** · Durasi 2,6 detik
- Sebaran kode: `200` ×26, `201` ×7, `422` ×8, `404` ×3, `401` ×2, `403` ×1, **`5xx` = 0**
- Laporan HTML: `docs/screenshots/kantin-report.html`
- Screenshot ringkasan: `docs/screenshots/01-newman-summary.png`

Angka Desktop dan Newman sama karena menjalankan file collection yang sama.

## Kenapa 47 request padahal endpoint cuma 26

26 itu jumlah route aktif. Collection menjalankan tiap endpoint lebih dari sekali untuk
skenario berbeda — `POST /auth/login` 3x (customer, admin, password salah),
`POST /products` 4x (201, 422, 403, sekali pakai untuk DELETE), `POST /orders` 2x (201, 422).

## Yang diuji bukan cuma happy path

- **422** — register email duplikat, kategori duplikat, produk invalid (`category_id` tidak ada,
  `price` negatif, nama kosong), order `items: []`, qty 0, status tidak dikenal, metode bayar tidak dikenal
- **401** — login password salah, `GET /orders` tanpa token
- **403** — customer mencoba `POST /products`
- **404** — produk/order/item keranjang yang tidak ada
- **500** — nol, tidak ada satu pun response 5xx selama run

## Reproduksi dari nol

```bash
php artisan migrate:fresh --seed      # DB bersih (wajib sebelum run ulang)
php artisan serve                     # terminal lain, port 8000
npx newman run docs/postman/KANTINKU_API_v1.postman_collection.json
```

Tanpa `migrate:fresh`, beberapa test unik (email, nama kategori) akan 422 karena datanya
sudah ada — itu perilaku benar, bukan bug.
