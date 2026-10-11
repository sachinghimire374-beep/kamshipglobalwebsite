<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Why Nepal for freight outsourcing | ' . c('site.brand'),
    "Nepal's UTC+05:45 time zone covers the US night and the Gulf and European day from one centre. Why Pokhara means lower attrition, and where Nepal is not the answer.",
    'why-nepal.php');
ks_header('about.php');
ks_page_head([['index.php', 'Home'], ['about.php', 'Company']], (string) c('pages.why_nepal.h1'), (string) c('pages.why_nepal.lead'), 47);
?>
<section>
  <div class="wrap split">
    <div class="prose" data-reveal="left"><?php ks_blocks(clist('pages.why_nepal.body')); ?></div>
    <div data-reveal="right">
      <div class="card">
        <h3><?= rich((string) c('pages.why_nepal.coverage_title')) ?></h3>
        <div class="table-wrap" style="border:0">
          <table class="data" style="min-width:0"><tbody>
            <?php foreach (clist('pages.why_nepal.coverage_rows') as $r): ?>
              <tr><td><?= rich((string) ($r[0] ?? '')) ?></td><td><?= rich((string) ($r[1] ?? '')) ?></td></tr>
            <?php endforeach; ?>
          </tbody></table>
        </div>
      </div>
      <div class="card mt-m">
        <h3><?= rich((string) c('pages.why_nepal.continuity_title')) ?></h3>
        <ul class="checks">
          <?php foreach (clist('pages.why_nepal.continuity') as $p): ?><li><?= rich((string) $p) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
<?php ks_cta(); ks_footer();
