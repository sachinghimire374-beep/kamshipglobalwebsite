<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('How the two-week pilot works | ' . c('site.brand'),
    'Scope one process, run a paid two-week pilot with written targets, then continue month to month. Full day-by-day timeline and answers to the questions buyers ask.',
    'pilot.php');
ks_header('about.php');
ks_page_head([['index.php', 'Home'], ['about.php', 'Company']], (string) c('pages.pilot.h1'), (string) c('pages.pilot.lead'), 59);
?>
<section>
  <div class="wrap">
    <ol class="steps">
      <?php foreach (clist('pages.pilot.steps') as $s): ?>
        <li data-reveal><h3><?= rich((string) ($s['title'] ?? '')) ?></h3><p><?= rich((string) ($s['text'] ?? '')) ?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Timeline</p>
      <h2><?= rich((string) c('pages.pilot.timeline_h2')) ?></h2>
      <p class="lead"><?= rich((string) c('pages.pilot.timeline_lead')) ?></p>
    </div>
    <div class="table-wrap" data-reveal="right">
      <table class="data">
        <thead><tr><th scope="col">When</th><th scope="col">What happens</th></tr></thead>
        <tbody>
          <?php foreach (clist('pages.pilot.timeline') as $r): ?>
            <tr><td><b><?= rich((string) ($r[0] ?? '')) ?></b></td><td><?= rich((string) ($r[1] ?? '')) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Fair questions</p>
      <h2>Before you commit to a fortnight</h2>
    </div>
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.pilot')); ?></div>
  </div>
</section>
<?php
ks_cta('Ready to scope one process?', 'Twenty minutes on a call tells us whether there is a sensible pilot here. If there is not, we will tell you that too.');
ks_footer();
