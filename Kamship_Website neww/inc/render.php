<?php
/**
 * Shared markup for the public pages: <head>, header, footer, CTA band and the
 * small building blocks. Everything reads from the content store, so editing
 * in the admin panel changes every page at once.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

// --------------------------------------------------------------- brand ----
/**
 * A picture from the brand settings, falling back to the file that ships with
 * the site so a half-finished change in the admin panel never leaves a page
 * with a broken image.
 */
function brand_img(string $key, string $fallback = ''): string
{
    $v = media_src((string) c('brand.' . $key));
    if ($v === '') {
        return $fallback;
    }
    // A file that has been deleted from disk is worse than the shipped default.
    $path = ROOT . '/' . ltrim($v, '/');
    return is_file($path) ? $v : $fallback;
}

/**
 * The logo <img>, with the retina versions attached when they exist. The
 * reversed variant is the one that goes on the dark footer; if nobody has
 * uploaded one, the ordinary logo is used rather than nothing at all.
 */
function brand_logo(string $cls = '', bool $reversed = false): string
{
    $base = $reversed
        ? brand_img('logo_reversed', brand_img('logo', 'assets/img/kamship-logo.png'))
        : brand_img('logo', 'assets/img/kamship-logo.png');

    $srcset = [$base . ' 1x'];
    foreach (['2x', '3x'] as $d) {
        $key = ($reversed ? 'logo_reversed_' : 'logo_') . $d;
        $v = brand_img($key);
        if ($v !== '') {
            $srcset[] = $v . ' ' . $d;
        }
    }

    // Explicit dimensions stop the header jumping while the logo loads.
    $w = max(40, (int) c('brand.logo_w', 199));
    $h = max(12, (int) c('brand.logo_h', 44));

    return '<img' . ($cls !== '' ? ' class="' . e($cls) . '"' : '')
        . ' src="' . e($base) . '"'
        . (count($srcset) > 1 ? ' srcset="' . e(implode(', ', $srcset)) . '"' : '')
        . ' alt="' . e((string) c('site.brand')) . '"'
        . ' width="' . $w . '" height="' . $h . '">';
}

/** A colour from the brand settings, ignored unless it is a real hex value. */
function brand_col(string $key, string $fallback): string
{
    $v = strtoupper(trim((string) c('brand.' . $key)));
    return preg_match('/^#(?:[0-9A-F]{3}|[0-9A-F]{6})$/', $v) === 1 ? $v : $fallback;
}

/**
 * The palette, pushed into the stylesheet's custom properties. The CSS file
 * carries the same values as its defaults, so this only matters once someone
 * changes a colour in the admin panel — and then it changes everywhere at once
 * rather than needing the stylesheet edited.
 */
function ks_brand_style(): string
{
    $map = [
        '--navy'        => ['navy', '#172D45'],
        '--red'         => ['accent', '#C20F15'],
        '--red-deep'    => ['accent_deep', '#A80D12'],
        '--red-light'   => ['accent_light', '#F07B80'],
        '--cyan'        => ['cyan', '#2A93B4'],
        '--cyan-ink'    => ['cyan_ink', '#1F6B85'],
        '--cyan-deep'   => ['cyan_deep', '#185466'],
        '--cyan-light'  => ['cyan_light', '#5FB8D4'],
    ];
    $out = [];
    foreach ($map as $prop => [$key, $default]) {
        $val = brand_col($key, $default);
        if ($val !== $default) {
            $out[] = $prop . ':' . $val;
        }
    }
    // The logo mark's own colours follow the accent, so the two never drift.
    $logoRed = brand_col('accent', '#C20F15');
    if ($logoRed !== '#C20F15') {
        $out[] = '--logo-red:' . $logoRed;
    }
    return $out === [] ? '' : '<style>:root{' . implode(';', $out) . '}</style>' . "\n";
}

