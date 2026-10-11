<?php
/** Sitemap generated from live content, so new jobs and articles appear at once. */
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$domain = rtrim((string) c('site.domain'), '/');

$urls = [
    ['', '1.0', 'weekly'], ['services.php', '0.9', 'monthly'], ['pricing.php', '0.9', 'monthly'],
    ['contact.php', '0.9', 'monthly'], ['jobs.php', '0.9', 'weekly'], ['careers.php', '0.8', 'monthly'],
    ['industries.php', '0.7', 'monthly'], ['about.php', '0.7', 'monthly'], ['why-nepal.php', '0.7', 'monthly'],
    ['security.php', '0.7', 'monthly'], ['pilot.php', '0.7', 'monthly'], ['insights.php', '0.7', 'monthly'],
    ['privacy.php', '0.3', 'yearly'], ['terms.php', '0.3', 'yearly'],
];
foreach (clist('services') as $s) {
    $urls[] = ['service.php?s=' . rawurlencode((string) $s['slug']), '0.8', 'monthly'];
}
foreach (posts_all(true) as $p) {
    $urls[] = ['insight.php?p=' . rawurlencode((string) $p['slug']), '0.6', 'yearly'];
}
foreach (jobs_all(true) as $j) {
    $urls[] = ['job.php?j=' . rawurlencode((string) $j['slug']), '0.8', 'weekly'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$path, $prio, $freq]) {
    echo '  <url><loc>' . htmlspecialchars($domain . '/' . $path, ENT_XML1) . '</loc>'
        . '<lastmod>' . date('Y-m-d') . '</lastmod>'
        . '<changefreq>' . $freq . '</changefreq>'
        . '<priority>' . $prio . "</priority></url>\n";
}
echo '</urlset>' . "\n";
