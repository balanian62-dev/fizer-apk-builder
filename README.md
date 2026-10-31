# fizer-apk-builder

ساخت خودکار APK از HTML برای «اف Ai» با GitHub Actions.

## ساختار

```
.github/workflows/build-apk.yml   گردش‌کار ساخت (workflow_dispatch)
android-template/                 پروژه‌ی اندروید (WebView + فایل HTML داخل assets)
scripts/fetch_payload.py          دریافت HTML و لوگو از سایت با توکن یک‌بارمصرف
scripts/prepare_project.py        ساخت پروژه، strings.xml امن و آیکون‌ها
sample/index.html                 صفحه‌ی آزمایشی (وقتی payload_url خالی است)
```

نسبت به ساختار پیشنهادی اولیه (`template/package.json` و Capacitor)، از یک پروژه‌ی ساده‌ی اندروید (Java + WebView) استفاده شده است؛ چون Node/npm/Capacitor لازم ندارد، وابستگی کمتری دارد و خطای کمتری می‌دهد.

## نسخه‌ها

Android Gradle Plugin 8.13.0 ، Gradle 8.13 ، JDK 17 ، compileSdk/targetSdk 35 ، minSdk 24 ، androidx.webkit 1.12.1.
اکشن‌ها: `actions/checkout@v4`, `actions/setup-java@v4`, `gradle/actions/setup-gradle@v4`, `actions/upload-artifact@v4`.

## ورودی‌های workflow

`job_id` (۲۴ hex) ، `app_name` ، `package_name` ، `version_code` ، `payload_url` ، `payload_token`.
اگر `payload_url` خالی باشد، `sample/index.html` ساخته می‌شود (آزمایش).

## رازهای لازم (Settings ← Secrets and variables ← Actions)

`KEYSTORE_BASE64` ، `KEYSTORE_PASSWORD` ، `KEY_ALIAS` ، `KEY_PASSWORD`

## امنیت

- ورودی‌های کاربر هرگز داخل دستور `run:` درج نمی‌شوند؛ فقط از طریق متغیر محیطی و پس از اعتبارسنجی regex.
- کد HTML کاربر در سرور ساخت **اجرا نمی‌شود**؛ فقط به‌عنوان فایل داخل `assets` کپی می‌شود.
- نام برنامه برای XML اندروید escape می‌شود؛ شناسه‌ی بسته فقط از الگوی مجاز می‌گذرد.
- دسترسی workflow: `contents: read`. کلید امضا پس از ساخت از دیسک ران‌نر حذف می‌شود.
- مخزن را Private نگه دارید.
