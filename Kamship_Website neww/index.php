<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';

$domain = rtrim((string) c('site.domain'), '/');
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => c('site.brand'),
    'legalName' => c('site.company'),
    'url' => $domain,
    'logo' => $domain . '/assets/img/kamship-icon-512.png',
    'image' => $domain . '/assets/img/og-image-1200x630.png',
    'email' => c('site.email'),
    'telephone' => c('site.phone_display'),
    'priceRange' => 'From $7 per hour',
    'description' => 'Supervised freight back-office teams for brokers, 3PLs, forwarders and customs brokers, delivered from Pokhara, Nepal.',
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Pokhara', 'addressRegion' => 'Gandaki', 'addressCountry' => 'NP'],
    'areaServed' => ['US', 'CA', 'GB', 'IE', 'AE', 'SA', 'QA', 'SG', 'HK', 'AU', 'NZ', 'NL', 'DE'],
    'sameAs' => array_values(array_filter([c('site.linkedin'), c('site.facebook')])),
    'makesOffer' => array_map(
        static fn($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => strip_tags((string) ($s['title'] ?? ''))]],
        clist('services')
    ),
];

ks_head(
    c('site.brand') . ' | Freight back-office teams for brokers and forwarders',
    'Supervised freight back-office teams in Pokhara, Nepal: carrier vetting, tracking, POD and billing, forwarding documents. From $7/hour, paid two-week pilot.',
    'index.php',
    $schema
);
ks_header('index.php');
?>

<section class="hero on-dark">
  <?= hero_bg(3) ?>
  <div class="wrap hero-grid">
    <div>
      <span class="hero-badge" data-enter><span class="pulse-dot"></span> <?= e((string) c('home.badge_prefix')) ?> <b><?= e((string) c('home.badge_place')) ?></b></span>
      <h1 data-enter><?= rich((string) c('home.h1_prefix')) ?>
        <span class="rotator">
          <?php foreach (clist('home.rotator') as $w): ?><span><?= e((string) $w) ?></span><?php endforeach; ?>
        </span>
      </h1>
      <p class="lead" data-enter><?= rich((string) c('home.lead')) ?></p>
      <div class="actions" data-enter>
        <a class="btn btn-accent" href="<?= url((string) c('home.cta1_link')) ?>"><?= e((string) c('home.cta1_text')) ?></a>
        <a class="btn btn-ghost" href="<?= url((string) c('home.cta2_link')) ?>"><?= e((string) c('home.cta2_text')) ?></a>
      </div>
      <p class="hero-note" data-enter><?= rich((string) c('home.hero_note')) ?></p>
    </div>

    <div data-enter style="--d:240ms">
      <div class="board" aria-label="Illustration of a live overnight desk queue">
        <div class="board-head">
          <div class="clock here"><span>Pokhara desk</span><time data-tz="Asia/Kathmandu">--:--</time></div>
          <div class="clock"><span>Chicago</span><time data-tz="America/Chicago">--:--</time></div>
          <div class="clock"><span>Dubai</span><time data-tz="Asia/Dubai">--:--</time></div>
        </div>
        <table>
          <thead><tr><th scope="col">Item</th><th scope="col">Lane or party</th><th scope="col">Status</th></tr></thead>
          <tbody>
            <?php foreach (clist('home.board_rows') as $r): ?>
              <tr>
                <td><?= rich((string) ($r['item'] ?? '')) ?></td>
                <td><?= rich((string) ($r['lane'] ?? '')) ?></td>
                <td>
                  <?php $then = trim((string) ($r['then'] ?? '')); ?>
                  <?php if ($then !== ''): ?>
                    <span class="status work" data-next="<?= e($then) ?>"<?= ($r['state'] ?? 'done') === 'flag' ? ' data-next-class="flag"' : '' ?>><?= e((string) ($r['now'] ?? '')) ?></span>
                  <?php else: ?>
                    <span class="status <?= e((string) ($r['state'] ?? 'done')) ?>"><?= e((string) ($r['now'] ?? '')) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="board-foot"><?= rich((string) c('home.board_caption')) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="on-deep on-dark" style="padding-block:clamp(40px,4vw,64px)">
  <div class="wrap">
    <div class="stats">
      <?php foreach (clist('home.stats') as $st): ?>
        <div class="stat" data-reveal>
          <span class="num">
            <?php $lit = trim((string) ($st['literal'] ?? '')); ?>
            <?php if ($lit !== ''): ?>
              <?= e($lit) ?>
            <?php else: ?>
              <?= e((string) ($st['prefix'] ?? '')) ?><span data-count="<?= e((string) ($st['value'] ?? '0')) ?>">0</span><?php $sfx = (string) ($st['suffix'] ?? ''); ?><?php if ($sfx !== ''): ?><i><?= e($sfx) ?></i><?php endif; ?>
            <?php endif; ?>
          </span>
          <span class="lab"><?= rich((string) ($st['label'] ?? '')) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="fine mt-m" style="max-width:74ch"><?= rich((string) c('home.stats_note')) ?></p>
  </div>
