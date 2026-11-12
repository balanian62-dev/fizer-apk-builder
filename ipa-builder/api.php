<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

function fail(string $message, int $code=400): never {
    http_response_code($code);
    echo json_encode(['error'=>$message], JSON_UNESCAPED_UNICODE);
    exit;
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) fail('ابتدا فایل config.php را طبق config.example.php بساز و توکن GitHub را در آن قرار بده.', 503);
$config = require $configFile;

if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
    fail('درخواست امنیتی معتبر نیست؛ صفحه را تازه‌سازی و دوباره امتحان کن.', 403);
}

$url = trim((string)($_POST['url'] ?? ''));
$name = trim((string)($_POST['app_name'] ?? ''));
$bundle = trim((string)($_POST['bundle_id'] ?? ''));
$parts = parse_url($url);

if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || filter_var($url, FILTER_VALIDATE_URL) === false) {
    fail('آدرس باید یک URL معتبر با https باشد.');
}
if (strlen($name) < 2 || strlen($name) > 30) fail('نام اپلیکیشن باید بین ۲ تا ۳۰ نویسه باشد.');
if (!preg_match('/^[A-Za-z0-9-]+(?:\\.[A-Za-z0-9-]+)+$/', $bundle)) fail('شناسه Bundle ID معتبر نیست.');

if (!isset($_FILES['logo']) || !is_array($_FILES['logo'])) fail('لطفاً لوگوی برنامه را انتخاب کن.');
$logo = $_FILES['logo'];
if (($logo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    fail('آپلود لوگو انجام نشد. حجم فایل باید حداکثر ۵ مگابایت باشد.');
}
if (($logo['size'] ?? 0) < 1 || $logo['size'] > 5 * 1024 * 1024) fail('حجم لوگو باید کمتر از ۵ مگابایت باشد.');

$tmp = (string)$logo['tmp_name'];
$info = @getimagesize($tmp);
if (!$info || ($info['mime'] ?? '') !== 'image/png') fail('لوگو باید فایل PNG معتبر باشد.');
if ($info[0] !== $info[1] || $info[0] < 1024) fail('لوگو باید مربعی و حداقل ۱۰۲۴ در ۱۰۲۴ پیکسل باشد.');

$owner = $config['owner'] ?? '';
$repo = $config['repo'] ?? '';
$token = $config['token'] ?? '';
if (!$owner || !$repo || !$token) fail('تنظیمات GitHub کامل نیست.', 503);

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    fail('ساخت پوشه آپلود ممکن نشد؛ دسترسی نوشتن هاست را بررسی کن.', 500);
}
$htaccess = $uploadDir . '/.htaccess';
if (!is_file($htaccess)) @file_put_contents($htaccess, "Options -Indexes\n");

foreach (glob($uploadDir . '/*.png') ?: [] as $oldFile) {
    if (is_file($oldFile) && filemtime($oldFile) < time() - 86400) @unlink($oldFile);
}

$fileName = bin2hex(random_bytes(24)) . '.png';
$filePath = $uploadDir . '/' . $fileName;
if (!move_uploaded_file($tmp, $filePath)) fail('ذخیره لوگو روی هاست انجام نشد؛ مجوز پوشه uploads را بررسی کن.', 500);
$logoUrl = 'https://ipa.fizer.ir/uploads/' . $fileName;

$payload = [
    'ref' => 'main',
    'inputs' => [
        'website_url' => $url,
        'app_name' => $name,
        'bundle_id' => $bundle,
        'logo_url' => $logoUrl
    ]
];

$ch = curl_init("https://api.github.com/repos/" . rawurlencode($owner) . "/" . rawurlencode($repo) . "/actions/workflows/build-ios-ipa.yml/dispatches");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: Fizer-IPA-Builder',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 25
]);
$response = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    @unlink($filePath);
    fail('ارتباط با GitHub برقرار نشد: ' . $error, 502);
}
if (!in_array($status, [200, 204], true)) {
    @unlink($filePath);
    $githubData = json_decode((string)$response, true);
    $githubMessage = is_array($githubData) ? (string)($githubData['message'] ?? '') : '';
    $detail = $githubMessage !== '' ? ' پیام GitHub: ' . $githubMessage : '';
    fail('GitHub درخواست را نپذیرفت (HTTP ' . $status . ').' . $detail . ' نام مالک، مخزن، فایل workflow و دسترسی توکن را بررسی کن.', 502);
}

echo json_encode([
    'ok' => true,
    'actions_url' => "https://github.com/{$owner}/{$repo}/actions/workflows/build-ios-ipa.yml"
], JSON_UNESCAPED_UNICODE);
