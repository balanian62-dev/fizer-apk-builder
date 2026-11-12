<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
function fail(string $message, int $code=400): never { http_response_code($code); echo json_encode(['error'=>$message], JSON_UNESCAPED_UNICODE); exit; }
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) fail('ابتدا فایل config.php را طبق config.example.php بساز و توکن GitHub را در آن قرار بده.', 503);
$config = require $configFile;
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) fail('درخواست نامعتبر است.');
if (!hash_equals($_SESSION['csrf'] ?? '', (string)($data['csrf'] ?? ''))) fail('درخواست امنیتی معتبر نیست.', 403);
$url = trim((string)($data['url'] ?? ''));
$name = trim((string)($data['app_name'] ?? ''));
$bundle = trim((string)($data['bundle_id'] ?? ''));
$parts = parse_url($url);
if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['https'], true) || empty($parts['host']) || filter_var($url, FILTER_VALIDATE_URL) === false) fail('آدرس باید یک URL معتبر با https باشد.');
if (strlen($name) < 2 || strlen($name) > 30) fail('نام اپلیکیشن باید بین ۲ تا ۳۰ نویسه باشد.');
if (!preg_match('/^[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)+$/', $bundle)) fail('شناسه Bundle ID معتبر نیست.');
$owner = $config['owner'] ?? ''; $repo = $config['repo'] ?? ''; $token = $config['token'] ?? '';
if (!$owner || !$repo || !$token) fail('تنظیمات GitHub کامل نیست.', 503);
$payload = ['ref'=>'main','inputs'=>['website_url'=>$url,'app_name'=>$name,'bundle_id'=>$bundle]];
$ch = curl_init("https://api.github.com/repos/".rawurlencode($owner)."/".rawurlencode($repo)."/actions/workflows/build-ios-ipa.yml/dispatches");
curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Accept: application/vnd.github+json','Authorization: Bearer '.$token,'X-GitHub-Api-Version: 2022-11-28','User-Agent: Fizer-IPA-Builder','Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_TIMEOUT=>25]);
$response = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
if ($response === false) fail('ارتباط با GitHub برقرار نشد: '.$error, 502);
if ($status !== 204) fail('GitHub درخواست را نپذیرفت (HTTP '.$status.'). دسترسی Actions و نام مخزن را بررسی کن.', 502);
echo json_encode(['ok'=>true,'actions_url'=>"https://github.com/{$owner}/{$repo}/actions/workflows/build-ios-ipa.yml"], JSON_UNESCAPED_UNICODE);
