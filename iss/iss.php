<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$url = 'https://api.wheretheiss.at/v1/satellites/25544';

function output_json(array $data, int $status = 200): never
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
$cacheFile = $cacheDir . '/iss_last.json';

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_ENCODING => '',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 Negaiboshi-ISS/8.0'
    ],
]);

$body = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($body !== false && $status >= 200 && $status < 300) {

    $data = json_decode($body, true);

    if (is_array($data)) {

        @file_put_contents(
            $cacheFile,
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ),
            LOCK_EX
        );

        output_json($data);
    }
}

/*
  外部接続が一時的に失敗した場合は、
  サーバーに保存された前回成功データを返します。
*/
if (is_file($cacheFile)) {

    $cachedBody =
    (string)file_get_contents(
        $cacheFile
    );

    $cached =
    json_decode(
        $cachedBody,
        true
    );

    if (is_array($cached)) {

        $cached['_cached'] = true;
        $cached['_message'] =
        '外部APIへ接続できなかったため、前回のデータを表示しています。';

        output_json($cached);
    }
}

output_json([
    'error' => true,
    'message' => 'ISS位置情報を取得できませんでした。',
    'status' => $status,
    'detail' => $error,
], 502);
