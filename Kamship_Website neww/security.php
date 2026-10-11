<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Security and data handling | ' . c('site.brand'),
    "Physical, device, access, people and incident controls at Kamship Global's Pokhara delivery centre, plus our certification status stated plainly.",
    'security.php');
ks_header('about.php');
ks_page_head([['index.php', 'Home'], ['about.php', 'Company']], (string) c('pages.security.h1'), (string) c('pages.security.lead'), 53);
?>
<section><div class="wrap"><div class="grid g-3">
  <?php foreach (clist('pages.security.groups') as $g): ?>
    <article class="card" data-reveal>
      <span class="card-icon"><?= icon((string) ($g['icon'] ?? 'check')) ?></span>
      <h3><?= rich((string) ($g['title'] ?? '')) ?></h3>
      <ul class="checks"><?php foreach ((array) ($g['points'] ?? []) as $p): ?><li><?= rich((string) $p) ?></li><?php endforeach; ?></ul>
    </article>
  <?php endforeach; ?>
</div></div></section>

<section class="on-dark">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Certification status</p>
      <h2><?= rich((string) c('pages.security.cert_h2')) ?></h2>
      <p><?= rich((string) c('pages.security.cert_p1')) ?></p>
      <p><?= rich((string) c('pages.security.cert_p2')) ?></p>
      <div class="actions"><a class="btn btn-accent" href="contact.php">Request the security overview</a></div>
    </div>
    <div data-reveal="right">
      <h3>Questions we are asked, answered plainly</h3>
      <?php ks_faq(clist('pages.security.cert_faqs'), false); ?>
    </div>
  </div>
</section>
<?php
ks_cta('Send us your security questionnaire.', 'We complete it in full before you commit to anything, and we will walk your IT or compliance team through the answers on a call.');
ks_footer();
