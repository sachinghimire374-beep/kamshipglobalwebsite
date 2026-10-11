<?php
/**
 * Admin panel chrome and form helpers.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

const ADMIN_SECTIONS = [
    ['dashboard',    'Dashboard',    'Overview and recent activity'],
    ['enquiries',    'Enquiries',    'Messages from the contact form'],
    ['applications', 'Applications', 'Job applications and CVs'],
    ['jobs',         'Jobs',         'Open roles on the careers pages'],
    ['services',     'Services',     'The eight service desks'],
    ['pricing',      'Pricing',      'Rate tiers and what is included'],
    ['home',         'Home page',    'Hero, statistics and section text'],
    ['pages',        'Other pages',  'About, Why Nepal, Security, Pilot, Careers'],
    ['insights',     'Insights',     'Articles and the blog'],
    ['media',        'Pictures',     'Images used across the site'],
    ['industries',   'Industries',   'Sectors served'],
    ['faqs',         'FAQs',         'Question and answer blocks'],
    ['settings',     'Settings',     'Contact details and social links'],
    ['branding',     'Logo & colours', 'The logo files and the palette'],
    ['account',      'Your account', 'Change your password'],
];

/** A byte count a person can read at a glance. */
function size_h(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024) . ' KB';
    }
    return round($bytes / (1024 * 1024), 1) . ' MB';
}

function flash(string $msg = null, string $kind = 'ok'): ?array
{
    ks_session_start();
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'kind' => $kind];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($f) ? $f : null;
}

function admin_head(string $title): void
{
    ks_security_headers();
    header('X-Robots-Tag: noindex, nofollow');
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> &middot; Kamship admin</title>
<link rel="icon" href="../assets/img/favicon.ico" sizes="any">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<?php
}

function admin_shell_open(string $current, string $title, string $intro = ''): void
{
    $user = auth_user();
    admin_head($title);
    ?>
<a class="skip" href="#main">Skip to content</a>
<header class="abar">
  <a class="abrand" href="?p=dashboard">
    <img src="../assets/img/kamship-icon-tile-192.png" alt="" width="30" height="30" style="border-radius:6px">
    <span>Kamship <b>admin</b></span>
  </a>
  <button class="anav-btn" type="button" aria-expanded="false" aria-controls="anav">Menu</button>
  <div class="abar-right">
    <a class="alink" href="../index.php" target="_blank" rel="noopener">View site &#8599;</a>
    <span class="auser"><?= e((string) ($user['name'] ?? '')) ?></span>
    <a class="btn btn-ghost btn-sm" href="?p=logout">Sign out</a>
  </div>
</header>

<div class="ashell">
  <nav id="anav" class="aside" aria-label="Admin sections">
    <ul>
      <?php foreach (ADMIN_SECTIONS as [$slug, $label, $desc]): ?>
        <li>
          <a href="?p=<?= e($slug) ?>"<?= $slug === $current ? ' aria-current="page"' : '' ?>>
            <b><?= e($label) ?></b><span><?= e($desc) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <main id="main" class="amain">
    <div class="ahead">
      <h1><?= e($title) ?></h1>
      <?php if ($intro !== ''): ?><p class="aintro"><?= e($intro) ?></p><?php endif; ?>
    </div>
    <?php $f = flash(); if ($f): ?>
      <div class="anote anote-<?= e($f['kind']) ?>" role="status"><?= e($f['msg']) ?></div>
    <?php endif; ?>
<?php
}

function admin_shell_close(): void
{
    ?>
  </main>
</div>
<script src="assets/admin.js" defer></script>
</body>
</html>
<?php
}

// -------------------------------------------------------- field helpers ---
/**
 * A labelled input bound to a content path.
 * $type: text | textarea | url | email | number | checkbox | select
 */
