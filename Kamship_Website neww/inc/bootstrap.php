<?php
/**
 * Kamship Global — application bootstrap.
 * Included by every public page and by the admin panel.
 */
declare(strict_types=1);

define('KAMSHIP', true);
define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');

mb_internal_encoding('UTF-8');

// Nepal time for every timestamp the admin panel shows.
date_default_timezone_set('Asia/Kathmandu');

// ---------------------------------------------------------------- errors --
// Never show PHP errors to visitors; log them instead. Set KAMSHIP_DEBUG=1 in
// the environment (or in .htaccess) while developing.
$debug = getenv('KAMSHIP_DEBUG') === '1';
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED);

// ---------------------------------------------------------------- session --
function ks_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // A session started after output has begun cannot set its cookie, which
    // silently breaks every CSRF check on the page. Fail loudly in the log.
    if (headers_sent($file, $line)) {
        error_log("kamship: session started too late, after output at $file:$line");
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('kamship_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ------------------------------------------------------------ escaping ----
/** Escape for HTML text and attributes. Use for anything a person typed. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Editor-authored text that may contain a small, fixed set of inline tags.
 * Everything else is escaped. This is what lets the admin panel keep the
 * <b>, <i> and &mdash; formatting already in the copy without opening the
 * door to script injection.
 */
function rich(?string $s): string
{
    // Drop script/style blocks with their contents first: strip_tags() would
    // remove the tags but leave the code inside as visible text.
    $clean = preg_replace(
        '#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1\s*>#is',
        '',
        (string) $s
    ) ?? '';
    $clean = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>#i', '', $clean) ?? '';

    $allowed = '<b><strong><i><em><br><a><sup><sub><small>';
    $clean = strip_tags($clean, $allowed);

    // Strip every attribute except href on <a>, and only allow safe schemes.
    $clean = preg_replace_callback('#<a\b([^>]*)>#i', static function ($m) {
        if (preg_match('#href\s*=\s*("|\')(.*?)\1#i', $m[1], $h)) {
            $url = trim(html_entity_decode($h[2], ENT_QUOTES, 'UTF-8'));
            $ok = (bool) preg_match('#^(https?://|mailto:|tel:|/|[A-Za-z0-9_\-./]+\.php|\#)#i', $url);
            if ($ok && stripos($url, 'javascript:') === false && stripos($url, 'data:') === false) {
                $ext = preg_match('#^https?://#i', $url) ? ' rel="noopener"' : '';
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $ext . '>';
            }
        }
        return '<a>';
    }, $clean);

    // Drop attributes from the other allowed tags.
    $clean = preg_replace('#<(b|strong|i|em|br|sup|sub|small)\b[^>]*>#i', '<$1>', $clean);

    return $clean;
}

/** Safe URL for an href the editor supplied. */
function url(?string $u): string
{
    $u = trim((string) $u);
    if ($u === '') {
        return '#';
    }
    if (preg_match('#^\s*(javascript|data|vbscript)\s*:#i', $u)) {
        return '#';
    }
    return e($u);
}

// ------------------------------------------------------------- utilities --
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'item';
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

/** Security headers for every response. */
function ks_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header_remove('X-Powered-By');

    // The pages ship no inline <script>; styles use a few inline style
    // attributes, so 'unsafe-inline' is needed for styles only.
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "script-src 'self'; "
        . "style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data:; "
        . "font-src 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'self'; "
        . "base-uri 'self'; "
        . "object-src 'none'"
    );
}

require_once __DIR__ . '/store.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/media.php';
