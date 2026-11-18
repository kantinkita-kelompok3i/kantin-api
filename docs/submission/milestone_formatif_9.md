# MILESTONE FORMATIF PERTEMUAN 9 — Layer-by-Layer Verification

**Kelompok:** Grup C-Projek-Mobile (KANTINKU / KantinCepat)

**Commit hash terakhir:** `f5ca025` (branch `feature/api-implementation`)

**Jumlah endpoint aktif:** 26 endpoint (`php artisan route:list --path=api/v1` → 31 baris termasuk OPTIONS)

## Status tiap lapis

| # | Lapis | Status | Bukti verifikasi |
|---|---|---|---|
| 1 | Migration | SELESAI | `php artisan migrate:status` → 13 migration Ran; `migrate:fresh --seed` bersih tanpa error |
| 2 | Model | SELESAI | Relasi diuji lewat Tinker: Category→products, Product→category/orderItems + scope `available`, Order→items/payment/statusHistories, User→cart/orders. Cast `price` decimal:2, `is_available` boolean |
| 3 | Seeder | SELESAI | categories 6, products 30, users 6, orders 8, order_items 16, payments 8 — semua ≥ 5. `DatabaseSeeder` berisi assert Product/User/Order/Payment ≥ 5 |
| 4 | Form Request | SELESAI | 11 Form Request dipakai semua endpoint input. `POST /orders` dengan `{"items":[]}` → HTTP 422 + detail `errors` |
| 5 | Resource | SELESAI | Field mengikuti `api_contract.md` (`product_name`, `order_code`, `order_status`). Response tidak memuat `password` maupun `remember_token` |
| 6 | Controller + Route | SELESAI | `route:list --path=api/v1` → 26 endpoint. `GET /products`, `POST /orders`, `PATCH /orders/{id}/status` terbukti jalan. Error 401/403/404/422 dibungkus JSON seragam, bukan HTML |
| 7 | Postman | SELESAI | Collection 47 request / 9 folder. `npx newman run` → 94 assertion, 0 gagal. Login menyimpan token otomatis; request tanpa token → 401 |
| 8 | Git | SELESAI | Satu commit per lapis (lihat `git log main..HEAD`), 20 commit dalam PR |

**Status lapis: semua SELESAI. Tidak ada yang BELUM.**

## Error paling sulit yang sudah diselesaikan

Semua endpoint ber-token mengembalikan HTTP 500 dengan pesan
`Auth guard [sanctum] is not defined`. Penyebabnya bukan paket Sanctum,
melainkan `config/auth.php` di skeleton repo lama yang tidak mendaftarkan guard
`sanctum`. Setelah guard didaftarkan dan `php artisan optimize:clear` dijalankan,
seluruh 25/25 test lulus.

Kesalahan kedua: middleware `abort(403)` mengembalikan HTML sehingga assertion
format JSON gagal. Diperbaiki dengan membungkus semua `HttpExceptionInterface`
di prefix `/api/*` menjadi `{status, message, errors}`.