function fld(string $name, string $label, string $value, string $type = 'text', array $opts = []): void
{
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $name);
    $hint = (string) ($opts['hint'] ?? '');
    $rows = (int) ($opts['rows'] ?? 4);
    $wide = !empty($opts['wide']) ? ' afield-wide' : '';
    echo '<div class="afield' . $wide . '">';
    echo '<label for="' . e($id) . '">' . e($label);
    if ($hint !== '') {
        echo ' <span class="ahint">' . e($hint) . '</span>';
    }
    echo '</label>';

    if ($type === 'textarea') {
        echo '<textarea id="' . e($id) . '" name="' . e($name) . '" rows="' . $rows . '">' . e($value) . '</textarea>';
    } elseif ($type === 'checkbox') {
        echo '<label class="acheck"><input type="checkbox" id="' . e($id) . '" name="' . e($name) . '" value="1"'
            . ($value !== '' && $value !== '0' ? ' checked' : '') . '> <span>'
            . e((string) ($opts['checkLabel'] ?? 'Yes')) . '</span></label>';
    } elseif ($type === 'select') {
        echo '<select id="' . e($id) . '" name="' . e($name) . '">';
        foreach ((array) ($opts['options'] ?? []) as $k => $v) {
            $k = (string) $k;
            echo '<option value="' . e($k) . '"' . ($value === $k ? ' selected' : '') . '>' . e((string) $v) . '</option>';
        }
        echo '</select>';
    } else {
        echo '<input type="' . e($type) . '" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"'
            . (isset($opts['max']) ? ' maxlength="' . (int) $opts['max'] . '"' : '')
            . (!empty($opts['placeholder']) ? ' placeholder="' . e((string) $opts['placeholder']) . '"' : '')
            . '>';
    }
    echo '</div>';
}

/** A textarea whose lines become an array. */
function fld_lines(string $name, string $label, array $lines, string $hint = 'One per line'): void
{
    fld($name, $label, implode("\n", array_map('strval', $lines)), 'textarea', [
        'rows' => max(4, count($lines) + 2), 'hint' => $hint, 'wide' => true,
    ]);
}

/** A textarea of "left | right" rows that becomes a list of pairs. */
function fld_pairs(string $name, string $label, array $pairs, string $hint = 'One row per line, with a | between the two columns'): void
{
    $txt = [];
    foreach ($pairs as $p) {
        $txt[] = (string) ($p[0] ?? '') . ' | ' . (string) ($p[1] ?? '');
    }
    fld($name, $label, implode("\n", $txt), 'textarea', [
        'rows' => max(4, count($txt) + 2), 'hint' => $hint, 'wide' => true,
    ]);
}

function post_lines(string $name): array
{
    $raw = (string) ($_POST[$name] ?? '');
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return $out;
}

function post_pairs(string $name): array
{
    $out = [];
    foreach (post_lines($name) as $line) {
        $bits = explode('|', $line, 2);
        $out[] = [trim($bits[0]), trim($bits[1] ?? '')];
    }
    return $out;
}

/**
 * Rows of "a | b | c" edited as text, where each row becomes a map keyed by
 * $keys. Fewer columns than keys is fine — the missing ones come back empty —
 * so an editor who leaves the icon off a row does not break the page.
 */
function fld_tuples(string $name, string $label, array $rows, array $keys, string $hint): void
{
    $txt = [];
    foreach ($rows as $r) {
        $cells = [];
        foreach ($keys as $k) {
            $cells[] = (string) ($r[$k] ?? '');
        }
        // Trailing empties only add noise for the editor.
        while ($cells !== [] && end($cells) === '') {
            array_pop($cells);
        }
        $txt[] = implode(' | ', $cells);
    }
    fld($name, $label, implode("\n", $txt), 'textarea', [
        'rows' => max(4, count($txt) + 2), 'hint' => $hint, 'wide' => true,
    ]);
}

function post_tuples(string $name, array $keys): array
{
    $out = [];
    foreach (post_lines($name) as $line) {
        $cells = array_map('trim', explode('|', $line, count($keys)));
        $row = [];
        foreach ($keys as $i => $k) {
            $row[$k] = $cells[$i] ?? '';
        }
        // A row with nothing in its first column is not a row.
        if (($row[$keys[0]] ?? '') !== '') {
            $out[] = $row;
        }
    }
    return $out;
}

/**
 * A list of titled groups, each with its own bullets, edited as one textarea:
 *
 *     ## Physical | pin
 *     - controlled entry
 *     - clean-desk policy
 *
 * Same "## " and "- " conventions as the prose editor, so there is only one
 * set of rules to learn.
 */
function groups_to_text(array $groups): string
{
    $out = [];
    foreach ($groups as $g) {
        $head = '## ' . (string) ($g['title'] ?? '');
        $icon = trim((string) ($g['icon'] ?? ''));
        if ($icon !== '') {
            $head .= ' | ' . $icon;
        }
        $out[] = $head;
        foreach ((array) ($g['points'] ?? []) as $p) {
            $out[] = '- ' . (string) $p;
        }
        $out[] = '';
    }
    return trim(implode("\n", $out));
}

