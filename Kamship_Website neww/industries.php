<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Industries we serve | ' . c('site.brand'),
    'Freight brokers, 3PLs, forwarders, customs brokers, NVOCCs, shippers and freight audit firms. Freight is the only sector Kamship Global serves.',
    'industries.php');
ks_header('industries.php');
ks_page_head([['index.php', 'Home']], 'Freight is the only sector we serve',
    'Our training programme, SOP library and quality sampling are all built for freight. We turn down work outside it, because a team that does everything learns nothing deeply.', 31);
?>
<section><div class="wrap"><div class="grid g-3">
  <?php foreach (clist('industries') as $ind): ?>
    <article class="card svc" data-reveal>
      <span class="card-icon"><?= icon((string) ($ind['icon'] ?? 'check')) ?></span>
      <h3><?= rich((string) ($ind['name'] ?? '')) ?></h3>
      <p><?= rich((string) ($ind['text'] ?? '')) ?></p>
      <ul class="tags"><?php foreach ((array) ($ind['tags'] ?? []) as $t): ?><li><?= e((string) $t) ?></li><?php endforeach; ?></ul>
    </article>
  <?php endforeach; ?>
</div></div></section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Sizing</p>
      <h2>Built for companies of 20 to 300 staff</h2>
      <p class="lead">Big enough to have real volume in a defined process. Small enough that the large outsourcing firms will not take the account seriously.</p>
      <p>Large BPOs are built around hundred-seat engagements and price their governance accordingly. Freelance marketplaces go the other way and give you one unsupervised person. Freight companies of this size sit in between and are badly served by both.</p>
      <p>Our structure starts at two seats and still carries an on-floor lead, a separate QA reviewer, written targets and trained backup cover.</p>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col"></th><th scope="col">Freelancer</th><th scope="col">Kamship</th><th scope="col">Large BPO</th></tr></thead>
          <tbody>
            <tr><td>Minimum engagement</td><td>1 person</td><td><b>2 seats</b></td><td>20+ seats</td></tr>
            <tr><td>Supervision</td><td>None</td><td><b>Team lead + QA</b></td><td>Full</td></tr>
            <tr><td>Cover when absent</td><td>None</td><td><b>Trained backup</b></td><td>Pooled</td></tr>
            <tr><td>Written targets</td><td>Rare</td><td><b>Always</b></td><td>Always</td></tr>
            <tr><td>Freight specialism</td><td>Varies</td><td><b>Only freight</b></td><td>Varies</td></tr>
            <tr><td>Contract</td><td>Ad hoc</td><td><b>Month to month</b></td><td>1&ndash;3 years</td></tr>
          </tbody>
        </table>
      </div>
      <p class="fine mt-m">A generalised comparison of how these models are typically structured, offered to help you place us. Individual providers vary.</p>
    </div>
  </div>
</section>
<?php ks_cta(); ks_footer();
