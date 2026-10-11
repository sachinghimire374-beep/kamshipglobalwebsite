<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Pricing | ' . c('site.brand'),
    'From $7 per hour per dedicated freight team member. Three tiers, published rates, no setup fee. Supervision, QA, backup cover and equipment included.',
    'pricing.php');
ks_header('pricing.php');
ks_page_head([['index.php', 'Home']], (string) c('pricing.h1'), (string) c('pricing.lead'), 61);
?>
<section>
  <div class="wrap">
    <div class="price-grid">
      <?php foreach (clist('pricing.tiers') as $t): ?>
        <article class="price<?= !empty($t['featured']) ? ' featured' : '' ?>" data-reveal>
          <?php if (!empty($t['featured'])): ?><span class="flag">Most chosen</span><?php endif; ?>
          <h3><?= rich((string) ($t['name'] ?? '')) ?></h3>
          <p class="small"><?= rich((string) ($t['blurb'] ?? '')) ?></p>
          <p class="amount" style="margin-top:18px"><sup>$</sup><?= e((string) ($t['day'] ?? '')) ?><span style="font-size:1rem;font-weight:600;letter-spacing:0"> /hour</span></p>
          <p class="per">$<?= e((string) ($t['night'] ?? '')) ?>/hour on the US night desk &middot; <?= rich((string) ($t['monthly'] ?? '')) ?> per seat per month</p>
          <ul class="checks"><?php foreach ((array) ($t['features'] ?? []) as $f): ?><li><?= rich((string) $f) ?></li><?php endforeach; ?></ul>
          <a class="btn <?= !empty($t['featured']) ? 'btn-primary' : 'btn-ghost' ?>" href="contact.php">Get a quote</a>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="fine mt-m" style="max-width:76ch"><?= rich((string) c('pricing.note')) ?></p>
  </div>
</section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Included as standard</p>
      <h2><?= rich((string) c('pricing.included_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('pricing.included_lead')) ?></p>
      <ul class="checks"><?php foreach (clist('pricing.included') as $i): ?><li><?= rich((string) $i) ?></li><?php endforeach; ?></ul>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3><?= rich((string) c('pricing.compare_title')) ?></h3>
        <div class="table-wrap" style="border:0">
          <table class="data" style="min-width:0"><tbody>
            <?php foreach (clist('pricing.compare_rows') as $r): ?>
              <tr><td><?= rich((string) ($r[0] ?? '')) ?></td><td><?= rich((string) ($r[1] ?? '')) ?></td></tr>
            <?php endforeach; ?>
          </tbody></table>
        </div>
        <p class="fine mt-m"><?= rich((string) c('pricing.compare_note')) ?></p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Commercials</p>
      <h2>Pricing questions, answered</h2>
    </div>
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.pricing')); ?></div>
  </div>
</section>
<?php
ks_cta('Want this priced for your actual volumes?', 'Send us the process and the weekly volume and we will come back with a seat count and a monthly number, not a range.');
ks_footer();
