# fizer-apk-builder

ساخت خودکار APK از HTML برای «اف Ai» با GitHub Actions.

## قابلیت‌های پوسته Android

- اجرای HTML داخل WebView با مبدأ محلی امن `appassets.androidplatform.net`.
- انتخاب فایل از طریق پنجره انتخاب‌گر اندروید (Storage Access Framework) برای `<input type="file">`؛ بدون درخواست مجوز دسترسی گسترده به حافظه.
- درخواست مجوز دوربین و میکروفون فقط وقتی صفحه HTML از قابلیت‌های رسانه‌ای WebView استفاده کند؛ تأیید نهایی با پنجره سیستمی اندروید است.
- اعلان محلی از طریق پل جاوااسکریپت، با درخواست مجوز اعلان در Android 13 و بالاتر.
- داده‌های WebView مانند localStorage و فایل‌های داخلی برنامه در فضای خصوصی اپ نگهداری می‌شوند؛ برنامه مجوز عمومی خواندن/نوشتن حافظه نمی‌گیرد.

### API اعلان برای HTML تولیدشده

در صفحه HTML می‌توان پس از اقدام کاربر از این تابع استفاده کرد:

```js
if (window.FizerNative) {
  FizerNative.requestNotification("عنوان اعلان", "متن اعلان");
}
```

این API برای اعلان محلی است؛ اعلان Push از سرور نیازمند پیاده‌سازی جداگانه است.

## ساختار

```
.github/workflows/build-apk.yml   گردش‌کار ساخت (workflow_dispatch)
android-template/                 پروژه اندروید (Java + WebView)
scripts/fetch_payload.py          دریافت HTML و لوگو از سایت با توکن یک‌بارمصرف
scripts/prepare_project.py        ساخت پروژه، strings.xml امن و آیکون‌ها
sample/index.html                 صفحه آزمایشی
```

## نسخه‌ها

Android Gradle Plugin 8.13.0 ، Gradle 8.13 ، JDK 17 ، compileSdk/targetSdk 35 ، minSdk 24 ، androidx.webkit 1.12.1 ، androidx.core 1.15.0.

## ورودی‌های workflow

`job_id` (۲۴ hex)، `app_name`، `package_name`، `version_code`، `payload_url`، `payload_token`.
اگر `payload_url` خالی باشد، `sample/index.html` ساخته می‌شود.

## رازهای لازم

در Settings ← Secrets and variables ← Actions این مقادیر لازم‌اند:

`KEYSTORE_BASE64`، `KEYSTORE_PASSWORD`، `KEY_ALIAS`، `KEY_PASSWORD`.

## امنیت

- ورودی‌های کاربر در shell command درج نمی‌شوند و پیش از ساخت اعتبارسنجی می‌شوند.
- HTML کاربر روی runner اجرا نمی‌شود؛ فقط به‌عنوان asset داخل APK قرار می‌گیرد.
- مجوزهای خطرپذیر فقط هنگام نیاز و با تأیید کاربر درخواست می‌شوند.
- فایل‌های انتخاب‌شده از طریق انتخاب‌گر سیستم و URI مجازشده دریافت می‌شوند.
- workflow به `contents: read` محدود است؛ کلید امضا پس از ساخت پاک می‌شود.
