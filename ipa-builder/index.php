<?php
declare(strict_types=1);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#080b12"><title>Fizer IPA Builder</title>
<style>
:root{color-scheme:dark;font-family:Tahoma,Arial,sans-serif}*{box-sizing:border-box}body{margin:0;min-height:100vh;background:radial-gradient(ellipse at 15% 0%,#183b57 0,transparent 42%),radial-gradient(ellipse at 90% 90%,#302050 0,transparent 38%),#080b12;color:#f5f7fb;display:grid;place-items:center;padding:24px}.panel{width:min(100%,680px);padding:30px;border:1px solid #ffffff20;border-radius:28px;background:#111827d9;box-shadow:0 24px 90px #0008;backdrop-filter:blur(20px)}.brand{display:flex;align-items:center;gap:12px;margin-bottom:22px}.logo{width:46px;height:46px;border-radius:15px;background:linear-gradient(135deg,#54d6c7,#6875ff);display:grid;place-items:center;font-weight:900;font-size:23px;color:#07111b}.muted{color:#9caec4;line-height:1.9}.field{margin:18px 0}label{display:block;margin-bottom:9px;font-size:14px;color:#dce7f5}input{width:100%;padding:15px 16px;border:1px solid #ffffff20;border-radius:14px;background:#080f1c;color:white;font:inherit;outline:none}input:focus{border-color:#66c8e8;box-shadow:0 0 0 3px #66c8e822}.hint{font-size:12px;color:#8da0b7;margin-top:7px}.btn{width:100%;border:0;border-radius:15px;padding:16px;background:linear-gradient(100deg,#42c9bb,#6477ff);color:#06111b;font:700 16px Tahoma;cursor:pointer;margin-top:12px}.btn:disabled{opacity:.5;cursor:wait}.status{display:none;margin-top:20px;padding:15px;border-radius:14px;background:#081321;border:1px solid #ffffff18;line-height:1.9;overflow-wrap:anywhere}.status a{color:#73d9ee}.foot{font-size:12px;color:#8191a7;text-align:center;margin-top:22px}
</style></head>
<body><main class="panel">
<div class="brand"><div class="logo">F</div><div><strong>Fizer IPA Builder</strong><div class="muted" style="font-size:12px">ساخت نسخه iOS از وب‌سایت شما</div></div></div>
<h2>ساخت اپلیکیشن آیفون</h2><p class="muted">مشخصات برنامه را وارد کن. فرایند ساخت در GitHub Actions اجرا می‌شود و فایل خروجی به‌صورت Artifact ارائه خواهد شد.</p>
<form id="buildForm">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8')?>">
<div class="field"><label for="url">آدرس کامل وب‌سایت</label><input id="url" name="url" type="url" placeholder="https://example.com" required></div>
<div class="field"><label for="name">نام اپلیکیشن</label><input id="name" name="app_name" maxlength="30" placeholder="مثلاً اف Ai" required></div>
<div class="field"><label for="bundle">شناسه برنامه (Bundle ID)</label><input id="bundle" name="bundle_id" value="ir.fizer.webapp" pattern="[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)+" required><div class="hint">نمونه: ir.fizer.myapp — باید در حساب توسعه‌دهنده اپل با شناسه امضا هماهنگ باشد.</div></div>
<button class="btn" id="submitBtn" type="submit">شروع ساخت IPA</button>
</form>
<div class="status" id="status" role="status"></div>
<div class="foot">ساخت IPA ممکن است چند دقیقه طول بکشد. نصب روی آیفون به امضای معتبر نیاز دارد.</div>
</main>
<script>
const form=document.getElementById('buildForm'),btn=document.getElementById('submitBtn'),statusBox=document.getElementById('status');
form.addEventListener('submit',async e=>{e.preventDefault();btn.disabled=true;btn.textContent='در حال ارسال درخواست…';statusBox.style.display='block';statusBox.textContent='در حال اتصال امن به GitHub…';
try{const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(form)))});const data=await r.json();if(!r.ok)throw new Error(data.error||'خطا در ثبت درخواست');statusBox.innerHTML='درخواست ساخت ثبت شد. برای مشاهده وضعیت و دریافت خروجی از این لینک استفاده کن: <br><a target="_blank" rel="noopener" href="'+data.actions_url+'">مشاهده فرایند ساخت در GitHub Actions</a><br><span class="hint">پس از پایان موفق، از بخش Artifacts فایل IPA را دریافت کن.</span>';btn.textContent='درخواست جدید';}catch(err){statusBox.textContent='خطا: '+err.message;btn.textContent='تلاش دوباره';}finally{btn.disabled=false;}
});
</script></body></html>