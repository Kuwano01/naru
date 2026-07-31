<?php
/**
 * 脱洗脳LP (datsusenno.html) 超確実本番アクセス解析プログラム (track.php)
 * 
 * 使い方:
 *  1. 本ファイルをサーバー（ロリポップやエックスサーバー等）の datsusenno.html と同じ場所にアップロード。
 *  2. ブラウザで http://www.naru-mind-deprogram.com/track.php にアクセスすると、集計されたアクセス数と「何時何分何秒」のログ一覧が直接見られます！
 */

// エラー非表示（画像破壊防止）
error_reporting(0);
ini_set('display_errors', 0);

// 日本時間に設定
date_default_timezone_set('Asia/Tokyo');

// ログファイルパス
$logFile = __DIR__ . '/datsusenno_access_log.txt';

// --- 管理画面表示モード (ブラウザで直接 track.php を開いた場合) ---
if (isset($_GET['view']) || !isset($_GET['mode'])) {
    header('Content-Type: text/html; charset=utf-8');
    
    // ログの読み込み
    $logs = [];
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $logs[] = $data;
            }
        }
    }
    
    // 最新が上になるようソート
    $logs = array_reverse($logs);
    $totalClicks = count($logs);
    
    $todayStr = date('Y/m/d');
    $todayClicks = 0;
    foreach ($logs as $l) {
        if (isset($l['time']) && strpos($l['time'], $todayStr) === 0) {
            $todayClicks++;
        }
    }
    
    $lastTime = !empty($logs) ? $logs[0]['time'] : '--:--:--';

    // HTML管理画面を出力
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>脱洗脳LP アクセス解析ダッシュボード</title>
        <style>
            body { font-family: -apple-system, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; margin: 0; }
            .container { max-width: 900px; margin: 0 auto; }
            h1 { color: #a78bfa; font-size: 1.6rem; border-bottom: 1px solid #334155; padding-bottom: 0.8rem; }
            .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
            .card { background: #1e293b; border-radius: 12px; padding: 1.25rem; border: 1px solid #334155; text-align: center; }
            .num { font-size: 2.5rem; font-weight: 800; color: #38bdf8; margin: 0.3rem 0; }
            .time { font-size: 1.2rem; font-family: monospace; color: #34d399; margin: 0.5rem 0; }
            .table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; font-size: 0.9rem; }
            .table th { background: #334155; color: #94a3b8; text-align: left; padding: 0.75rem 1rem; }
            .table td { padding: 0.75rem 1rem; border-bottom: 1px solid #334155; }
            .badge { background: #0284c7; color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📊 脱洗脳LP (datsusenno.html) アクセス解析カウンター</h1>
            <p style="color: #94a3b8; font-size: 0.9rem;">対象URL: http://www.naru-mind-deprogram.com/datsusenno.html</p>
            
            <div class="grid">
                <div class="card">
                    <div style="color: #94a3b8; font-size: 0.85rem;">累計アクセス総数</div>
                    <div class="num"><?php echo $totalClicks; ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">回 閲覧されました</div>
                </div>
                <div class="card">
                    <div style="color: #94a3b8; font-size: 0.85rem;">本日のアクセス数</div>
                    <div class="num" style="color: #34d399;"><?php echo $todayClicks; ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">Today</div>
                </div>
                <div class="card">
                    <div style="color: #94a3b8; font-size: 0.85rem;">直近アクセス日時</div>
                    <div class="time"><?php echo htmlspecialchars($lastTime); ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">何時何分何秒</div>
                </div>
            </div>

            <h2>⏱️ アクセス日時履歴ログ（何時何分何秒）</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>アクセス日時 (何時何分何秒)</th>
                        <th>端末/デバイス</th>
                        <th>IPアドレス</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="3" style="text-align: center; color: #64748b; padding: 2rem;">アクセス記録はまだありません</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($logs, 0, 100) as $log): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: bold; color: #34d399;">
                                    <?php echo htmlspecialchars($log['time'] ?? ''); ?>
                                </td>
                                <td><span class="badge"><?php echo htmlspecialchars($log['device'] ?? '不明'); ?></span></td>
                                <td style="font-family: monospace; color: #64748b;"><?php echo htmlspecialchars($log['ip'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php
    exit(0);
}

// --- 計測タグ用ビーコン受信モード (imgタグやJSから叩かれた場合) ---
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device = 'PC / ブラウザ';
if (preg_match('/iPhone|Android.*Mobile/i', $userAgent)) {
    $device = 'スマホ (Mobile)';
} elseif (preg_match('/iPad|Android/i', $userAgent)) {
    $device = 'タブレット';
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$formattedTime = date('Y/m/d H:i:s');

$logData = [
    'time' => $formattedTime,
    'device' => $device,
    'ip' => $ip
];

// 1行テキストとして追記
@file_put_contents($logFile, json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);

// 1x1 透明GIF画像を返却（JavaScriptエラーを100%防ぐ）
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
exit(0);
