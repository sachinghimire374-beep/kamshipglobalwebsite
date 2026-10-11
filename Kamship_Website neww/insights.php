<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/media.php';
require __DIR__ . '/inc/render.php';

$posts = posts_all(true);
$cats = post_categories();

$fCat = trim((string) ($_GET['cat'] ?? ''));
if ($fCat !== '' && !isset($cats[$fCat])) {
    $fCat = '';
}
if ($fCat !== '') {
    $posts = array_values(array_filter($posts, static fn($p) => ($p['cat'] ?? '') === $fCat));
}

$perPage = max(3, (int) c('blog.per_page', 9));
$page = max(1, (int) ($_GET['page'] ?? 1));
$total = count($posts);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$shown = array_slice($posts, ($page - 1) * $perPage, $perPage);

$title = $fCat !== ''
    ? $fCat . ' | Insights | ' . c('site.brand')
    : 'Insights | ' . c('site.brand');

ks_head(
    $title,
    'Practical writing on freight back-office operations, carrier vetting, offshore delivery and choosing the first process to outsource.',
    'insights.php'
);
ks_header('insights.php');
ks_page_head(
    [['index.php', 'Home']],
    (string) c('blog.h1', 'Notes from the desk'),
    (string) c('blog.lead'),
    71
);
?>

<section>
  <div class="wrap">
    <?php if ($cats): ?>
      <nav class="blog-filter" aria-label="Filter by category" data-reveal>
        <a href="insights.php"<?= $fCat === '' ? ' class="on"' : '' ?>>All<span><?= count(posts_all(true)) ?></span></a>
        <?php foreach ($cats as $name => $n): ?>
          <a href="insights.php?cat=<?= e(urlencode($name)) ?>"<?= $fCat === $name ? ' class="on"' : '' ?>>
            <?= e($name) ?><span><?= (int) $n ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if (!$shown): ?>
      <div class="placeholder-note mt-l" data-reveal>
        <h3>Nothing here yet</h3>
        <p><?= rich((string) c('blog.empty')) ?></p>
        <div class="actions"><a class="btn btn-ghost" href="insights.php">Show everything</a></div>
      </div>
    <?php else: ?>
      <div class="grid g-3 mt-l">
        <?php foreach ($shown as $p): ?>
          <?php $img = media_src((string) ($p['image'] ?? '')); ?>
          <article class="card svc post-card" data-reveal>
            <?php if ($img !== ''): ?>
              <a class="post-thumb" href="insight.php?p=<?= e(urlencode((string) $p['slug'])) ?>" tabindex="-1" aria-hidden="true">
                <img src="<?= e($img) ?>" alt="" loading="lazy" width="600" height="338">
              </a>
            <?php endif; ?>
            <?php if (trim((string) ($p['cat'] ?? '')) !== ''): ?>
              <p class="eyebrow"><?= e((string) $p['cat']) ?></p>
            <?php endif; ?>
            <h3><?= rich((string) ($p['title'] ?? '')) ?></h3>
            <p class="small post-meta">
              <time datetime="<?= e((string) ($p['date'] ?? '')) ?>"><?= e((string) ($p['date_display'] ?? '')) ?></time>
              &middot; <?= read_minutes($p) ?> min read
            </p>
            <p><?= rich((string) ($p['excerpt'] ?? '')) ?></p>
            <a class="arrow-link" href="insight.php?p=<?= e(urlencode((string) $p['slug'])) ?>">Read this <?= KS_ARROW ?></a>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Pages">
          <?php $q = $fCat !== '' ? 'cat=' . urlencode($fCat) . '&amp;' : ''; ?>
          <?php if ($page > 1): ?>
            <a href="insights.php?<?= $q ?>page=<?= $page - 1 ?>">Previous</a>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="insights.php?<?= $q ?>page=<?= $i ?>"<?= $i === $page ? ' class="on" aria-current="page"' : '' ?>><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $pages): ?>
            <a href="insights.php?<?= $q ?>page=<?= $page + 1 ?>">Next</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php ks_cta(); ks_footer();