// --------------------------------------------------------------- icons ----
const KS_ICONS = [
    'shield'  => '<path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
    'radar'   => '<path d="M12 3a9 9 0 109 9"/><path d="M12 8a4 4 0 104 4"/><path d="M12 12l8-8"/>',
    'receipt' => '<path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z"/><path d="M9 8h6"/><path d="M9 12h6"/>',
    'box'     => '<path d="M3 8l9-5 9 5v8l-9 5-9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>',
    'file'    => '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h4"/>',
    'stamp'   => '<path d="M5 21h14"/><path d="M8 17h8v-2a4 4 0 00-1.2-2.8A3 3 0 0114 10V7a2 2 0 10-4 0v3a3 3 0 01-.8 2.2A4 4 0 008 15z"/>',
    'calc'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8"/><path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
    'headset' => '<path d="M4 14v-2a8 8 0 0116 0v2"/><path d="M4 14a2 2 0 012-2h1v6H6a2 2 0 01-2-2z"/><path d="M20 14a2 2 0 00-2-2h-1v6h1a2 2 0 002-2z"/><path d="M17 18v1a3 3 0 01-3 3h-2"/>',
    'users'   => '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.9"/><path d="M16 3.1a4 4 0 010 7.8"/>',
    'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'message' => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
    'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 010 18a15 15 0 010-18z"/>',
    'chart'   => '<path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/>',
    'lock'    => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 118 0v3"/>',
    'truck'   => '<path d="M3 16V6a1 1 0 011-1h10v11"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7.5" cy="17.5" r="2"/><circle cx="17.5" cy="17.5" r="2"/>',
    'ship'    => '<path d="M3 17l1.5-5h15L21 17"/><path d="M12 12V5"/><path d="M8 8h8"/><path d="M3 20c1.5 0 1.5-1 3-1s1.5 1 3 1 1.5-1 3-1 1.5 1 3 1 1.5-1 3-1"/>',
    'plane'   => '<path d="M17.8 19.2L16 11l3.5-3.5a2.1 2.1 0 00-3-3L13 8 4.8 6.2a1 1 0 00-.9 1.7l6.3 4-2 4.3-2.6-.6a1 1 0 00-1 1.6l3 3 3 3a1 1 0 001.6-1l-.6-2.6 4.3-2 4 6.3a1 1 0 001.7-.9z"/>',
    'search'  => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
    'check'   => '<path d="M20 6L9 17l-5-5"/>',
    'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'phone'   => '<path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.2a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z"/>',
    'pin'     => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1116 0z"/><circle cx="12" cy="10" r="3"/>',
    'spark'   => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18 16l.9 2.1L21 19l-2.1.9L18 22l-.9-2.1L15 19l2.1-.9z"/>',
    'layers'  => '<path d="M12 2l9 5-9 5-9-5z"/><path d="M3 12l9 5 9-5"/><path d="M3 17l9 5 9-5"/>',
    'cycle'   => '<path d="M3 12a9 9 0 0115-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
    'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/><path d="M3 12h18"/>',
    'upload'  => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 9l5-5 5 5"/><path d="M12 4v12"/>',
];

