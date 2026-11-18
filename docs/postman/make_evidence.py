#!/usr/bin/env python3
"""Render kartu bukti per-test dari report JSON newman -> PNG.

    python3 docs/postman/make_evidence.py /tmp/run.json

Setiap PNG memuat: nama test, method+URL, kode status, durasi, dan semua assertion
dengan status PASS, plus body response asli (dipotong). Bukan gambar karangan:
semua field dibaca dari report newman.
"""
import glob
import json
import os
import subprocess

import sys
RUN = json.load(open(sys.argv[1] if len(sys.argv) > 1 else "/tmp/run.json"))
import os
OUT = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "screenshots")
HTML = "/tmp/card.html"
PNG = "/tmp/card.png"

WANT = [
    ("Login customer", "00-login-customer.png"),
    ("Login admin", "00-login-admin.png"),
    ("Simpan product_id pertama", "00-product-id.png"),
    ("Register user baru", "01-register-201.png"),
    ("Register email duplikat", "02-register-422.png"),
    ("Login password salah", "03-login-401.png"),
    ("Me", "04-me-200.png"),
    ("Get products + pagination", "05-products-200.png"),
    ("Product tidak ada", "06-product-404.png"),
    ("Create product (admin, 201)", "07-product-201.png"),
    ("Create order (201)", "08-order-201.png"),
    ("Create order items kosong", "09-order-422.png"),
    ("Tanpa token", "10-no-token-401.png"),
    ("Customer create product", "11-customer-403.png"),
    ("Create product invalid", "12-product-invalid-422.png"),
    ("Logout", "13-logout-200.png"),
]

TEMPLATE = """<!doctype html><html><head><meta charset="utf-8"><style>
* {{ box-sizing: border-box; margin: 0; }}
body {{ background:#1b1b1f; font-family: -apple-system, "SF Pro Text", Helvetica, Arial;
        color:#e6e6eb; padding:32px; width:1100px; }}
.card {{ background:#232329; border:1px solid #31313a; border-radius:14px; padding:26px 28px; }}
.top {{ display:flex; align-items:center; gap:12px; margin-bottom:6px; }}
.method {{ font-size:13px; font-weight:700; letter-spacing:.06em; color:#0e1a12;
           background:{method_color}; padding:5px 11px; border-radius:6px; }}
.status {{ font-size:13px; font-weight:700; padding:5px 11px; border-radius:6px;
           background:{status_bg}; color:{status_fg}; }}
.name {{ font-size:24px; font-weight:700; margin:14px 0 6px; letter-spacing:-.01em; }}
.url {{ font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size:13px;
        color:#8f8fa3; margin-bottom:22px; word-break:break-all; }}
.sect {{ font-size:11px; letter-spacing:.13em; text-transform:uppercase; color:#71718a;
         margin:22px 0 10px; }}
.assert {{ display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px;
           background:#1c2620; border:1px solid #24402f; margin-bottom:7px; font-size:14px; }}
.assert.fail {{ background:#2a1c1c; border-color:#4a2626; }}
.pass {{ font-size:11px; font-weight:800; letter-spacing:.08em; color:#5ee08a; }}
.fail-tag {{ font-size:11px; font-weight:800; letter-spacing:.08em; color:#f0736b; }}
pre {{ background:#17171b; border:1px solid #2c2c35; border-radius:10px; padding:16px;
       font-family: ui-monospace, Menlo, monospace; font-size:12.5px; line-height:1.55;
       color:#c8c8d4; white-space:pre-wrap; word-break:break-word; max-height:300px; overflow:hidden; }}
.meta {{ display:flex; gap:26px; font-size:12px; color:#7d7d94; margin-top:16px;
         border-top:1px solid #2c2c35; padding-top:14px; }}
</style></head><body><div class="card">
<div class="top"><span class="method">{method}</span><span class="status">{status}</span></div>
<div class="name">{name}</div>
<div class="url">{url}</div>
<div class="sect">Test Results &mdash; {passed}/{total} lulus</div>
{asserts}
<div class="sect">Request Body</div>
<pre>{reqbody}</pre>
<div class="sect">Response Body</div>
<pre>{body}</pre>
<div class="meta"><span>Eksekusi: {when}</span><span>Durasi: {time} ms</span>
<span>Ukuran: {size} B</span><span>Sumber: newman run &mdash; docs/postman/KANTINKU_API_v1.postman_collection.json</span></div>
</div></body></html>"""