function text_to_groups(string $text): array
{
    $groups = [];
    $cur = null;
    foreach (preg_split('/\r\n|\r|\n/', post_str_raw($text)) ?: [] as $line) {
        $t = trim($line);
        if ($t === '') {
            continue;
        }
        if (preg_match('/^##\s*(.*)$/', $t, $m)) {
            if ($cur !== null) {
                $groups[] = $cur;
            }
            $bits = array_map('trim', explode('|', $m[1], 2));
            $cur = ['title' => $bits[0], 'icon' => $bits[1] ?? '', 'points' => []];
        } elseif (preg_match('/^-\s*(.*)$/', $t, $m)) {
            // A bullet before any heading would be lost, so start a group for it.
            $cur ??= ['title' => '', 'icon' => '', 'points' => []];
            $cur['points'][] = trim($m[1]);
        } else {
            $cur ??= ['title' => '', 'icon' => '', 'points' => []];
            $cur['points'][] = $t;
        }
    }
    if ($cur !== null) {
        $groups[] = $cur;
    }
    return array_values(array_filter(
        $groups,
        static fn($g) => $g['title'] !== '' || $g['points'] !== []
    ));
}

/** Strip control characters without trimming structure. */
function post_str_raw(string $v): string
{
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
}

function post_str(string $name, int $max = 8000): string
{
    $v = $_POST[$name] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

function post_bool(string $name): bool
{
    return !empty($_POST[$name]);
}

/** Prose blocks edited as text: "## heading", "- bullet", blank-line paragraphs. */
function blocks_to_text(array $blocks): string
{
    $out = [];
    foreach ($blocks as $b) {
        $t = (string) ($b[0] ?? 'p');
        $v = $b[1] ?? '';
        if ($t === 'ul' || $t === 'ol') {
            foreach ((array) $v as $li) {
                $out[] = ($t === 'ol' ? '1. ' : '- ') . $li;
            }
            $out[] = '';
        } elseif ($t === 'h2') {
            $out[] = '## ' . $v;
            $out[] = '';
        } elseif ($t === 'h3') {
            $out[] = '### ' . $v;
            $out[] = '';
        } elseif ($t === 'eyebrow') {
            $out[] = '> ' . $v;
            $out[] = '';
        } elseif ($t === 'quote') {
            $out[] = '"" ' . $v;
            $out[] = '';
        } else {
            $out[] = (string) $v;
            $out[] = '';
        }
    }
    return trim(implode("\n", $out));
}

function text_to_blocks(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $blocks = [];
    $list = null;
    $listType = 'ul';

    $flush = static function () use (&$list, &$blocks, &$listType): void {
        if ($list !== null && $list !== []) {
            $blocks[] = [$listType, $list];
        }
        $list = null;
    };

    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            $flush();
            continue;
        }
        if (preg_match('/^###\s+(.*)$/', $t, $m)) {
            $flush();
            $blocks[] = ['h3', trim($m[1])];
        } elseif (preg_match('/^##\s+(.*)$/', $t, $m)) {
            $flush();
            $blocks[] = ['h2', trim($m[1])];
        } elseif (preg_match('/^>\s+(.*)$/', $t, $m)) {
            $flush();
            $blocks[] = ['eyebrow', trim($m[1])];
        } elseif (preg_match('/^""\s+(.*)$/', $t, $m)) {
            $flush();
            $blocks[] = ['quote', trim($m[1])];
        } elseif (preg_match('/^-\s+(.*)$/', $t, $m)) {
            if ($list === null || $listType !== 'ul') {
                $flush();
                $listType = 'ul';
                $list = [];
            }
            $list[] = trim($m[1]);
        } elseif (preg_match('/^\d+[.)]\s+(.*)$/', $t, $m)) {
            if ($list === null || $listType !== 'ol') {
                $flush();
                $listType = 'ol';
                $list = [];
            }
            $list[] = trim($m[1]);
        } else {
            $flush();
            $blocks[] = ['p', $t];
        }
    }
    $flush();
    return $blocks;
}

const BLOCK_HELP = 'Blank line between paragraphs. "## " starts a heading, "- " a bullet, "1. " a numbered item, "> " a small coloured label.';
