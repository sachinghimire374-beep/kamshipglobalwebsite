<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';

$open = jobs_all(true);
ks_head('Careers in Pokhara | ' . c('site.brand'),
    'Freight Operations Associate, Documentation Specialist, Team Lead and QA roles in Pokhara. Paid three-week training, above-market pay and safe transport on night shifts.',
    'careers.php');
ks_header('careers.php');
ks_page_head([['index.php', 'Home']], (string) c('pages.careers.h1'), (string) c('pages.careers.lead'), 67);
?>
<section>
  <div class="wrap">
    <div class="stats" style="margin-bottom:60px">
      <?php foreach (clist('pages.careers.stats') as $st): ?>
        <div class="stat" data-reveal>
          <span class="num"><span data-count="<?= e((string) ($st['value'] ?? '0')) ?>">0</span><?php $s = (string) ($st['suffix'] ?? ''); if ($s !== ''): ?><i><?= e($s) ?></i><?php endif; ?></span>
          <span class="lab"><?= rich((string) ($st['label'] ?? '')) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="split" style="margin-bottom:40px">
      <div data-reveal="left">
        <p class="eyebrow">Open roles</p>
        <h2><?= $open ? count($open) . ' ' . (count($open) === 1 ? 'role' : 'roles') . ' open right now' : 'No roles open right now' ?></h2>
      </div>
      <div data-reveal="right">
        <p class="lead">Every role is based at our Pokhara office and starts with a paid three-week Freight Academy. No freight experience needed.</p>
        <div class="actions"><a class="btn btn-primary" href="jobs.php">See all open roles</a></div>
      </div>
    </div>

    <?php if ($open): ?>
      <div class="grid g-3">
        <?php foreach (array_slice($open, 0, 3) as $j): ?>
          <article class="card svc" data-reveal>
            <p class="eyebrow"><?= e((string) ($j['dept'] ?? '')) ?></p>
            <h3><?= rich((string) ($j['title'] ?? '')) ?></h3>
            <p><?= rich((string) ($j['summary'] ?? '')) ?></p>
            <ul class="tags">
              <li><?= e((string) ($j['shift'] ?? '')) ?></li>
              <?php if (trim((string) ($j['openings'] ?? '')) !== ''): ?><li><?= e((string) $j['openings']) ?> positions</li><?php endif; ?>
            </ul>
            <a class="arrow-link" href="job.php?j=<?= e(urlencode((string) $j['slug'])) ?>">View and apply <?= KS_ARROW ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="on-dark">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">What we offer</p>
      <h2><?= rich((string) c('pages.careers.offer_h2')) ?></h2>
      <ul class="checks">
        <?php foreach (clist('pages.careers.offer') as $o): ?><li><?= rich((string) $o) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3><?= rich((string) c('pages.careers.process_title')) ?></h3>
        <ol class="mini-steps">
          <?php foreach (clist('pages.careers.process') as $st): ?>
            <li><b><?= rich((string) ($st[0] ?? '')) ?></b><span class="small"><?= rich((string) ($st[1] ?? '')) ?></span></li>
          <?php endforeach; ?>
        </ol>
        <p class="small mt-m"><?= rich((string) c('pages.careers.women_note')) ?></p>
        <div class="actions"><a class="btn btn-accent btn-sm" href="jobs.php">Browse open roles</a></div>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Questions candidates ask</p>
      <h2>Before you apply</h2>
    </div>
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.careers')); ?></div>
  </div>
</section>

<section class="cta-band on-dark">
  <?= hero_bg(77) ?>
  <div class="wrap" style="text-align:center">
    <h2 style="margin-inline:auto" data-reveal>Your freight career starts with one application.</h2>
    <p class="lead" style="margin-inline:auto" data-reveal>Paid training, international clients, and a real path upward &mdash; from Pokhara.</p>
    <div class="actions" style="justify-content:center" data-reveal><a class="btn btn-accent" href="jobs.php">See open roles</a></div>
  </div>
</section>
<?php ks_footer();
