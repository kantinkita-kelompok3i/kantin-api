# KantinCepat - Project Guidelines & Git Workflow

## 1. Branching Strategy (Aturan Cabang)
Tim pengembang WAJIB menggunakan struktur branch berikut:
- `main`: Branch utama berisi kode produksi yang stabil. **(Protected)**
- `dev`: Branch integrasi untuk menggabungkan semua fitur sebelum rilis.
- `feature/*`: Branch untuk mengerjakan fitur spesifik (contoh: `feature/login`, `feature/cart`).

## 2. Alur Kerja Git (Git Workflow)
1. Selalu mulai dari branch `dev` terbaru: `git pull origin dev`
2. Buat branch fitur baru: `git checkout -b feature/nama-fitur`
3. Lakukan commit secara berkala dengan pesan yang jelas.
4. Push branch fitur ke repository GitHub: `git push origin feature/nama-fitur`
5. Buat **Pull Request (PR)** dari branch `feature/*` menuju branch `dev`.

## 3. Pull Request (PR) & Review Rules
- **TIDAK BOLEH** melakukan *direct push* ke branch `main`.
- Setiap PR wajib di-review dan di-approve minimal oleh 1 anggota kelompok lain.
- Pastikan kode mematuhi arsitektur (MVC untuk Laravel, MVVM untuk Flutter).
- **HARAM HUKUMNYA** menggunakan perintah `git push --force` pada shared branches (`main` atau `dev`).

## 4. Rollback Policy
Jika terjadi kode yang rusak (breaking changes) di branch integrasi:
- Gunakan `git revert <commit-hash>` untuk membatalkan perubahan yang sudah di-push.
- Hindari penggunaan `git reset --hard` jika commit sudah dibagikan ke anggota tim lain.