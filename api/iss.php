<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
    exit;
}

$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/iss.json';

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$apis = [
    'https://api.wheretheiss.at/v1/satellites/25544',
    'http://api.open-notify.org/iss-now.json',
];

$errors = [];

foreach ($apis as $url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 7,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 Negaiboshi-ISS/10.0',
        ],
    ]);

    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($body === false || $status < 200 || $status >= 300) {
        $errors[] = [
            'url' => $url,
            'status' => $status,
            'detail' => $error,
        ];
        continue;
    }

    $data = json_decode($body, true);

    if (!is_array($data)) {
        $errors[] = [
            'url' => $url,
            'status' => $status,
            'detail' => 'JSON解析に失敗しました。',
        ];
        continue;
    }

    if (isset($data['iss_position'])) {
        $output = [
            'name' => 'iss',
            'latitude' => (float)($data['iss_position']['latitude'] ?? 0),
            'longitude' => (float)($data['iss_position']['longitude'] ?? 0),
            'altitude' => 420.0,
            'velocity' => 27580.0,
            'visibility' => '',
            'timestamp' => (int)($data['timestamp'] ?? time()),
            '_source' => 'open-notify',
        ];
    } else {
        $output = [
            'name' => (string)($data['name'] ?? 'iss'),
            'latitude' => (float)($data['latitude'] ?? 0),
            'longitude' => (float)($data['longitude'] ?? 0),
            'altitude' => (float)($data['altitude'] ?? 420),
            'velocity' => (float)($data['velocity'] ?? 27580),
            'visibility' => (string)($data['visibility'] ?? ''),
            'timestamp' => (int)($data['timestamp'] ?? time()),
            '_source' => 'wheretheiss',
        ];
    }

    if (
        !is_finite($output['latitude']) ||
        !is_finite($output['longitude'])
    ) {
        continue;
    }

    @file_put_contents(
        $cacheFile,
        json_encode(
            $output,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );

    respond($output);
}

if (is_file($cacheFile)) {
    $cached = json_decode(
        (string)file_get_contents($cacheFile),
        true
    );

    if (is_array($cached)) {
        $cached['_cached'] = true;
        $cached['_source'] = 'server-cache';
        respond($cached);
    }
}

respond([
    'error' => true,
    'message' => 'ISS位置情報を取得できませんでした。',
    'attempts' => $errors,
], 502);
