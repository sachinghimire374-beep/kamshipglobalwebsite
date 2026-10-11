<?php
/**
 * JSON data store.
 *
 * Small site, low write volume, no database needed. Writes are atomic
 * (write to a temp file in the same directory, then rename) so a crash or a
 * concurrent request can never leave a half-written content file, and reads
 * take a shared lock.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

function store_path(string $name): string
{
    // Fixed whitelist — never build this path from user input.
    $allowed = ['content', 'users', 'applications', 'enquiries', 'loginlog'];
    if (!in_array($name, $allowed, true)) {
        throw new InvalidArgumentException('Unknown store: ' . $name);
    }
    return DATA_DIR . '/' . $name . '.json';
}

function store_read(string $name, array $default = []): array
{
    $file = store_path($name);
    if (!is_file($file)) {
        return $default;
    }
    $fh = @fopen($file, 'rb');
    if ($fh === false) {
        error_log("kamship: cannot open $file for reading");
        return $default;
    }
    try {
        flock($fh, LOCK_SH);
        $raw = stream_get_contents($fh);
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
    if ($raw === false || trim($raw) === '') {
        return $default;
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_log("kamship: $file contains invalid JSON");
        return $default;
    }
    return $data;
}

function store_write(string $name, array $data): bool
{
    $file = store_path($name);
    $dir = dirname($file);

    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        error_log("kamship: cannot create $dir");
        return false;
    }

    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
    if ($json === false) {
        error_log('kamship: json_encode failed: ' . json_last_error_msg());
        return false;
    }

    // Atomic replace: a reader either sees the old file or the new one.
    $tmp = tempnam($dir, '.ks');
    if ($tmp === false) {
        error_log("kamship: cannot create temp file in $dir");
        return false;
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        @unlink($tmp);
        error_log("kamship: cannot write temp file for $file");
        return false;
    }
    @chmod($tmp, 0640);
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        error_log("kamship: cannot rename temp file over $file");
        return false;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($file, true);
    }
    return true;
}

/** Timestamped backup before a destructive change. Keeps the last 20. */
function store_backup(string $name): void
{
    $file = store_path($name);
    if (!is_file($file)) {
        return;
    }
    $dir = DATA_DIR . '/backups';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return;
    }
    @copy($file, $dir . '/' . $name . '-' . date('Ymd-His') . '.json');

    $old = glob($dir . '/' . $name . '-*.json') ?: [];
    if (count($old) > 20) {
        sort($old);
        foreach (array_slice($old, 0, count($old) - 20) as $f) {
            @unlink($f);
        }
    }
}

// ----------------------------------------------------------- content API --
/** The whole content tree, loaded once per request. */
function content(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = store_read('content');
    }
    return $cache;
}

/**
 * Read a value by dotted path, e.g. c('site.email') or c('home.stats').
 * Returns $default when the path is missing, so a page never fatals because
 * an editor deleted something.
 */
function c(string $path, mixed $default = ''): mixed
{
    $node = content();
    foreach (explode('.', $path) as $key) {
        if (is_array($node) && array_key_exists($key, $node)) {
            $node = $node[$key];
        } else {
            return $default;
        }
    }
    return $node;
}

/** Same as c() but always returns a list, so foreach is always safe. */
function clist(string $path): array
{
    $v = c($path, []);
    return is_array($v) ? $v : [];
}

function content_save(array $data): bool
{
    store_backup('content');
    return store_write('content', $data);
}

// -------------------------------------------------------------- jobs API --
function jobs_all(bool $openOnly = true): array
{
    $jobs = clist('jobs');
    if ($openOnly) {
        $jobs = array_values(array_filter(
            $jobs,
            static fn($j) => ($j['status'] ?? 'open') === 'open'
        ));
    }
    // Featured first, then by closing date.
    usort($jobs, static function ($a, $b) {
        $fa = !empty($a['featured']) ? 0 : 1;
        $fb = !empty($b['featured']) ? 0 : 1;
        return $fa <=> $fb ?: strcmp((string) ($a['closing'] ?? ''), (string) ($b['closing'] ?? ''));
    });
    return $jobs;
}

function job_by_slug(string $slug): ?array
{
    foreach (clist('jobs') as $j) {
        if (($j['slug'] ?? '') === $slug) {
            return $j;
        }
    }
    return null;
}

/** True when a job's closing date has passed. */
function job_closed(array $job): bool
{
    $closing = trim((string) ($job['closing'] ?? ''));
    if ($closing === '') {
        return false;
    }
    $ts = strtotime($closing . ' 23:59:59');
    return $ts !== false && $ts < time();
}

// -------------------------------------------------------------- blog API --
/** Published posts, newest first. Drafts never reach the public pages. */
function posts_all(bool $publishedOnly = true): array
{
    $posts = clist('insights');
    if ($publishedOnly) {
        $posts = array_values(array_filter(
            $posts,
            static fn($p) => ($p['status'] ?? 'published') === 'published'
        ));
    }
    usort($posts, static function ($a, $b) {
        $fa = !empty($a['featured']) ? 0 : 1;
        $fb = !empty($b['featured']) ? 0 : 1;
        return $fa <=> $fb ?: strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? ''));
    });
    return $posts;
}

function post_by_slug(string $slug): ?array
{
    foreach (clist('insights') as $p) {
        if (($p['slug'] ?? '') === $slug) {
            return $p;
        }
    }
    return null;
}

/** Categories that actually have a published post behind them. */
function post_categories(): array
{
    $used = [];
    foreach (posts_all(true) as $p) {
        $c = trim((string) ($p['cat'] ?? ''));
        if ($c !== '') {
            $used[$c] = ($used[$c] ?? 0) + 1;
        }
    }
    ksort($used);
    return $used;
}

/** Rough reading time, for the byline. */
function read_minutes(array $post): int
{
    $words = str_word_count(strip_tags((string) ($post['excerpt'] ?? '')));
    foreach ((array) ($post['body'] ?? []) as $b) {
        $v = $b[1] ?? '';
        $words += is_array($v)
            ? array_sum(array_map(static fn($x) => str_word_count(strip_tags((string) $x)), $v))
            : str_word_count(strip_tags((string) $v));
    }
    return max(1, (int) round($words / 220));
}