</section>

<?php ks_marquee(); ?>

<section>
  <div class="wrap">
    <div class="split" style="margin-bottom:56px">
      <div data-reveal="left">
        <p class="eyebrow"><?= e((string) c('home.pillars_eyebrow')) ?></p>
        <h2><?= rich((string) c('home.pillars_h2')) ?></h2>
      </div>
      <div data-reveal="right">
        <p class="lead"><?= rich((string) c('home.pillars_lead')) ?></p>
      </div>
    </div>
    <div class="grid g-4">
      <?php foreach (clist('home.pillars') as $p): ?>
        <article class="card pillar" data-reveal>
          <span class="card-icon"><?= icon((string) ($p['icon'] ?? 'check')) ?></span>
          <h3><?= rich((string) ($p['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($p['text'] ?? '')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:60ch;margin-bottom:12px" data-reveal>
      <p class="eyebrow"><?= e((string) c('home.services_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.services_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('home.services_lead')) ?></p>
    </div>
    <div class="grid g-3 mt-l">
      <?php foreach (clist('services') as $s): ?>
        <article class="card svc" data-reveal>
          <span class="card-icon"><?= icon((string) ($s['icon'] ?? 'check')) ?></span>
          <h3><?= rich((string) ($s['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($s['blurb'] ?? '')) ?></p>
          <ul class="tags"><?php foreach ((array) ($s['tags'] ?? []) as $t): ?><li><?= e((string) $t) ?></li><?php endforeach; ?></ul>
          <a class="arrow-link" href="service.php?s=<?= e(urlencode((string) $s['slug'])) ?>">What this desk does <?= KS_ARROW ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap">
    <div style="max-width:62ch" data-reveal>
      <p class="eyebrow"><?= e((string) c('home.desks_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.desks_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('home.desks_lead')) ?></p>
    </div>

    <div class="tabs" data-tabs data-reveal>
      <div class="tablist" role="tablist" aria-label="Desk coverage">
        <button role="tab" id="tab-us" aria-controls="panel-us" aria-selected="true" tabindex="0">US brokerage night desk</button>
        <button role="tab" id="tab-fw" aria-controls="panel-fw" aria-selected="false" tabindex="-1">Global forwarding day desk</button>
      </div>

      <?php
      $desks = [
          ['us', 'desk_us_title', 'desk_us_text', 'desk_us_points', 'desk_us_rows', 'desk_us_note', 'Your time zone', 'industries.php', 'Who this suits', false],
          ['fw', 'desk_fw_title', 'desk_fw_text', 'desk_fw_points', 'desk_fw_rows', 'desk_fw_note', 'Your region', 'service.php?s=forwarding-documentation', 'Forwarding desk detail', true],
      ];
      foreach ($desks as [$id, $kT, $kX, $kP, $kR, $kN, $col1, $lnk, $lnkT, $hidden]): ?>
        <div role="tabpanel" id="panel-<?= $id ?>" aria-labelledby="tab-<?= $id ?>" class="tabpanel"<?= $hidden ? ' hidden' : '' ?>>
          <div class="split">
            <div>
              <h3><?= rich((string) c('home.' . $kT)) ?></h3>
              <p><?= rich((string) c('home.' . $kX)) ?></p>
              <ul class="checks">
                <?php foreach (clist('home.' . $kP) as $pt): ?><li><?= rich((string) $pt) ?></li><?php endforeach; ?>
              </ul>
              <div class="actions"><a class="btn btn-ghost btn-sm" href="<?= e($lnk) ?>"><?= e($lnkT) ?></a></div>
            </div>
            <div class="table-wrap">
              <table class="data">
                <thead><tr><th scope="col"><?= e($col1) ?></th><th scope="col">Our shift, Nepal time</th></tr></thead>
                <tbody>
                  <?php foreach (clist('home.' . $kR) as $row): ?>
                    <tr><td><?= rich((string) ($row[0] ?? '')) ?></td><td><?= rich((string) ($row[1] ?? '')) ?></td></tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <p class="fine" style="padding:14px 20px 0"><?= rich((string) c('home.' . $kN)) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow"><?= e((string) c('home.steps_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.steps_h2')) ?></h2>
    </div>
    <ol class="steps">
      <?php foreach (clist('home.steps') as $st): ?>
        <li data-reveal><h3><?= rich((string) ($st['title'] ?? '')) ?></h3><p><?= rich((string) ($st['text'] ?? '')) ?></p></li>
      <?php endforeach; ?>
    </ol>
    <div class="actions" data-reveal><a class="btn btn-primary" href="pilot.php">How the pilot works in detail</a></div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap split center">
    <div data-reveal="left">
      <p class="eyebrow"><?= e((string) c('home.vetting_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.vetting_h2')) ?></h2>
      <p><?= rich((string) c('home.vetting_p1')) ?></p>
      <p><?= rich((string) c('home.vetting_p2')) ?></p>
      <div class="actions">
        <a class="btn btn-accent" href="service.php?s=carrier-vetting">How the vetting desk works</a>
        <a class="btn btn-ghost" href="insight.php?p=carrier-vetting-after-montgomery">Read our note on the ruling</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">Commitment</th><th scope="col">Standard</th></tr></thead>
          <tbody>
            <?php foreach (clist('home.vetting_rows') as $row): ?>
              <tr><td><?= rich((string) ($row[0] ?? '')) ?></td><td><?= rich((string) ($row[1] ?? '')) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="fine mt-m"><?= rich((string) c('home.vetting_note')) ?></p>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow"><?= e((string) c('home.industries_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.industries_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('home.industries_lead')) ?></p>
    </div>
    <div class="grid g-3 mt-l">
      <?php foreach (array_slice(clist('industries'), 0, 6) as $ind): ?>
        <article class="card" data-reveal>
          <span class="card-icon"><?= icon((string) ($ind['icon'] ?? 'check')) ?></span>
          <h3><?= rich((string) ($ind['name'] ?? '')) ?></h3>
          <p><?= rich((string) ($ind['text'] ?? '')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="actions" data-reveal><a class="btn btn-ghost" href="industries.php">More on each sector</a></div>
  </div>
</section>

<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow"><?= e((string) c('home.proof_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.proof_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('home.proof_lead')) ?></p>
      <p><?= rich((string) c('home.proof_p')) ?></p>
      <div class="actions">
        <a class="btn btn-primary" href="contact.php">Request a sample QA report</a>
        <a class="btn btn-ghost" href="security.php">Read our security controls</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="placeholder-note">
        <h3><?= rich((string) c('home.proof_card_title')) ?></h3>
        <ul class="checks accent">
          <?php foreach (clist('home.proof_card_points') as $pt): ?><li><?= rich((string) $pt) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div class="split" style="margin-bottom:40px">
      <div data-reveal="left">
        <p class="eyebrow"><?= e((string) c('home.insights_eyebrow')) ?></p>
        <h2><?= rich((string) c('home.insights_h2')) ?></h2>
      </div>
      <div data-reveal="right"><p class="lead"><?= rich((string) c('home.insights_lead')) ?></p></div>
    </div>
    <div class="grid g-3">
      <?php foreach (array_slice(posts_all(true), 0, 3) as $p): ?>
        <article class="card" data-reveal>
          <p class="eyebrow"><?= e((string) ($p['cat'] ?? '')) ?></p>
          <h3><?= rich((string) ($p['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($p['excerpt'] ?? '')) ?></p>
          <a class="arrow-link" href="insight.php?p=<?= e(urlencode((string) $p['slug'])) ?>">Read this <?= KS_ARROW ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow"><?= e((string) c('home.faq_eyebrow')) ?></p>
      <h2><?= rich((string) c('home.faq_h2')) ?></h2>
    </div>
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.home')); ?></div>
  </div>
</section>

<?php ks_cta(); ks_footer();