function icon(string $name, string $cls = '', int $size = 24): string
{
    $body = KS_ICONS[$name] ?? KS_ICONS['check'];
    return '<svg class="' . e($cls) . '" viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '"'
        . ' fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"'
        . ' stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

const KS_ARROW = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"'
    . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
    . '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>';

const KS_CHEV = '<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
    . '<path d="M6 9l6 6 6-6"/></svg>';

// ------------------------------------------------------ hero background ---
/** Animated route network. Deterministic from $seed so pages stay stable. */
function hero_bg(int $seed = 0): string
{
    mt_srand($seed);
    $out = '';
    for ($i = 0; $i < 9; $i++) {
        $y1 = mt_rand(600, 9400) / 100;
        $y2 = mt_rand(600, 9400) / 100;
        $cy = min($y1, $y2) - mt_rand(1000, 2800) / 100;
        $d = sprintf('M-4,%.1f Q50,%.1f 104,%.1f', $y1, $cy, $y2);
        $dur = mt_rand(70, 140) / 10;
        $out .= '<path d="' . $d . '" stroke="#C3CCD8" stroke-opacity="0.18" stroke-width="0.22"'
            . ' fill="none" vector-effect="non-scaling-stroke"/>'
            . '<path d="' . $d . '" stroke="#5FB8D4" stroke-opacity="0.55" stroke-width="0.3"'
            . ' fill="none" stroke-dasharray="3 60" vector-effect="non-scaling-stroke">'
            . '<animate attributeName="stroke-dashoffset" from="63" to="0" dur="' . $dur . 's"'
            . ' repeatCount="indefinite"/></path>';
    }
    for ($i = 0; $i < 14; $i++) {
        $x = mt_rand(300, 9700) / 100;
        $y = mt_rand(400, 9600) / 100;
        // Every fourth node picks up the logo's red; the rest echo its grey ring.
        $accent = $i % 4 === 0;
        $r = $accent ? 0.55 : 0.36;
        $col = $accent ? '#F07B80' : '#C3CCD8';
        $op = $accent ? 0.85 : 0.32;
        $dur = mt_rand(35, 70) / 10;
        $out .= sprintf(
            '<circle cx="%.1f" cy="%.1f" r="%.2f" fill="%s" fill-opacity="%.2f">'
            . '<animate attributeName="fill-opacity" values="%.2f;%.2f;%.2f" dur="%.1fs"'
            . ' repeatCount="indefinite"/></circle>',
            $x, $y, $r, $col, $op, $op, $op * 0.35, $op, $dur
        );
    }
    return '<div class="hero-bg" aria-hidden="true">'
        . '<svg viewBox="0 0 100 100" preserveAspectRatio="none" role="presentation">'
        . $out . '</svg></div>';
}

// ------------------------------------------------------------- chrome -----
function ks_nav(): array
{
    $svc = [];
    foreach (clist('services') as $s) {
        $svc[] = ['service.php?s=' . urlencode((string) ($s['slug'] ?? '')), (string) ($s['nav'] ?? ''), (string) ($s['navdesc'] ?? '')];
    }
    return [
        ['index.php', 'Home', null],
        ['services.php', 'Services', $svc],
        ['industries.php', 'Industries', null],
        ['about.php', 'Company', [
            ['about.php', 'About Kamship', 'Who we are and how we are set up'],
            ['why-nepal.php', 'Why Nepal', 'Time zone, talent and infrastructure'],
            ['security.php', 'Security &amp; data', 'How we protect client systems and data'],
            ['pilot.php', 'How the pilot works', 'Two weeks, one process, agreed targets'],
        ]],
        ['pricing.php', 'Pricing', null],
        ['careers.php', 'Careers', [
            ['careers.php', 'Working at Kamship', 'Pay, training, shifts and transport'],
            ['jobs.php', 'Open roles', 'Current vacancies in Pokhara'],
        ]],
        ['insights.php', 'Insights', null],
    ];
}

function ks_head(string $title, string $desc, string $path, ?array $schema = null, string $extra = ''): void
{
    $domain = rtrim((string) c('site.domain'), '/');
    $url = $domain . '/' . ($path === 'index.php' ? '' : $path);
    ks_security_headers();
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($url) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e((string) c('site.brand')) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($url) ?>">
<meta property="og:image" content="<?= e($domain . '/' . brand_img('og', 'assets/img/og-image-1200x630.png')) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="theme-color" content="<?= e(brand_col('navy', '#172D45')) ?>">
<link rel="icon" href="assets/img/favicon.ico" sizes="any">
<link rel="icon" href="assets/img/kamship-icon-32.png" sizes="32x32" type="image/png">
<link rel="icon" href="<?= e(brand_img('icon', 'assets/img/kamship-icon-192.png')) ?>" sizes="192x192" type="image/png">
<link rel="apple-touch-icon" href="assets/img/kamship-icon-tile-192.png">
<link rel="manifest" href="site.webmanifest">
<link rel="preload" href="assets/fonts/PlusJakartaSans-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/css/style.css">
<?= ks_brand_style() ?><?= $extra ?>
<?php if ($schema !== null): ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="progress" aria-hidden="true"></div>
<?php
}

function ks_header(string $path): void
{
    $email = (string) c('site.email');
    $phoneD = (string) c('site.phone_display');
    $phoneL = (string) c('site.phone_link');
    ?>
<div class="topbar">
  <div class="wrap">
    <span class="topbar-live"><span class="pulse-dot"></span> <?= e((string) c('site.topbar_live')) ?> &middot; Pokhara <time data-tz="Asia/Kathmandu">--:--</time></span>
    <span class="topbar-contact">
      <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
      <a href="tel:<?= e($phoneL) ?>"><?= e($phoneD) ?></a>
    </span>
  </div>
</div>
<header class="site-header">
  <div class="wrap navbar">
    <a class="brand" href="index.php" aria-label="<?= e((string) c('site.brand')) ?> home">
      <?= brand_logo() ?>
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="burger" aria-hidden="true"><span></span><span></span><span></span></span> Menu
    </button>
    <nav id="site-nav" class="nav" aria-label="Main">
      <ul class="nav-list">
        <?php foreach (ks_nav() as [$href, $label, $kids]): ?>
          <?php if ($kids): ?>
            <li class="nav-item">
              <button class="nav-toggle" type="button" aria-expanded="false"><?= $label ?><?= KS_CHEV ?></button>
              <div class="dropdown">
                <?php foreach ($kids as [$k, $t, $d]): ?>
                  <a href="<?= e($k) ?>"><strong><?= $t ?></strong><span><?= e($d) ?></span></a>
                <?php endforeach; ?>
              </div>
            </li>
          <?php else: ?>
            <li><a href="<?= e($href) ?>"<?= $href === $path ? ' aria-current="page"' : '' ?>><?= $label ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-primary btn-sm" href="contact.php">Book a call</a>
    </nav>
  </div>
</header>
<div class="nav-scrim" aria-hidden="true"></div>
<main id="main">
<?php
}

function ks_page_head(array $crumbs, string $h1, string $lead, int $seed = 7): void
{
    ?>
<section class="page-head on-dark">
  <?= hero_bg($seed) ?>
  <div class="wrap">
    <p class="crumb"><?php foreach ($crumbs as [$h, $t]): ?><a href="<?= e($h) ?>"><?= e($t) ?></a><span>/</span><?php endforeach; ?></p>
    <h1 data-reveal="fade"><?= rich($h1) ?></h1>
    <p class="lead" data-reveal="fade"><?= rich($lead) ?></p>
  </div>
</section>
<?php
}

function ks_marquee(?string $label = null): void
{
    $label ??= (string) c('home.marquee_label');
    ?>
<section class="on-paper" style="padding-block:46px">
  <div class="wrap" style="text-align:center;margin-bottom:24px">
    <p class="small" style="margin:0 auto"><?= e($label) ?></p>
  </div>
  <div class="marquee"><div class="marquee-track">
    <?php foreach (clist('systems') as $s): ?><span class="marquee-item"><?= e((string) $s) ?></span><?php endforeach; ?>
  </div></div>
  <div class="wrap" style="text-align:center;margin-top:24px">
    <p class="fine" style="margin:0 auto;max-width:62ch"><?= rich((string) c('home.marquee_note')) ?></p>
  </div>
</section>
<?php
}

function ks_faq(array $items, bool $single = true): void
{
    echo '<div class="acc"' . ($single ? ' data-acc-single' : '') . '>';
    foreach ($items as $qa) {
        $q = (string) ($qa[0] ?? '');
        $a = (string) ($qa[1] ?? '');
        echo '<details><summary>' . rich($q) . '<span class="plus" aria-hidden="true"></span></summary>'
            . '<div class="acc-body"><p>' . rich($a) . '</p></div></details>';
    }
    echo '</div>';
}

/** Render an editable prose block array: [["h2","..."],["p","..."],["ul",[...]]] */
function ks_blocks(array $blocks): void
{
    foreach ($blocks as $b) {
        $type = (string) ($b[0] ?? 'p');
        $val = $b[1] ?? '';
        switch ($type) {
            case 'h2':
                echo '<h2>' . rich((string) $val) . '</h2>';
                break;
            case 'h3':
                echo '<h3>' . rich((string) $val) . '</h3>';
                break;
            case 'eyebrow':
                echo '<p class="eyebrow">' . e((string) $val) . '</p>';
                break;
            case 'quote':
                echo '<blockquote>' . rich((string) $val) . '</blockquote>';
                break;
            case 'ul':
                echo '<ul>';
                foreach ((array) $val as $li) {
                    echo '<li>' . rich((string) $li) . '</li>';
                }
                echo '</ul>';
                break;
            case 'ol':
                echo '<ol>';
                foreach ((array) $val as $li) {
                    echo '<li>' . rich((string) $li) . '</li>';
                }
                echo '</ol>';
                break;
            case 'note':
                echo '<p class="small">' . rich((string) $val) . '</p>';
                break;
            default:
                echo '<p>' . rich((string) $val) . '</p>';
        }
    }
}

function ks_cta(?string $title = null, ?string $text = null): void
{
    $title ??= (string) c('cta.title');
    $text ??= (string) c('cta.text');
    ?>
<section class="cta-band on-dark">
  <?= hero_bg(99) ?>
  <div class="wrap">
    <div class="split center">
      <div data-reveal="left">
        <p class="eyebrow"><?= e((string) c('cta.eyebrow')) ?></p>
        <h2><?= rich($title) ?></h2>
        <p class="lead"><?= rich($text) ?></p>
        <div class="actions">
          <a class="btn btn-accent" href="<?= url((string) c('cta.btn1_link')) ?>"><?= e((string) c('cta.btn1')) ?></a>
          <a class="btn btn-ghost" href="<?= url((string) c('cta.btn2_link')) ?>"><?= e((string) c('cta.btn2')) ?></a>
        </div>
      </div>
      <div data-reveal="right">
        <div class="contact-tile">
          <span class="card-icon"><?= icon('mail', '', 22) ?></span>
          <span><b><?= e((string) c('site.email_label', 'General enquiries')) ?></b><a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a></span>
        </div>
        <?php if (trim((string) c('site.email_ceo')) !== ''): ?>
        <div class="contact-tile">
          <span class="card-icon"><?= icon('spark', '', 22) ?></span>
          <span><b><?= e((string) c('site.email_ceo_label', 'Operations and partnerships')) ?></b><a href="mailto:<?= e((string) c('site.email_ceo')) ?>"><?= e((string) c('site.email_ceo')) ?></a></span>
        </div>
        <?php endif; ?>
        <div class="contact-tile">
          <span class="card-icon"><?= icon('phone', '', 22) ?></span>
          <span><b>Call or WhatsApp</b><a href="tel:<?= e((string) c('site.phone_link')) ?>"><?= e((string) c('site.phone_display')) ?></a></span>
        </div>
        <div class="contact-tile">
          <span class="card-icon"><?= icon('clock', '', 22) ?></span>
          <span><b>We reply</b><span class="small"><?= e((string) c('cta.reply_note')) ?></span></span>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
}

function ks_footer(): void
{
    $email = (string) c('site.email');
    ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <?= brand_logo('foot-logo', true) ?>
        <p><?= rich((string) c('site.footer_blurb')) ?></p>
        <div class="social-row">
          <a href="<?= url((string) c('site.linkedin')) ?>" rel="noopener" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 8.98h4v12H3v-12zM9 8.98h3.8v1.64h.05c.53-1 1.83-2.05 3.76-2.05C20.4 8.57 21 11.1 21 14.2v6.78h-4v-6c0-1.43-.03-3.27-2-3.27-2 0-2.3 1.56-2.3 3.17v6.1H9v-12z"/></svg>
          </a>
          <a href="<?= url((string) c('site.facebook')) ?>" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg>
          </a>
          <a href="mailto:<?= e($email) ?>" aria-label="Email">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          </a>
        </div>
      </div>
      <div>
        <h4>Services</h4>
        <ul>
          <?php foreach (array_slice(clist('services'), 0, 6) as $s): ?>
            <li><a href="service.php?s=<?= e(urlencode((string) $s['slug'])) ?>"><?= $s['nav'] ?></a></li>
          <?php endforeach; ?>
          <li><a href="services.php">All services</a></li>
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="about.php">About us</a></li>
          <li><a href="why-nepal.php">Why Nepal</a></li>
          <li><a href="security.php">Security &amp; data</a></li>
          <li><a href="pilot.php">How the pilot works</a></li>
          <li><a href="pricing.php">Pricing</a></li>
          <li><a href="jobs.php">Open roles</a></li>
          <li><a href="insights.php">Insights</a></li>
        </ul>
      </div>
      <div>
        <h4>Get in touch</h4>
        <ul>
          <li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
          <?php if (trim((string) c('site.email_ceo')) !== ''): ?>
            <li><a href="mailto:<?= e((string) c('site.email_ceo')) ?>"><?= e((string) c('site.email_ceo')) ?></a></li>
          <?php endif; ?>
          <li><a href="tel:<?= e((string) c('site.phone_link')) ?>"><?= e((string) c('site.phone_display')) ?></a></li>
          <li><?= e((string) c('site.address')) ?></li>
          <li><a href="contact.php">Book a 20-minute call</a></li>
        </ul>
        <p class="fine" style="margin-top:14px"><?= rich((string) c('site.footer_note')) ?></p>
      </div>
    </div>
    <div class="foot-base">
      <span>&copy; <span id="year"><?= date('Y') ?></span> <?= e((string) c('site.company')) ?>. Registered in Nepal. All rights reserved.</span>
      <span><a href="privacy.php">Privacy</a> &nbsp;&middot;&nbsp; <a href="terms.php">Terms</a> &nbsp;&middot;&nbsp; <a href="sitemap.xml">Sitemap</a></span>
    </div>
  </div>
</footer>
<button class="to-top" type="button" aria-label="Back to top">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<script src="assets/js/main.js" defer></script>
</body>
</html>
<?php
}
