<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('About ' . c('site.brand'),
    'The international freight operations division of Kamship Pvt Ltd, with a supervised delivery centre in Pokhara, Nepal.',
    'about.php');
ks_header('about.php');
ks_page_head([['index.php', 'Home']], (string) c('pages.about.h1'), (string) c('pages.about.lead'), 41);
?>
<section>
  <div class="wrap split">
    <div class="prose" data-reveal="left"><?php ks_blocks(clist('pages.about.body')); ?></div>
    <div data-reveal="right">
      <div class="card">
        <h3><?= rich((string) c('pages.about.card_title')) ?></h3>
        <ul class="checks">
          <?php foreach (clist('pages.about.card_points') as $p): ?><li><?= rich((string) $p) ?></li><?php endforeach; ?>
        </ul>
        <div class="actions"><a class="btn btn-primary btn-sm" href="contact.php">Talk to us</a></div>
      </div>
    </div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">How we work</p>
      <h2><?= rich((string) c('pages.about.commitments_h2')) ?></h2>
    </div>
    <div class="grid g-4 mt-l">
      <?php foreach (clist('pages.about.commitments') as $cm): ?>
        <article class="card pillar" data-reveal>
          <span class="card-icon"><?= icon((string) ($cm['icon'] ?? 'check')) ?></span>
          <h3><?= rich((string) ($cm['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($cm['text'] ?? '')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php ks_cta(); ks_footer();
