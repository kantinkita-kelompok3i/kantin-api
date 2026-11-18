# Cara Testing Manual di Postman (klik Send satu per satu)

Ini cara yang dipakai kalau mau screenshot per-request untuk lampiran tugas.
`Run` collection = otomatis semua; **manual** = satu request satu klik, cocok kalau
dosen minta bukti tiap endpoint.

## Persiapan (sekali saja)

1. Pastikan server jalan — di terminal lain:
   ```bash
   cd kantin-api
   php artisan serve
   ```
   Cek: `curl http://127.0.0.1:8000/up` → `200`.
2. Database baru (kalau habis run sebelumnya):
   ```bash
   php artisan migrate:fresh --seed
   ```
3. Buka Postman, collection **KANTINKU API v1 — Pertemuan 9 (runnable)** di sidebar.

## Urutan klik manual (9 langkah)

Urutannya penting: token harus diisi dulu sebelum request bertanda gembok.

| # | Klik request | Yang harus muncul di panel Response |
|---|---|---|
| 1 | `0. Setup` → **Login customer → simpan {{token}}** → Send | `200`, `status: success`, `data.access_token` terisi. Test Results: 2 hijau |
| 2 | `0. Setup` → **Login admin → simpan {{adminToken}}** → Send | `200`, token admin. 2 hijau |
| 3 | `0. Setup` → **Simpan product_id pertama** → Send | `200`, list produk. 2 hijau |
| 4 | `1. Auth` → **Me** → Send | `200`, `data.email = riska@kantin.test` |
| 5 | `4. Products` → **Get products + pagination** → Send | `200`, `meta: {page:1, per_page:5, total:30, last_page:6}` |
| 6 | `6. Orders` → **Create order (201)** → Send | `201`, `order_code: KC-...`, `total_amount` terisi |
| 7 | `6. Orders` → **Create order items kosong (422)** → Send | `422`, `errors.items: ["Minimal satu produk harus dipilih"]` |
| 8 | `6. Orders` → **Tanpa token (401)** → Send | `401`, `Unauthenticated, token tidak valid atau tidak dikirim` |
| 9 | `8. Logout` → **Logout** → Send | `200`, `Logout berhasil`. Sesudah ini token mati |

Kalau langkah 1 belum dijalankan, semua request gembok akan balas `401`.
Itu bukan bug — token belum ada.

## Di mana lihat PASS/FAIL per request

Setiap request punya tab **Test Results** (di bawah panel Response).
Isinya 2 baris: `HTTP <kode>` dan `format {status,message,data|errors}`.
Hijau = lulus. Screenshot tab ini untuk bukti per-endpoint.

## Bukti hasil nyata (dijalankan 18 Nov 2026)

Output aktual dari urutan di atas, terhadap database hasil `migrate:fresh --seed`:

```
1. POST /auth/login        -> 200  token 1|RNnDCqCQAFEaF3p8w7y1...
3. GET  /auth/me           -> 200  Data pengguna berhasil diambil | riska@kantin.test
5. GET  /products?limit=2  -> 200  meta {page:1, per_page:2, total:30, last_page:15}
                                   produk pertama: Air Mineral Dingin 4000
6. POST /orders            -> 201  Pesanan berhasil dibuat
                                   order_code KC-20261118-009 total 15000
7. POST /orders items kosong -> 422 Data tidak valid
                                   {'items': ['Minimal satu produk harus dipilih']}
8. GET  /orders tanpa token  -> 401 Unauthenticated, token tidak valid atau tidak dikirim
9. POST /auth/logout       -> 200  Logout berhasil
   GET  /auth/me (token mati) -> 401 Unauthenticated, ...
```

Perhatikan langkah terakhir: setelah logout, token lama langsung `401`.
Itu bukti token benar-benar dihapus dari server, bukan cuma dihapus di aplikasi.

## Kenapa `POST /orders` balas 201 tapi tanpa `payment`

Pembayaran dibuat terpisah di `POST /orders/{id}/payments`. Urutan manualnya:

1. `6. Orders` → Create order → catat `order_id` dari response
2. `7. Payments` → **Create payment (201)** dengan `order_id` itu → Send
3. `7. Payments` → **Get payment detail** → Send

## Beda manual vs Run collection

| | Manual (klik Send) | Run collection |
|---|---|---|
| Kecepatan | 1 klik per request, ~47 klik | 1 klik, 2,5 detik |
| Bukti | screenshot per endpoint | satu halaman ringkasan 94/94 |
| Cocok untuk | lampiran "bukti tiap endpoint" | lampiran "semua lulus" |
| Variable otomatis | ya, sama (dari folder 0) | ya |

Keduanya menguji hal yang sama — manual cuma lebih lambat.
