#!/usr/bin/env python3
"""ساخت پروژه اندروید از روی android-template و فایل‌های دریافت‌شده در work/.

ورودی‌ها از متغیرهای محیطی: APP_NAME, PACKAGE_NAME
خروجی: پوشه build/ آماده برای gradle assembleRelease
"""
import os
import re
import shutil
import sys
from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parent.parent
WORK = ROOT / "work"
BUILD = ROOT / "build"
TEMPLATE = ROOT / "android-template"

DENSITIES = {"mdpi": 48, "hdpi": 72, "xhdpi": 96, "xxhdpi": 144, "xxxhdpi": 192}
PACKAGE_RE = re.compile(r"^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$")


def die(msg: str) -> None:
    print(f"::error::{msg}")
    sys.exit(1)


def android_string(value: str) -> str:
    """نام را برای قرار گرفتن امن در strings.xml آماده می‌کند."""
    value = value.replace("\\", "\\\\")
    value = value.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    value = value.replace("'", "\\'").replace('"', '\\"')
    if value.startswith(("@", "?")):
        value = "\\" + value
    return value


def default_icon(size: int = 512) -> Image.Image:
    img = Image.new("RGBA", (size, size), (37, 169, 138, 255))
    d = ImageDraw.Draw(img)
    pad = size // 4
    d.ellipse((pad, pad, size - pad, size - pad), fill=(255, 255, 255, 255))
    return img


def main() -> None:
    name = os.environ.get("APP_NAME", "").strip()
    package = os.environ.get("PACKAGE_NAME", "").strip()
    if not (1 <= len(name) <= 40):
        die("نام اپلیکیشن باید بین ۱ تا ۴۰ نویسه باشد.")
    if not PACKAGE_RE.match(package):
        die("شناسه بسته معتبر نیست.")
    html_path = WORK / "index.html"
    if not html_path.is_file():
        die("فایل index.html در work/ پیدا نشد.")

    if BUILD.exists():
        shutil.rmtree(BUILD)
    shutil.copytree(TEMPLATE, BUILD)

    res = BUILD / "app" / "src" / "main" / "res"
    assets = BUILD / "app" / "src" / "main" / "assets"
    assets.mkdir(parents=True, exist_ok=True)
    shutil.copy(html_path, assets / "index.html")

    (res / "values" / "strings.xml").write_text(
        '<?xml version="1.0" encoding="utf-8"?>\n<resources>\n'
        f'    <string name="app_name">{android_string(name)}</string>\n'
        "</resources>\n",
        encoding="utf-8",
    )

    logo_path = WORK / "logo.png"
    if logo_path.is_file():
        logo = Image.open(logo_path).convert("RGBA")
    else:
        logo = default_icon()
    w, h = logo.size
    side = min(w, h)
    logo = logo.crop(((w - side) // 2, (h - side) // 2, (w - side) // 2 + side, (h - side) // 2 + side))
    for dens, px in DENSITIES.items():
        folder = res / f"mipmap-{dens}"
        folder.mkdir(parents=True, exist_ok=True)
        logo.resize((px, px), Image.LANCZOS).save(folder / "ic_launcher.png", "PNG")

    print(f"پروژه آماده شد: {name} / {package}")


if __name__ == "__main__":
    main()
