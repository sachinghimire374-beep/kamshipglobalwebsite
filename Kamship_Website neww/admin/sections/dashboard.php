<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }

$user = auth_require();
$enq = store_read('enquiries', []);
$apps = store_read('applications', []);
$newEnq = count(array_filter($enq, static fn($x) => ($x['status'] ?? 'new') === 'new'));
$newApp = count(array_filter($apps, static fn($x) => ($x['status'] ?? 'new') === 'new'));
$openJobs = jobs_all(true);

admin_shell_open('dashboard', 'Dashboard', 'Everything on the website can be edited from the menu on the left.');
?>
<div class="acards">
  <a class="acard<?= $newEnq ? ' hot' : '' ?>" href="?p=enquiries">
    <span class="anum"><?= count($enq) ?></span>
    <span class="alabel">Enquiries<?= $newEnq ? ' &middot; ' . $newEnq . ' new' : '' ?></span>
  </a>
  <a class="acard<?= $newApp ? ' hot' : '' ?>" href="?p=applications">
    <span class="anum"><?= count($apps) ?></span>
    <span class="alabel">Job applications<?= $newApp ? ' &middot; ' . $newApp . ' new' : '' ?></span>
  </a>
  <a class="acard" href="?p=jobs">
    <span class="anum"><?= count($openJobs) ?></span>
    <span class="alabel">Roles open on the site</span>
  </a>
  <a class="acard" href="?p=services">
    <span class="anum"><?= count(clist('services')) ?></span>
    <span class="alabel">Service desks</span>
  </a>
</div>

<div class="agrid2">
  <section class="apanel">
    <h2>Latest enquiries</h2>
    <?php $recent = array_slice($enq, 0, 6); ?>
    <?php if (!$recent): ?>
      <p class="aempty">No enquiries yet. They will appear here as soon as someone uses the contact form.</p>
    <?php else: ?>
      <table class="atable">
        <thead><tr><th>From</th><th>Company</th><th>When</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $x): ?>
            <tr<?= ($x['status'] ?? 'new') === 'new' ? ' class="isnew"' : '' ?>>
              <td><a href="?p=enquiries#e-<?= e((string) $x['id']) ?>"><?= e((string) ($x['name'] ?? '')) ?></a></td>
              <td><?= e((string) ($x['company'] ?? '')) ?></td>
              <td class="anowrap"><?= e(date('j M, H:i', (int) strtotime((string) $x['received']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <section class="apanel">
    <h2>Latest applications</h2>
    <?php $recentA = array_slice($apps, 0, 6); ?>
    <?php if (!$recentA): ?>
      <p class="aempty">No applications yet. They will appear here when someone applies through the jobs page.</p>
    <?php else: ?>
      <table class="atable">
        <thead><tr><th>Applicant</th><th>Role</th><th>When</th></tr></thead>
        <tbody>
          <?php foreach ($recentA as $x): ?>
            <tr<?= ($x['status'] ?? 'new') === 'new' ? ' class="isnew"' : '' ?>>
              <td><a href="?p=applications#a-<?= e((string) $x['id']) ?>"><?= e((string) ($x['name'] ?? '')) ?></a></td>
              <td><?= e((string) ($x['job_title'] ?? '')) ?></td>
              <td class="anowrap"><?= e(date('j M, H:i', (int) strtotime((string) $x['received']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>

<section class="apanel">
  <h2>Quick edits</h2>
  <p class="aempty" style="margin-bottom:18px">The things people change most often.</p>
  <div class="aquick">
    <a href="?p=settings">Phone number and email</a>
    <a href="?p=jobs">Add or close a job</a>
    <a href="?p=pricing">Change the rates</a>
    <a href="?p=home">Home page headline</a>
    <a href="?p=services">Service descriptions</a>
    <a href="?p=faqs">Questions and answers</a>
  </div>
</section>

<section class="apanel">
  <h2>How saving works</h2>
  <p class="aempty">Every change is saved straight to the live website — there is no separate publish step.
     Before each save the previous version is copied into <code>data/backups/</code>, and the last 20 are kept,
     so a mistake can be undone by restoring a file over <code>data/content.json</code>.</p>
</section>
<?php admin_shell_close();
