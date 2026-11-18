# Tugas Proyek Pertemuan 9 — Backend Laravel, Postman & Testing Results

**Nama kelompok:** Grup C-Projek-Mobile (KANTINKU — aplikasi pre-order kantin kampus)

**Anggota:**

1. Arjuna — NIM: 23210109
2. Said Kemal Alfarizi — NIM: 23210056
3. Riska Dinar Andini — NIM: 23210063

**URL repository backend:**
https://github.com/kantinkita-kelompok3i/kantin-api

**URL Pull Request** (`feature/api-implementation` → `main`):
https://github.com/kantinkita-kelompok3i/kantin-api/pull/2

**Permalink testing_results.md:**
https://github.com/arjunaa-dot/kantin-api/blob/feature/api-implementation/docs/testing_results.md

> Catatan PR: repo kelompok saat ini hanya memiliki branch `main` (tidak ada `dev`),
> sehingga PR diarahkan ke `main`. Status PR: MERGEABLE, 20 commit, 126 file, 0 konflik.

## Jumlah endpoint aktif

**26 endpoint** dalam prefix `/api/v1` (`php artisan route:list --path=api/v1` → 31 baris termasuk OPTIONS).

- **Publik:** register, login, daftar & detail kategori, daftar & detail produk
- **Token:** logout, me, users/me (GET + PATCH), cart (GET, POST item, PATCH item, DELETE item), orders (GET, POST, GET detail, DELETE/cancel, status-history), pembayaran (POST, GET)
- **Admin:** CRUD kategori (POST/PATCH/DELETE), CRUD produk (POST/PATCH/DELETE), PATCH status pesanan, PATCH pembayaran lunas, GET admin/orders, GET admin/summary

## Jumlah atomic commit

**20 commit** dalam PR. Sembilan lapis dikerjakan satu commit per lapis:
`chore(bootstrap)` → `feat(database)` → `feat(model)` → `feat(seeder)` →
`feat(request)` → `feat(resource)` → `feat(controller)` → `feat(route)` →
`docs(testing)`, ditambah commit bukti dan perbaikan. Commit terakhir: `f5ca025`.

## Ringkasan 10 test Postman

Collection v2.1 berisi 47 request dalam 9 folder; eksekusi menghasilkan **94 assertion, 0 gagal**
(Postman Runner Desktop 2 detik 253 ms; newman CLI juga lulus).

1. `POST /auth/register` — 201 + access_token
2. `POST /auth/login` — 200 + token (password salah → 401)
3. `GET /auth/me` — 200, data user diambil dari token
4. `GET /products?limit=3` — 200 list + meta pagination
5. `GET /products/{id}` — 200 detail; id tidak ada → 404
6. `POST /orders` valid — 201 order + total terhitung, stok berkurang
7. `GET /orders` — 200, hanya pesanan milik user yang login
8. `POST /orders` dengan `{"items":[]}` — 422 + detail errors
9. `GET /orders` tanpa token — 401 Unauthorized
10. `POST /auth/logout` — 200, token dihapus

## Sebaran kode status hasil run

200 × 26, 201 × 7, 422 × 8, 404 × 3, 401 × 2, 403 × 1, **5xx = 0**.

Bukan hanya happy path: email duplikat, items kosong, status tidak dikenal,
customer mengakses endpoint admin, dan token setelah logout juga diuji.

## Seeder terverifikasi

Minimal 5 record per tabel utama — categories 6, products 30, users 6, orders 8,
order_items 16, payments 8. Diverifikasi ulang lewat `php artisan migrate:fresh --seed`
lalu hitung record per model: semua ≥ 5.

## Migration

13 migration berstatus Ran (`php artisan migrate:status`); `migrate:fresh --seed` selesai bersih.

## Catatan teknis

- Format response seragam `{status, message, data, meta}`; error `{status, message, errors}`. Password dan `remember_token` tidak pernah muncul di response (diuji di collection).
- Seluruh validasi input memakai Form Request (11 Form Request), tidak ada validasi inline di controller.
- Repo asal tidak punya `bootstrap/`, `config/`, `public/` sehingga `php artisan` gagal total. Commit pertama melengkapi skeleton Laravel 12 + Sanctum.
- Database dev: SQLite. File `.env` dan file DB tidak di-commit.
- Base URL dev: `http://127.0.0.1:8000/api/v1`
- Backend ini menjadi bahan Pertemuan 10 (integrasi Flutter ↔ Laravel).

## Lampiran

- Collection: `docs/postman/KANTINKU_API_v1.postman_collection.json`
- Environment: `docs/postman/KANTINKU_Local.postman_environment.json`
- Laporan testing + 16 kartu bukti: `docs/testing_results.md`
