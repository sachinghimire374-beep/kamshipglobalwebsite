<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    require __DIR__ . '/inc/bootstrap.php';
    require __DIR__ . '/inc/render.php';
    http_response_code(404);
}
ks_head('Page not found | ' . c('site.brand'),
    "That page could not be found. Browse Kamship Global's freight back-office services, pricing and careers instead.",
    '404.php', null, '<meta name="robots" content="noindex">');
ks_header('');
?>
<section class="page-head on-dark" style="min-height:62vh;display:flex;align-items:center">
  <?= hero_bg(95) ?>
  <div class="wrap" style="text-align:center">
    <p class="eyebrow" style="justify-content:center">Error 404</p>
    <h1 style="margin-inline:auto">This page has been re-routed.</h1>
    <p class="lead" style="margin-inline:auto">The link may be out of date or mistyped. Try the home page, or tell us what you were looking for.</p>
    <div class="actions" style="justify-content:center">
      <a class="btn btn-accent" href="index.php">Go to the home page</a>
      <a class="btn btn-ghost" href="contact.php">Contact us</a>
    </div>
  </div>
</section>
<section>
  <div class="wrap">
    <div style="max-width:58ch"><p class="eyebrow">Popular pages</p><h2>You might want one of these</h2></div>
    <div class="grid g-3 mt-l">
      <article class="card svc"><h3>Services</h3><p>Eight freight desks you can staff, each with written targets.</p><a class="arrow-link" href="services.php">See services <?= KS_ARROW ?></a></article>
      <article class="card svc"><h3>Pricing</h3><p>Published hourly rates from $7, with everything included.</p><a class="arrow-link" href="pricing.php">See pricing <?= KS_ARROW ?></a></article>
      <article class="card svc"><h3>Jobs in Pokhara</h3><p>Open roles with paid three-week training.</p><a class="arrow-link" href="jobs.php">See open roles <?= KS_ARROW ?></a></article>
    </div>
  </div>
</section>
<?php ks_footer();
