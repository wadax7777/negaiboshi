<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

$config = require dirname(__DIR__) . '/config.php';
$apiKey = trim((string)($config['nasa_api_key'] ?? ''));

if ($apiKey === '' || $apiKey === 'YOUR_NASA_API_KEY') {
    json_response([
        'error' => true,
        'configured' => false,
        'message' => 'NASA APIキーは最後にconfig.phpへ設定してください。'
    ]);
}

$url = 'https://api.nasa.gov/planetary/apod?api_key='
     . rawurlencode($apiKey)
     . '&thumbs=true';

$result = fetch_url($url);

if (!$result['ok']) {
    json_response([
        'error' => true,
        'message' => 'NASA APODとの通信に失敗しました。',
        'status' => $result['status']
    ], 502);
}

$data = json_decode($result['body'], true);

if (!is_array($data)) {
    json_response([
        'error' => true,
        'message' => 'NASAから正しいデータを受信できませんでした。'
    ], 502);
}

$keys = [
    'date', 'explanation', 'hdurl', 'media_type',
    'title', 'url', 'copyright', 'thumbnail_url'
];

$output = [];
foreach ($keys as $key) {
    if (array_key_exists($key, $data)) {
        $output[$key] = $data[$key];
    }
}

json_response($output);
