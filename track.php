<?php
// 本番WEBサーバー用 24時間アクセス自動集計プログラム (track.php)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$logFile = __DIR__ . '/access_logs.json';

// 日本時間の現在日時を秒単位で取得
date_default_timezone_set('Asia/Tokyo');
$formattedTime = date('Y/m/d H:i:s');
$timestamp = date('c');

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device = 'PC / ブラウザ';
if (preg_match('/iPhone|Android.*Mobile/i', $userAgent)) {
    $device = 'スマホ (Mobile)';
} elseif (preg_match('/iPad|Android/i', $userAgent)) {
    $device = 'タブレット';
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$logEntry = [
    'id' => 'log_' . round(microtime(true) * 1000),
    'page' => 'datsusenno.html',
    'timestamp' => $timestamp,
    'formattedTime' => $formattedTime,
    'device' => $device,
    'ip' => $ip
];

// ログ保存処理
$logs = [];
if (file_exists($logFile)) {
    $raw = file_get_contents($logFile);
    $logs = json_decode($raw, true) ?: [];
}

array_unshift($logs, $logEntry);

// 最新1000件まで保持
if (count($logs) > 1000) {
    $logs = array_slice($logs, 0, 1000);
}

file_put_contents($logFile, json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(['status' => 'success', 'logged' => $logEntry]);
?>
