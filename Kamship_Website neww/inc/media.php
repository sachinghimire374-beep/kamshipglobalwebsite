<?php
/**
 * Image uploads for the admin panel: blog pictures, the logo, and anything
 * else the site shows.
 *
 * Images live in /assets/uploads so the browser can fetch them directly, which
 * is the opposite of the CV files in /uploads. That is deliberate: these are
 * meant to be public. The safety comes from validating the file is genuinely
 * an image, re-encoding it so nothing can hide inside, and storing it under a
 * name the uploader does not choose.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

const MEDIA_DIR = ROOT . '/assets/uploads';
const MEDIA_URL = 'assets/uploads';
const MEDIA_MAX_BYTES = 6 * 1024 * 1024;
const MEDIA_MAX_W = 2000;

/** @return array{0:?array,1:?string} [record, error] */
function media_store(array $file, string $prefix = 'img'): array
{
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'That image is larger than the 6 MB limit.'];
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
        return [null, 'That image could not be uploaded. Please try again.'];
    }
    if ((int) $file['size'] > MEDIA_MAX_BYTES) {
        return [null, 'That image is larger than the 6 MB limit.'];
    }

    $tmp = (string) $file['tmp_name'];

    // getimagesize both identifies the type and proves it parses as an image.
    $info = @getimagesize($tmp);
    if ($info === false) {
        return [null, 'That file is not an image we can read. Use JPG, PNG, WebP or GIF.'];
    }
    [$w, $h, $type] = $info;
    $allowed = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($allowed[$type])) {
        return [null, 'Use a JPG, PNG, WebP or GIF image.'];
    }
    if ($w < 1 || $h < 1 || $w > 12000 || $h > 12000) {
        return [null, 'That image has unusual dimensions. Please use something smaller.'];
    }

    $ext = $allowed[$type];
    if (!is_dir(MEDIA_DIR) && !@mkdir(MEDIA_DIR, 0755, true) && !is_dir(MEDIA_DIR)) {
        error_log('kamship: cannot create media directory');
        return [null, 'We could not store the image. Check that assets/uploads is writable.'];
    }

    $name = $prefix . '-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = MEDIA_DIR . '/' . $name;

    // Re-encode through GD where we can. This strips EXIF and anything else
    // riding along in the file, and resizes oversized uploads.
    $done = false;
    if (function_exists('imagecreatefromstring') && $type !== IMAGETYPE_GIF) {
        $raw = @file_get_contents($tmp);
        $src = $raw !== false ? @imagecreatefromstring($raw) : false;
        if ($src !== false) {
            if ($w > MEDIA_MAX_W) {
                $nh = (int) round($h * MEDIA_MAX_W / $w);
                $dst = imagecreatetruecolor(MEDIA_MAX_W, $nh);
                if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, MEDIA_MAX_W, $nh, $w, $h);
                imagedestroy($src);
                $src = $dst;
                $w = MEDIA_MAX_W;
                $h = $nh;
            }
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($src, false);
                imagesavealpha($src, true);
                $done = imagepng($src, $dest, 6);
            } elseif ($type === IMAGETYPE_WEBP) {
                $done = imagewebp($src, $dest, 82);
            } else {
                $done = imagejpeg($src, $dest, 84);
            }
            imagedestroy($src);
        }
    }
    if (!$done && !move_uploaded_file($tmp, $dest)) {
        error_log('kamship: could not store uploaded image');
        return [null, 'We could not store the image. Check that assets/uploads is writable.'];
    }
    @chmod($dest, 0644);

    return [[
        'file' => $name,
        'url'  => MEDIA_URL . '/' . $name,
        'w'    => $w,
        'h'    => $h,
        'size' => (int) filesize($dest),
        'original' => mb_substr(preg_replace('/[^\w .\-()]+/u', '_', (string) $file['name']) ?? '', 0, 120),
        'uploaded' => date('c'),
    ], null];
}

/** Every stored image, newest first. */
function media_all(): array
{
    if (!is_dir(MEDIA_DIR)) {
        return [];
    }
    $out = [];
    foreach (glob(MEDIA_DIR . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $f) {
        $out[] = [
            'file' => basename($f),
            'url'  => MEDIA_URL . '/' . basename($f),
            'size' => (int) filesize($f),
            'time' => (int) filemtime($f),
        ];
    }
    usort($out, static fn($a, $b) => $b['time'] <=> $a['time']);
    return $out;
}

/** Delete one image. The name is checked against the directory listing. */
function media_delete(string $file): bool
{
    $file = basename($file);
    $path = MEDIA_DIR . '/' . $file;
    $real = realpath($path);
    $base = realpath(MEDIA_DIR);
    if ($real === false || $base === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
        return false;
    }
    return @unlink($real);
}

/** Turn a stored value into a usable src, tolerating a full URL or a bare name. */
function media_src(?string $v): string
{
    $v = trim((string) $v);
    if ($v === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $v) || str_starts_with($v, 'assets/')) {
        return $v;
    }
    return MEDIA_URL . '/' . basename($v);
}
