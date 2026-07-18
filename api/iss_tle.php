<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

$url = 'https://celestrak.org/NORAD/elements/gp.php?CATNR=25544&FORMAT=TLE';
$result = fetch_url($url, 20);

if (!$result['ok']) {
    json_response([
        'error' => true,
        'message' => 'CelesTrakからISS軌道データを取得できませんでした。',
        'status' => $result['status'],
        'detail' => $result['error'],
    ], 502);
}

$lines = preg_split('/\R/', trim($result['body']));
$lines = array_values(array_filter(array_map('trim', $lines)));

if (count($lines) < 2) {
    json_response([
        'error' => true,
        'message' => 'ISS軌道データの形式が正しくありません。'
    ], 502);
}

if (str_starts_with($lines[0], '1 ')) {
    $name = 'ISS (ZARYA)';
    $line1 = $lines[0];
    $line2 = $lines[1] ?? '';
} else {
    $name = $lines[0];
    $line1 = $lines[1] ?? '';
    $line2 = $lines[2] ?? '';
}

if (!str_starts_with($line1, '1 ') || !str_starts_with($line2, '2 ')) {
    json_response([
        'error' => true,
        'message' => 'ISSのTLEを確認できませんでした。'
    ], 502);
}

json_response([
    'name' => $name,
    'line1' => $line1,
    'line2' => $line2,
    'fetched_at' => gmdate('c'),
]);
