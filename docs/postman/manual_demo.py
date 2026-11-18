import json
import urllib.request
import urllib.error

B = "http://127.0.0.1:8000/api/v1"


def hit(method, path, body=None, token=None):
    data = json.dumps(body).encode() if body else None
    req = urllib.request.Request(B + path, data=data, method=method)
    req.add_header("Accept", "application/json")
    if data:
        req.add_header("Content-Type", "application/json")
    if token:
        req.add_header("Authorization", "Bearer " + token)
    try:
        with urllib.request.urlopen(req) as r:
            return r.status, json.loads(r.read())
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read())


print("=== LANGKAH 1: POST /auth/login  (klik Send) ===")
code, body = hit("POST", "/auth/login", {"email": "riska@kantin.test", "password": "password123"})
print("status", code)
token = body["data"]["access_token"]
print("token", token[:22] + "...")
print("Panel Response menampilkan: status/message/data.access_token")

print("\n=== LANGKAH 2: salin token ke variable {{token}} ===")
print("(di Postman: tab collection > Variables > token > Tempel > Save)")

print("\n=== LANGKAH 3: GET /auth/me dengan Bearer {{token}} ===")
code, body = hit("GET", "/auth/me", token=token)
print("status", code, "|", body["message"], "|", body["data"]["email"])

print("\n=== LANGKAH 4: GET /products?limit=2 ===")
code, body = hit("GET", "/products?limit=2")
print("status", code, "| meta:", body["meta"])
print("produk pertama:", body["data"][0]["product_name"], body["data"][0]["price"])

print("\n=== LANGKAH 5: POST /orders (valid) ===")
code, body = hit("POST", "/orders", {"items": [{"product_id": 1, "quantity": 1}]}, token=token)
print("status", code, "|", body["message"])
if code == 201:
    print("order_code:", body["data"]["order_code"], "total:", body["data"]["total_amount"])

print("\n=== LANGKAH 6: POST /orders {\"items\": []} (harus 422) ===")
code, body = hit("POST", "/orders", {"items": []}, token=token)
print("status", code, "|", body["message"], "|", body["errors"])

print("\n=== LANGKAH 7: GET /orders tanpa token (harus 401) ===")
code, body = hit("GET", "/orders")
print("status", code, "|", body["message"])

print("\n=== LANGKAH 8: POST /auth/logout ===")
code, body = hit("POST", "/auth/logout", token=token)
print("status", code, "|", body["message"])

print("\n=== LANGKAH 9: GET /auth/me pakai token yang sudah logout (harus 401) ===")
code, body = hit("GET", "/auth/me", token=token)
print("status", code, "|", body["message"])
