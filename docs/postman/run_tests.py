#!/usr/bin/env python3
"""Smoke test KANTINKU API — jalankan saat `php artisan serve` aktif.

    python3 docs/postman/run_tests.py [base_url]

Menulis ulang docs/testing_results.md dari hasil nyata (bukan template).
ponytail: stdlib urllib saja; ganti requests kalau nanti butuh multipart upload.
"""

import json
import sys
import urllib.error
import urllib.request
from datetime import datetime

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000/api/v1").rstrip("/")
CUSTOMER = {"email": "riska@kantin.test", "password": "password123"}
ADMIN = {"email": "admin@kantin.test", "password": "password123"}
STAMP = datetime.now().strftime("%H%M%S")

results = []


def call(method, path, body=None, token=None, expect=200, label="", note=""):
    url = BASE + path
    data = json.dumps(body).encode() if body is not None else None
    req = urllib.request.Request(url, data=data, method=method)
    req.add_header("Accept", "application/json")
    if data:
        req.add_header("Content-Type", "application/json")
    if token:
        req.add_header("Authorization", f"Bearer {token}")

    try:
        with urllib.request.urlopen(req) as resp:
            code, raw = resp.status, resp.read().decode()
    except urllib.error.HTTPError as e:
        code, raw = e.code, e.read().decode()

    try:
        payload = json.loads(raw)
    except json.JSONDecodeError:
        payload = {"raw": raw[:300]}

    ok = code == expect
    results.append({
        "label": label, "method": method, "path": path, "expect": expect,
        "actual": code, "ok": ok, "response": payload, "note": note,
    })
    print(f"{'PASS' if ok else 'FAIL'}  {code:>3} (expect {expect})  {method} {path}  {label}")
    return payload if ok else {}


# 1. Register
reg = call("POST", "/auth/register", {
    "name": f"Uji Coba {STAMP}", "email": f"uji{STAMP}@kantin.test", "password": "password123",
}, expect=201, label="Register user baru")
new_token = reg.get("data", {}).get("access_token")

# 2. Login
login = call("POST", "/auth/login", CUSTOMER, label="Login dan dapat token")
token = login.get("data", {}).get("access_token", new_token)

# 3. Me
call("GET", "/auth/me", token=token, label="Profil dari token")

# 4. Products
products = call("GET", "/products?limit=3", label="Daftar produk + pagination meta")
items = products.get("data", [])
first_id = items[0]["product_id"] if items else 1
second_id = items[1]["product_id"] if len(items) > 1 else first_id

# 5. Product detail
call("GET", f"/products/{first_id}", label="Detail produk")

# 6. Order valid
order = call("POST", "/orders", {
    "items": [{"product_id": first_id, "quantity": 2}, {"product_id": second_id, "quantity": 1}],
    "pickup_time": datetime.now().strftime("%Y-%m-%dT%H:%M:%S"),
}, token=token, expect=201, label="Buat pesanan valid")
order_id = order.get("data", {}).get("order_id")

# 7. My orders
call("GET", "/orders", token=token, label="Daftar pesanan milik sendiri")

# 8. Order invalid -> 422
call("POST", "/orders", {"items": []}, token=token, expect=422,
     label="Validasi: items kosong", note="422 + detail errors")

# 9. Tanpa token -> 401
call("GET", "/orders", expect=401, label="Tanpa token", note="401 Unauthorized")

# 10. Logout
call("POST", "/auth/logout", token=new_token or token, label="Logout")

# --- tambahan: 404, 403, cart, payment ---
call("GET", "/products/999999", expect=404, label="Produk tidak ada", note="404 Not Found")

admin = call("POST", "/auth/login", ADMIN, label="Login admin")
adm_token = admin.get("data", {}).get("access_token", "")

call("POST", "/products", {
    "category_id": 1, "product_name": "Produk Tanpa Hak", "price": 1000, "stock": 1,
}, token=token, expect=403, label="Customer buat produk", note="403 Forbidden")

call("POST", "/products", {
    "category_id": 1, "product_name": f"Menu Uji {STAMP}", "description": "Dibuat saat smoke test",
    "price": 12500, "stock": 10, "image_url": "/images/uji.jpg",
}, token=adm_token, expect=201, label="Admin buat produk", note="201 Created")
new_product = call("GET", f"/products?search=Menu%20Uji%20{STAMP}", label="Cari produk baru")
new_product_id = (new_product.get("data") or [{}])[0].get("product_id", first_id)

call("POST", "/cart/items", {"product_id": new_product_id, "quantity": 2}, token=token,
     expect=201, label="Tambah item ke keranjang")
cart = call("GET", "/cart", token=token, label="Lihat keranjang")

call("POST", f"/orders/{order_id}/payments", {"payment_method": "qris"}, token=token,
     expect=201, label="Buat pembayaran", note="201 Created")
call("GET", f"/orders/{order_id}/payment", token=token, label="Detail pembayaran")
call("PATCH", f"/orders/{order_id}/status", {"status": "processing", "note": "Diproses dapur"},
     token=adm_token, label="Admin ubah status")
call("GET", f"/orders/{order_id}/status-history", token=token, label="Riwayat status")
call("PATCH", f"/orders/{order_id}/status", {"status": "ngawur"}, token=adm_token, expect=422,
     label="Status tidak valid", note="422")
call("GET", "/admin/orders", token=adm_token, label="Admin lihat semua pesanan")
call("GET", "/admin/summary", token=adm_token, label="Ringkasan dashboard")
call("GET", "/categories", label="Daftar kategori")

# ---------- tulis laporan ----------
passed = sum(1 for r in results if r["ok"])
lines = [
    "# Testing Results — KANTINKU API",
    "",
    f"- Base URL: `{BASE}`",
    f"- Waktu eksekusi: {datetime.now().isoformat(timespec='seconds')}",
    f"- Hasil: **{passed}/{len(results)} test lulus**",
    "- Dijalankan dari `docs/postman/run_tests.py` (isi tabel = response asli, bukan tulisan tangan)",
    "",
    "| # | Test | Request | Expected | Actual | Status |",
    "|---|---|---|---|---|---|",
]
for i, r in enumerate(results, 1):
    lines.append(
        f"| {i} | {r['label']} | `{r['method']} {r['path']}` | {r['expect']} | {r['actual']} | "
        f"{'PASS' if r['ok'] else 'FAIL'} |"
    )

lines += ["", "## Contoh Response", ""]
for r in results:
    lines += [
        f"### {r['label']} — {r['method']} `{r['path']}`",
        "",
        "```json",
        json.dumps(r["response"], indent=2, ensure_ascii=False)[:1200],
        "```",
        "",
    ]

with open("docs/testing_results.md", "w", encoding="utf-8") as fh:
    fh.write("\n".join(lines))

print(f"\n{passed}/{len(results)} lulus → docs/testing_results.md")
sys.exit(0 if passed == len(results) else 1)