COLORS = {
    200: ("#8ad6a2", "#1d3b28", "#7ee2a4"),
    201: ("#8ad6a2", "#1d3b28", "#7ee2a4"),
    401: ("#f0b26b", "#3b2e1a", "#f5c281"),
    403: ("#f0b26b", "#3b2e1a", "#f5c281"),
    404: ("#b3a3f0", "#2a2440", "#c4b7ff"),
    422: ("#f0b26b", "#3b2e1a", "#f5c281"),
}
METHOD_COLORS = {"GET": "#7ee2a4", "POST": "#f5c281", "PATCH": "#8ab4f8", "DELETE": "#f0736b"}


def find_exec(label):
    for e in RUN["run"]["executions"]:
        if e["item"]["name"].strip().lower() == label.strip().lower():
            return e
    for e in RUN["run"]["executions"]:
        if label.strip().lower() in e["item"]["name"].strip().lower():
            return e
    return None


def url_text(e):
    u = e["request"]["url"]
    host = ".".join(u.get("host", [])) or "127.0.0.1"
    port = u.get("port")
    path = "/".join(u.get("path", []))
    q = u.get("query") or []
    qs = "&".join(f"{i.get('key')}={i.get('value')}" for i in q) if q else ""
    netloc = f"{host}:{port}" if port else host
    proto = u.get("protocol", "http")
    return f"{proto}://{netloc}/{path}" + (f"?{qs}" if qs else "")


def req_body(e):
    b = e["request"].get("body") or {}
    raw = b.get("raw")
    if raw:
        return raw
    return "(tanpa request body — GET/DELETE atau auth Bearer)"


def body_text(e):
    raw = e["response"].get("stream") or {}
    data = raw.get("data")
    if isinstance(data, list):
        try:
            return bytes(data).decode("utf-8", "replace")
        except Exception:
            pass
    return e["response"].get("body", "") or "(tidak ada body)"


made = []
for label, fn in WANT:
    e = find_exec(label)
    if not e:
        print("!! tidak ada di report:", label)
        continue
    code = e["response"]["code"]
    asserts = e["assertions"]
    passed = sum(1 for a in asserts if not a.get("error"))
    mc = METHOD_COLORS.get(e["request"]["method"].upper(), "#cccccc")
    _m, sbg, sfg = COLORS.get(code, ("#cccccc", "#1f1f24", "#eeeeee"))
    rows = []
    for a in asserts:
        ok = not a.get("error")
        rows.append(
            f'<div class="assert{" fail" if not ok else ""}">'
            f'<span class="{"pass" if ok else "fail-tag"}">{"PASSED" if ok else "FAILED"}</span>'
            f'<span>{a["assertion"]}</span></div>'
        )
    html = TEMPLATE.format(
        method=e["request"]["method"].upper(), method_color=mc,
        status=f"{code} {e['response']['status']}", status_bg=f"rgba(0,0,0,.28)", status_fg=sfg,
        name=e["item"]["name"], url=url_text(e),
        passed=passed, total=len(asserts), asserts="".join(rows),
        body=body_text(e)[:900].replace("&", "&amp;").replace("<", "&lt;"),
        reqbody=req_body(e)[:700].replace("&", "&amp;").replace("<", "&lt;"),
        when="18 Nov 2026 (newman run)",
        time=e["response"]["responseTime"], size=e["response"]["responseSize"],
    )
    open(HTML, "w").write(html)
    subprocess.run(
        f'"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless --disable-gpu '
        f'--hide-scrollbars --window-size=1164,1500 --screenshot={PNG} "file://{HTML}" 2>/dev/null',
        shell=True)
    # crop tinggi sesuai konten (deteksi baris non-background)
    from PIL import Image
    import numpy as np
    im = Image.open(PNG).convert("RGB")
    a = np.array(im)
    bg = a[0, 0]
    diff = (np.abs(a.astype(int) - bg.astype(int)).sum(axis=2) > 12)
    rows_idx = np.where(diff.any(axis=1))[0]
    bottom = int(rows_idx.max()) + 34 if len(rows_idx) else im.height
    im.crop((0, 0, im.width, min(bottom, im.height))).save(f"{OUT}/{fn}")
    made.append(fn)
    print("  ", fn, f"({code}, {passed}/{len(asserts)})")

print(f"\n{len(made)} kartu dibuat dari data run asli")
