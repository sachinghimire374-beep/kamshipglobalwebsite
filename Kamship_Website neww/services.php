<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';

ks_head(
    'Freight outsourcing services | ' . c('site.brand'),
    'Eight freight back-office desks: carrier vetting, track and trace, POD and billing, load entry, forwarding documentation, customs data and freight accounting.',
    'services.php'
);
ks_header('services.php');
ks_page_head(
    [['index.php', 'Home']],
    'Eight freight desks you can staff',
    'Each desk is a defined process with written targets, a trained team, an on-floor lead and daily quality checking. Start with one; add others when the first runs without you thinking about it.',
    11
);
?>
<section>
  <div class="wrap"><div class="grid g-3">
    <?php foreach (clist('services') as $s): ?>
      <article class="card svc" data-reveal>
        <span class="card-icon"><?= icon((string) ($s['icon'] ?? 'check')) ?></span>
        <h3><?= rich((string) ($s['title'] ?? '')) ?></h3>
        <p><?= rich((string) ($s['blurb'] ?? '')) ?></p>
        <ul class="tags"><?php foreach ((array) ($s['tags'] ?? []) as $t): ?><li><?= e((string) $t) ?></li><?php endforeach; ?></ul>
        <a class="arrow-link" href="service.php?s=<?= e(urlencode((string) $s['slug'])) ?>">What this desk does <?= KS_ARROW ?></a>
      </article>
    <?php endforeach; ?>
  </div></div>
</section>

<?php ks_marquee(); ?>

<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">What every desk includes</p>
      <h2>The same structure behind all eight</h2>
      <p class="lead">The work differs. The way it is supervised, measured and reported does not.</p>
    </div>
    <div data-reveal="right">
      <ul class="checks">
        <li>An SOP written with you before anyone is assigned to the work</li>
        <li>A named team lead on the floor and a separate QA reviewer</li>
        <li>Written targets for accuracy, turnaround and volume</li>
        <li>A report at the end of every shift and a weekly quality report with raw data</li>
        <li>A trained backup who knows your process, for absence and holiday cover</li>
        <li>Replacement of any team member within 10 business days at no cost</li>
        <li>Named, multi-factor access to your systems, revocable in minutes</li>
      </ul>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Not sure where to start?</p>
      <h2>Pick the process that is easiest to judge</h2>
      <p class="lead">The first desk you hand over decides whether there is a second one. Choose for clarity rather than for volume.</p>
    </div>
    <div class="grid g-2 mt-l">
      <article class="card" data-reveal>
        <span class="card-icon"><?= icon('check') ?></span>
        <h3>Good first candidates</h3>
        <ul class="checks">
          <li>POD collection and billing preparation</li>
          <li>Documented carrier vetting</li>
          <li>Appointment booking and carrier packet chasing</li>
          <li>Job file creation and arrival notices</li>
          <li>Load entry and data cleanup</li>
        </ul>
        <p class="small mt-m">High volume, obviously right or wrong, and a caught error costs an apology rather than an account.</p>
      </article>
      <article class="card" data-reveal>
        <span class="card-icon"><?= icon('search') ?></span>
        <h3>Leave these until later</h3>
        <ul class="checks">
          <li>Rate negotiation and margin decisions</li>
          <li>Customer escalation handling</li>
          <li>Anything requiring a licence or professional sign-off</li>
          <li>Processes that live only in one person's memory</li>
        </ul>
        <p class="small mt-m">Not because they cannot be supported eventually, but because they are a poor test of whether the arrangement works.</p>
      </article>
    </div>
    <div class="actions" data-reveal><a class="btn btn-primary" href="insight.php?p=first-process-to-outsource">How to choose, in more detail</a></div>
  </div>
</section>

<?php ks_cta(); ks_footer();
