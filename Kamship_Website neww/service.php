<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';

$slug = (string) ($_GET['s'] ?? '');
$svc = null;
$idx = 0;
foreach (clist('services') as $i => $s) {
    if (($s['slug'] ?? '') === $slug) { $svc = $s; $idx = $i; break; }
}
if ($svc === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$plain = trim(strip_tags(str_replace(['&mdash;', '&amp;'], ['-', 'and'], (string) $svc['blurb'])));
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => strip_tags((string) $svc['title']),
    'provider' => ['@type' => 'Organization', 'name' => c('site.brand'), 'url' => c('site.domain')],
    'areaServed' => ['US', 'CA', 'GB', 'AE', 'SG', 'AU'],
    'description' => $plain,
];

ks_head(
    strip_tags((string) $svc['title']) . ' | ' . c('site.brand'),
    mb_substr($plain, 0, 158),
    'service.php?s=' . $slug,
    $schema
);
ks_header('services.php');
ks_page_head(
    [['index.php', 'Home'], ['services.php', 'Services']],
    (string) $svc['title'],
    (string) ($svc['lead'] ?? ''),
    20 + $idx
);

$others = array_values(array_filter(clist('services'), static fn($o) => ($o['slug'] ?? '') !== $slug));
$others = array_slice($others, 0, 3);
?>
<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">In short</p>
      <h2><?= rich((string) $svc['blurb']) ?></h2>
      <ul class="tags" style="margin-top:22px"><?php foreach ((array) ($svc['tags'] ?? []) as $t): ?><li><?= e((string) $t) ?></li><?php endforeach; ?></ul>
      <div class="actions">
        <a class="btn btn-primary" href="contact.php">Discuss this desk</a>
        <a class="btn btn-ghost" href="pricing.php">See pricing</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">What we commit to</th><th scope="col">Target</th></tr></thead>
          <tbody>
            <?php foreach ((array) ($svc['targets'] ?? []) as $t): ?>
              <tr><td><?= rich((string) ($t[0] ?? '')) ?></td><td><b><?= rich((string) ($t[1] ?? '')) ?></b></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="fine mt-m">Targets are agreed in writing during scoping and measured by daily QA sampling. The raw sampling data is shared with you weekly.</p>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">The work itself</p>
      <h2>What this desk handles</h2>
    </div>
    <div class="grid g-3 mt-l">
      <?php foreach ((array) ($svc['what'] ?? []) as $w): ?>
        <article class="card" data-reveal>
          <h3><?= rich((string) ($w[0] ?? '')) ?></h3>
          <p><?= rich((string) ($w[1] ?? '')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if (trim((string) ($svc['note'] ?? '')) !== ''): ?>
      <p class="fine mt-m" style="max-width:72ch"><?= rich((string) $svc['note']) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="on-dark">
  <div class="wrap split center">
    <div data-reveal="left">
      <p class="eyebrow">Systems</p>
      <h2>We work in your tools, on your licences</h2>
      <p class="lead">Our teams are trained on <?= rich((string) ($svc['tools'] ?? '')) ?>.</p>
      <p>We do not ask you to move to our platform, because there isn't one. Your data stays in your systems, your audit trail stays intact, and if you end the engagement your process carries on exactly where it was.</p>
    </div>
    <div data-reveal="right">
      <h3>What stays with you</h3>
      <ul class="checks">
        <li>Every approval, pricing and commercial decision</li>
        <li>Customer relationships and negotiations</li>
        <li>Licensed and professional judgements</li>
        <li>Your systems, your data and your audit trail</li>
      </ul>
      <p class="small mt-m">We execute defined steps and document them. Where a step needs judgement outside the SOP, it comes to your named approver rather than being decided on the floor.</p>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Also available</p>
      <h2>Other desks teams add</h2>
    </div>
    <div class="grid g-3 mt-l">
      <?php foreach ($others as $o): ?>
        <article class="card svc" data-reveal>
          <span class="card-icon"><?= icon((string) ($o['icon'] ?? 'check')) ?></span>
          <h3><?= rich((string) ($o['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($o['blurb'] ?? '')) ?></p>
          <a class="arrow-link" href="service.php?s=<?= e(urlencode((string) $o['slug'])) ?>">What this desk does <?= KS_ARROW ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
ks_cta('Try this desk for two weeks.', 'We will scope the process in your systems, agree the targets in writing, and run a paid two-week pilot. If the targets are missed, you walk away.');
ks_footer();
