FORUM FORMATIF PERTEMUAN 9 — AUDIT URUTAN LAPIS BACKEND & API CONTRACT

Kelompok: Grup C-Projek-Mobile (KANTINKU / KantinCepat)
Resource yang dibahas: **Product**

Resource Product saya pilih karena semua fitur lain bergantung padanya: keranjang
butuh harga & stok produk, pesanan butuh snapshot nama dan harga produk, dan
dashboard admin butuh jumlah produk aktif.

Urutan tujuh lapis yang kami bangun:

1. **Migration** — `create_products_table` dengan `category_id` (FK ke categories,
   restrictOnDelete), `name`, `description`, `price` decimal(12,2), `image_url`,
   `stock`, `is_available`, plus index gabungan `(category_id, is_available)`.
   Tabel referensi (categories) dimigrasikan lebih dulu.
2. **Model** — `Product` dengan `$fillable`, cast `price` decimal:2 dan
   `is_available` boolean, relasi `belongsTo(Category)` serta
   `hasMany(OrderItem)`, dan scope `available()`.
3. **Seeder** — `ProductSeeder` mengisi 30 produk dari 6 kategori dengan harga
   dan stok realistis (Rp4.000–Rp19.000), jadi testing tidak bergantung input manual.
4. **Form Request** — `StoreProductRequest` / `UpdateProductRequest`:
   `category_id` wajib `exists:categories,id`, `product_name` wajib max 150,
   `price` numeric min 0, `stock` integer min 0.
5. **API Resource** — `ProductResource` mengeluarkan `product_id`, `product_name`,
   `price` sebagai float, `is_available` boolean, dan objek `category` bila di-eager-load.
6. **Controller** — `ProductController` dengan index (filter kategori, search, pagination),
   show, store, update, destroy. Nama field kontrak `product_name` dipetakan ke kolom
   `name` di satu helper `payload()`.
7. **Route** — `GET /products`, `GET /products/{id}` publik; `POST`, `PATCH`, `DELETE`
   di dalam grup `auth:sanctum` + middleware `admin`.

Endpoint dari `api_contract.md` yang saya jadikan acuan: **POST /products** (khusus admin).
Response sukses yang diharapkan — **201**:

```json
{
  "status": "success",
  "message": "Produk berhasil dibuat",
  "data": { "product_id": 31, "product_name": "Nasi Goreng Spesial Kampus", "price": 15000 }
}
```

Error case yang wajib dites: **422** saat `category_id` tidak ada atau `price` negatif.
Bukti keberhasilannya: response `{"status":"error","message":"Data tidak valid","errors":{"category_id":[...]}}`
dengan HTTP 422 — terbukti di `docs/testing_results.md` baris test "Admin buat produk"
(201) dan test validasi 422, bukan sekadar asumsi.

Kami tidak meminta AI membuat seluruh backend sekaligus karena pada percobaan awal
hasilnya justru berhenti di setengah jalan: migration ada tapi relasi model kosong,
controller mengembalikan model mentah, dan validasi tercampur di controller.
Dengan satu lapis per commit, setiap kesalahan ketahuan di lapis tempat ia terjadi —
urutannya juga tidak mungkin salah (FK sebelum tabel referensi).
