<?php
/*
 * NEGAIBOSHI.SITE Ver.4.5 NASA Moon Edition
 * NASA SVS CGI Moon Kit / LRO color map proxy & cache
 * Source: https://svs.gsfc.nasa.gov/4720/
 */

declare(strict_types=1);

$source = 'https://svs.gsfc.nasa.gov/vis/a000000/a004700/a004720/lroc_color_2k.jpg';
$cacheDir = __DIR__ . '/../cache';
$cacheFile = $cacheDir . '/nasa_lro_moon_2k.jpg';
$fallback = __DIR__ . '/../image/moon_ultimate_equirectangular.jpg';
$maxAge = 30 * 24 * 60 * 60;

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

function validJpeg(string $path): bool {
    if (!is_file($path) || filesize($path) < 10000) return false;
    $fh = @fopen($path, 'rb');
    if (!$fh) return false;
    $head = fread($fh, 2);
    fclose($fh);
    return $head === "\xFF\xD8";
}

function downloadFile(string $url, string $dest): bool {
    $tmp = $dest . '.tmp';
    @unlink($tmp);

    if (function_exists('curl_init')) {
        $fp = @fopen($tmp, 'wb');
        if ($fp) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_USERAGENT => 'NEGAIBOSHI.SITE/4.5 NASA Moon Edition',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FAILONERROR => true,
            ]);
            $ok = curl_exec($ch) === true;
            curl_close($ch);
            fclose($fp);
            if ($ok && validJpeg($tmp)) {
                return @rename($tmp, $dest);
            }
        }
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 25,
            'follow_location' => 1,
            'user_agent' => 'NEGAIBOSHI.SITE/4.5 NASA Moon Edition',
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $data = @file_get_contents($url, false, $context);
    if ($data !== false && strlen($data) > 10000 && substr($data, 0, 2) === "\xFF\xD8") {
        return @file_put_contents($dest, $data, LOCK_EX) !== false;
    }
    @unlink($tmp);
    return false;
}

$needsRefresh = !validJpeg($cacheFile) || (time() - (int)@filemtime($cacheFile)) > $maxAge;
if ($needsRefresh) {
    downloadFile($source, $cacheFile);
}

$file = validJpeg($cacheFile) ? $cacheFile : $fallback;
if (!is_file($file)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
header('X-Moon-Texture: ' . ($file === $cacheFile ? 'NASA-LRO' : 'local-fallback'));
header('Content-Length: ' . filesize($file));
readfile($file);
