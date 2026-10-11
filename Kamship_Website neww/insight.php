<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/media.php';
require __DIR__ . '/inc/render.php';

$slug = (string) ($_GET['p'] ?? '');
$post = post_by_slug($slug);

// A draft is reachable only by someone signed in to the admin panel, so the
// author can preview it before publishing.
$isDraft = $post !== null && ($post['status'] ?? 'published') !== 'published';
if ($isDraft) {
    require __DIR__ . '/inc/auth.php';
    if (auth_user() === null) {
        $post = null;
    }
}
if ($post === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$idx = 0;
foreach (clist('insights') as $i => $p) {
    if (($p['slug'] ?? '') === $slug) { $idx = $i; break; }
}

$domain = rtrim((string) c('site.domain'), '/');
$img = media_src((string) ($post['image'] ?? ''));
$plainExcerpt = trim(strip_tags((string) ($post['excerpt'] ?? '')));
$seoTitle = trim((string) ($post['seo_title'] ?? '')) ?: strip_tags((string) $post['title']);
$seoDesc = trim((string) ($post['seo_desc'] ?? '')) ?: $plainExcerpt;

$schema = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => strip_tags((string) $post['title']),
    'datePublished' => $post['date'] ?? '',
    'author' => ['@type' => 'Organization', 'name' => (string) ($post['author'] ?? c('site.brand'))],
    'publisher' => [
        '@type' => 'Organization',
        'name' => c('site.brand'),
        'logo' => ['@type' => 'ImageObject', 'url' => $domain . '/assets/img/kamship-icon-512.png'],
    ],
    'description' => $seoDesc,
    'image' => $img !== '' ? $domain . '/' . ltrim($img, '/') : null,
    'articleSection' => (string) ($post['cat'] ?? '') ?: null,
]);

ks_head(
    $seoTitle . ' | ' . c('site.brand'),
    mb_substr($seoDesc, 0, 158),
    'insight.php?p=' . $slug,
    $schema,
    $isDraft ? '<meta name="robots" content="noindex">' : ''
);
ks_header('insights.php');

$byline = '<time datetime="' . e((string) ($post['date'] ?? '')) . '">'
        . e((string) ($post['date_display'] ?? '')) . '</time>'
        . ' &middot; ' . e((string) ($post['cat'] ?? 'Insight'))
        . ' &middot; ' . read_minutes($post) . ' min read';

ks_page_head(
    [['index.php', 'Home'], ['insights.php', 'Insights']],
    (string) $post['title'],
    $byline,
    80 + $idx
);
?>

<?php if ($isDraft): ?>
  <div class="draft-bar" role="status">
    This is a draft. Only signed-in staff can see it, and search engines are told to ignore it.
    <a href="admin/?p=insights&amp;edit=<?= (int) $idx ?>">Edit it</a>
  </div>
<?php endif; ?>

<section>
  <div class="wrap-narrow">
    <?php if ($img !== ''): ?>
      <figure class="post-hero" data-reveal="fade">
        <img src="<?= e($img) ?>" alt="<?= e((string) ($post['image_alt'] ?? '')) ?>" width="1200" height="675">
      </figure>
    <?php endif; ?>

    <article class="prose" data-reveal="fade"><?php ks_blocks((array) ($post['body'] ?? [])); ?></article>

    <p class="post-byline">
      Written by <b><?= e((string) ($post['author'] ?? c('site.brand'))) ?></b>.
      Questions about anything here? Email
      <a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a>.
    </p>

    <div class="card mt-l" data-reveal>
      <h3>Want to talk about this in your own operation?</h3>
      <p>Twenty minutes on a call, no deck. Tell us where the process is stretched and we will tell you honestly whether a desk would help.</p>
      <div class="actions"><a class="btn btn-primary" href="contact.php">Book a call</a></div>
    </div>
  </div>
</section>

<?php
$others = array_values(array_filter(posts_all(true), static fn($o) => ($o['slug'] ?? '') !== $slug));
// Prefer the same category.
usort($others, static function ($a, $b) use ($post) {
    $sa = ($a['cat'] ?? '') === ($post['cat'] ?? '') ? 0 : 1;
    $sb = ($b['cat'] ?? '') === ($post['cat'] ?? '') ? 0 : 1;
    return $sa <=> $sb;
});
if ($others): ?>
<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal><p class="eyebrow">Keep reading</p><h2>More from the desk</h2></div>
    <div class="grid g-3 mt-l">
      <?php foreach (array_slice($others, 0, 3) as $o): ?>
        <?php $oi = media_src((string) ($o['image'] ?? '')); ?>
        <article class="card svc post-card" data-reveal>
          <?php if ($oi !== ''): ?>
            <a class="post-thumb" href="insight.php?p=<?= e(urlencode((string) $o['slug'])) ?>" tabindex="-1" aria-hidden="true">
              <img src="<?= e($oi) ?>" alt="" loading="lazy" width="600" height="338">
            </a>
          <?php endif; ?>
          <p class="eyebrow"><?= e((string) ($o['cat'] ?? '')) ?></p>
          <h3><?= rich((string) ($o['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($o['excerpt'] ?? '')) ?></p>
          <a class="arrow-link" href="insight.php?p=<?= e(urlencode((string) $o['slug'])) ?>">Read this <?= KS_ARROW ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif;
ks_footer();
