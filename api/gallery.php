<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

$type = strtolower((string)($_GET['type'] ?? 'webb'));
$queries = [
    'webb' => 'James Webb Space Telescope',
    'hubble' => 'Hubble Space Telescope',
];

if (!isset($queries[$type])) {
    json_response(['error' => true, 'message' => '種類が正しくありません。'], 400);
}

$url = 'https://images-api.nasa.gov/search?media_type=image&page_size=9&q='
     . rawurlencode($queries[$type]);

$result = fetch_url($url);

if (!$result['ok']) {
    json_response([
        'error' => true,
        'message' => 'NASA画像ギャラリーとの通信に失敗しました。'
    ], 502);
}

$data = json_decode($result['body'], true);
$items = $data['collection']['items'] ?? [];
$output = [];

foreach ($items as $item) {
    $meta = $item['data'][0] ?? [];
    $links = $item['links'] ?? [];
    $preview = '';

    foreach ($links as $link) {
        if (($link['render'] ?? '') === 'image' && !empty($link['href'])) {
            $preview = (string)$link['href'];
            break;
        }
    }

    if ($preview === '') {
        continue;
    }

    $output[] = [
        'title' => (string)($meta['title'] ?? 'NASA Image'),
        'description' => (string)($meta['description_508'] ?? $meta['description'] ?? ''),
        'date' => substr((string)($meta['date_created'] ?? ''), 0, 10),
        'center' => (string)($meta['center'] ?? 'NASA'),
        'preview' => $preview,
    ];
}

json_response(['type' => $type, 'items' => $output]);
