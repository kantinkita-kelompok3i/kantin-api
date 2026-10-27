# Arsitektur Backend (Laravel MVC + API)

Proyek ini menggunakan arsitektur MVC (Model-View-Controller) yang difokuskan sebagai RESTful API. Setiap layer memiliki tanggung jawab (Separation of Concerns) yang jelas dan tidak tumpang tindih.

## Struktur Direktori Utama
- **Models**: Merepresentasikan tabel database dan relasinya (`app/Models`).
- **Controllers/API**: Menangani alur logika bisnis (`app/Http/Controllers`).
- **Requests**: Menangani validasi input dari klien (`app/Http/Requests`).
- **Resources**: Memformat data Model menjadi JSON yang terstandarisasi (`app/Http/Resources`).
- **Routes**: Mendefinisikan endpoint API (`routes/api.php`).

## Alur Data (End-to-End Flow)
Berikut adalah contoh aliran data saat klien mengirim permintaan pembuatan produk (Create Product):

1. **Client (Flutter)** mengirim request `POST /api/products` dengan payload data.
2. **Route (`api.php`)** menerima request dan meneruskannya ke `ProductController@store`.
3. **Request (`StoreProductRequest`)** memvalidasi input secara otomatis (misal: nama wajib diisi, harga harus angka). Jika gagal, mengembalikan response error 422.
4. **Controller (`ProductController`)** menerima data yang sudah tervalidasi dan memanggil Model.
5. **Model (`Product`)** menyimpan data ke dalam database.
6. **Resource (`ProductResource`)** memformat data produk yang baru dibuat menjadi struktur JSON.
7. **Response** dikembalikan ke Client (Flutter) dengan status 201 (Created) beserta data JSON.