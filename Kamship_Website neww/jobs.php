<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';

$jobs = jobs_all(true);

// Optional filters from the query string.
$fDept  = (string) ($_GET['dept'] ?? '');
$fShift = (string) ($_GET['shift'] ?? '');

$depts  = array_values(array_unique(array_map(static fn($j) => (string) ($j['dept'] ?? ''), $jobs)));
$shifts = array_values(array_unique(array_map(static function ($j) {
    return str_contains(strtolower((string) ($j['shift'] ?? '')), 'night') ? 'Night' : 'Day';
}, $jobs)));
sort($depts);
sort($shifts);

$shown = array_values(array_filter($jobs, static function ($j) use ($fDept, $fShift) {
    if ($fDept !== '' && (string) ($j['dept'] ?? '') !== $fDept) {
        return false;
    }
    if ($fShift !== '') {
        $isNight = str_contains(strtolower((string) ($j['shift'] ?? '')), 'night');
        if (($fShift === 'Night') !== $isNight) {
            return false;
        }
    }
    return true;
}));

$totalOpenings = 0;
foreach ($jobs as $j) {
    $totalOpenings += max(0, (int) ($j['openings'] ?? 0));
}

$domain = rtrim((string) c('site.domain'), '/');
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Open roles at ' . c('site.brand'),
    'numberOfItems' => count($jobs),
    'itemListElement' => array_values(array_map(static function ($j, $i) use ($domain) {
        return [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => $domain . '/job.php?j=' . rawurlencode((string) ($j['slug'] ?? '')),
            'name' => strip_tags((string) ($j['title'] ?? '')),
        ];
    }, $jobs, array_keys($jobs))),
];

ks_head(
    'Jobs in Pokhara | ' . c('site.brand'),
    'Current openings at Kamship Global in Pokhara: freight operations, documentation, team lead, QA and HR roles. Paid three-week training, no freight experience needed.',
    'jobs.php',
    $schema
);
ks_header('careers.php');
ks_page_head([['index.php', 'Home'], ['careers.php', 'Careers']], (string) c('pages.jobs.h1'), (string) c('pages.jobs.lead'), 67);
?>

<section>
  <div class="wrap">
    <?php if (!$jobs): ?>
      <div class="placeholder-note" data-reveal>
        <h3>No open roles at the moment</h3>
        <p><?= rich((string) c('pages.jobs.empty')) ?></p>
        <div class="actions"><a class="btn btn-primary" href="mailto:<?= e((string) c('site.email')) ?>?subject=Future%20openings">Email us about future openings</a></div>
      </div>
    <?php else: ?>

      <div class="jobs-bar" data-reveal>
        <p class="jobs-count">
          <b><?= count($jobs) ?></b> open <?= count($jobs) === 1 ? 'role' : 'roles' ?>
          <?php if ($totalOpenings > 0): ?> &middot; <b><?= $totalOpenings ?></b> positions to fill<?php endif; ?>
          &middot; all based in Pokhara
        </p>
        <?php if (count($depts) > 1 || count($shifts) > 1): ?>
          <form class="jobs-filter" method="get" action="jobs.php">
            <label class="vh" for="fdept">Filter by team</label>
            <select id="fdept" name="dept">
              <option value="">All teams</option>
              <?php foreach ($depts as $d): ?>
                <option value="<?= e($d) ?>"<?= $fDept === $d ? ' selected' : '' ?>><?= e($d) ?></option>
              <?php endforeach; ?>
            </select>
            <label class="vh" for="fshift">Filter by shift</label>
            <select id="fshift" name="shift">
              <option value="">Any shift</option>
              <?php foreach ($shifts as $s): ?>
                <option value="<?= e($s) ?>"<?= $fShift === $s ? ' selected' : '' ?>><?= e($s) ?> shift</option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
            <?php if ($fDept !== '' || $fShift !== ''): ?>
              <a class="btn btn-sm btn-ghost" href="jobs.php">Clear</a>
            <?php endif; ?>
          </form>
        <?php endif; ?>
      </div>

      <?php if (!$shown): ?>
        <div class="placeholder-note mt-l" data-reveal>
          <h3>Nothing matches that filter</h3>
          <p>There are no open roles in that team or shift right now.</p>
          <div class="actions"><a class="btn btn-ghost" href="jobs.php">Show all roles</a></div>
        </div>
      <?php endif; ?>

      <div class="job-list mt-l">
        <?php foreach ($shown as $j): ?>
          <?php $closed = job_closed($j); ?>
          <article class="job-card<?= !empty($j['featured']) ? ' featured' : '' ?>" data-reveal>
            <div class="job-main">
              <div class="job-head">
                <h2><a href="job.php?j=<?= e(urlencode((string) $j['slug'])) ?>"><?= rich((string) ($j['title'] ?? '')) ?></a></h2>
                <?php if (!empty($j['featured'])): ?><span class="chip chip-accent">Hiring now</span><?php endif; ?>
                <?php if ($closed): ?><span class="chip">Closed</span><?php endif; ?>
              </div>
              <p class="job-sum"><?= rich((string) ($j['summary'] ?? '')) ?></p>
              <ul class="job-meta">
                <li><?= icon('briefcase', '', 16) ?><?= e((string) ($j['dept'] ?? '')) ?></li>
                <li><?= icon('pin', '', 16) ?><?= e((string) ($j['location'] ?? '')) ?></li>
                <li><?= icon('clock', '', 16) ?><?= e((string) ($j['shift'] ?? '')) ?></li>
                <?php if (trim((string) ($j['openings'] ?? '')) !== ''): ?>
                  <li><?= icon('users', '', 16) ?><?= e((string) $j['openings']) ?> <?= (int) $j['openings'] === 1 ? 'position' : 'positions' ?></li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="job-side">
              <?php if (trim((string) ($j['salary'] ?? '')) !== ''): ?>
                <p class="job-pay"><?= rich((string) $j['salary']) ?></p>
              <?php endif; ?>
              <?php if (trim((string) ($j['closing'] ?? '')) !== ''): ?>
                <p class="fine">Applications close <?= e(date('j M Y', (int) strtotime((string) $j['closing']))) ?></p>
              <?php endif; ?>
              <a class="btn btn-primary btn-sm" href="job.php?j=<?= e(urlencode((string) $j['slug'])) ?>">
                <?= $closed ? 'View role' : 'View and apply' ?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="small mt-l" style="max-width:70ch"><?= rich((string) c('pages.jobs.apply_intro')) ?></p>
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
      <div class="actions"><a class="btn btn-ghost" href="careers.php">More about working here</a></div>
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
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.jobs')); ?></div>
  </div>
</section>
<?php ks_footer();
