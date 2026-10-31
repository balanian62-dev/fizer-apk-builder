#!/usr/bin/env python3
"""دریافت فایل‌های برنامه (HTML و لوگو) از سایت Fizer.

اگر PAYLOAD_URL خالی باشد (حالت آزمایشی)، از پوشه sample/ استفاده می‌شود.
خروجی در پوشه work/ نوشته می‌شود.
"""
import os
import shutil
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

MAX_HTML = 3 * 1024 * 1024
MAX_LOGO = 2 * 1024 * 1024
ROOT = Path(__file__).resolve().parent.parent
WORK = ROOT / "work"


def die(msg: str) -> None:
    print(f"::error::{msg}")
    sys.exit(1)


def fetch(base: str, job: str, token: str, kind: str, limit: int) -> bytes:
    query = urllib.parse.urlencode({"action": "payload", "job": job, "file": kind, "t": token})
    url = f"{base}{'&' if '?' in base else '?'}{query}"
    req = urllib.request.Request(url, headers={"User-Agent": "fizer-apk-builder/1.0"})
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            data = resp.read(limit + 1)
    except urllib.error.HTTPError as e:
        die(f"دریافت {kind} از سایت شکست خورد (HTTP {e.code}). اگر هاست شما IP خارجی را مسدود می‌کند، فایروال هاست را بررسی کنید.")
    except Exception as e:  # noqa: BLE001
        die(f"دریافت {kind} از سایت شکست خورد: {type(e).__name__}")
    if len(data) > limit:
        die(f"فایل {kind} بزرگ‌تر از حد مجاز است.")
    if not data:
        die(f"فایل {kind} خالی است.")
    return data


def main() -> None:
    WORK.mkdir(exist_ok=True)
    base = os.environ.get("PAYLOAD_URL", "").strip()
    job = os.environ["JOB_ID"]
    token = os.environ.get("PAYLOAD_TOKEN", "")

    if not base:
        print("حالت آزمایشی: استفاده از sample/")
        shutil.copy(ROOT / "sample" / "index.html", WORK / "index.html")
        (WORK / "logo.png").unlink(missing_ok=True)  # آیکون پیش‌فرض بعداً ساخته می‌شود
        return

    if not base.startswith("https://"):
        die("payload_url باید با https:// شروع شود.")
    if not token:
        die("payload_token خالی است.")

    html = fetch(base, job, token, "html", MAX_HTML)
    logo = fetch(base, job, token, "logo", MAX_LOGO)
    if b"\x00" in html:
        die("فایل HTML معتبر نیست.")
    if not logo.startswith(b"\x89PNG\r\n\x1a\n"):
        die("لوگو باید PNG باشد (سایت آن را تبدیل می‌کند).")
    (WORK / "index.html").write_bytes(html)
    (WORK / "logo.png").write_bytes(logo)
    print(f"HTML: {len(html)} بایت، لوگو: {len(logo)} بایت")


if __name__ == "__main__":
    main()
