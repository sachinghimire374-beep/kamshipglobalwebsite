<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
require __DIR__ . '/inc/forms.php';

// The page carries an application form, so the session (and its CSRF token)
// must exist before a single byte of output is sent.
ks_session_start();

$slug = (string) ($_GET['j'] ?? '');
$job = job_by_slug($slug);

if ($job === null || ($job['status'] ?? 'open') !== 'open') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$closed = job_closed($job);
$errors = [];
$sent = false;
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$closed) {
    csrf_require();
    [$sent, $errors, $old] = handle_application($job);
}

$plainSummary = trim(strip_tags((string) ($job['summary'] ?? '')));
$domain = rtrim((string) c('site.domain'), '/');

$schema = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => strip_tags((string) $job['title']),
    'description' => $plainSummary,
    'employmentType' => strtoupper(str_replace('-', '_', (string) ($job['type'] ?? 'FULL_TIME'))),
    'datePosted' => (string) ($job['posted'] ?? date('Y-m-d')),
    'validThrough' => trim((string) ($job['closing'] ?? '')) !== '' ? (string) $job['closing'] : null,
    'hiringOrganization' => [
        '@type' => 'Organization',
        'name' => c('site.company'),
        'sameAs' => $domain,
        'logo' => $domain . '/assets/img/kamship-icon-512.png',
    ],
    'jobLocation' => [
        '@type' => 'Place',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Pokhara',
            'addressRegion' => 'Gandaki',
            'addressCountry' => 'NP',
        ],
    ],
    'totalJobOpenings' => (int) ($job['openings'] ?? 0) ?: null,
]);

ks_head(
    strip_tags((string) $job['title']) . ' in Pokhara | ' . c('site.brand'),
    mb_substr($plainSummary, 0, 158),
    'job.php?j=' . $slug,
    $schema
);
ks_header('careers.php');
ks_page_head(
    [['index.php', 'Home'], ['careers.php', 'Careers'], ['jobs.php', 'Open roles']],
    (string) $job['title'],
    (string) ($job['summary'] ?? ''),
    73
);

$v = static fn(string $k): string => e((string) ($old[$k] ?? ''));
$sections = [
    ['About this role', 'about', 'text'],
    ['What you will do', 'responsibilities', 'list'],
    ['What we are looking for', 'requirements', 'list'],
    ['Helpful, but not required', 'nice', 'list'],
    ['What you get', 'benefits', 'list'],
];
?>

<section>
  <div class="wrap split">
    <div class="prose" data-reveal="left">
      <?php foreach ($sections as [$heading, $key, $kind]): ?>
        <?php $val = $job[$key] ?? ''; ?>
        <?php if ($kind === 'text' && trim((string) $val) !== ''): ?>
          <h2><?= e($heading) ?></h2>
          <p><?= rich((string) $val) ?></p>
        <?php elseif ($kind === 'list' && is_array($val) && $val): ?>
          <h2><?= e($heading) ?></h2>
          <ul><?php foreach ($val as $li): ?><li><?= rich((string) $li) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
      <?php endforeach; ?>

      <h2>Where and when</h2>
      <p>This role is based at our supervised office in Pokhara. Client work cannot be done from home,
         so you will need to be in Pokhara or willing to move here.</p>
    </div>

    <div data-reveal="right">
      <div class="card job-facts">
        <h3>At a glance</h3>
        <dl>
          <?php
          $facts = [
              'Team' => $job['dept'] ?? '',
              'Location' => $job['location'] ?? '',
              'Shift' => $job['shift'] ?? '',
              'Contract' => $job['type'] ?? '',
              'Positions' => $job['openings'] ?? '',
              'Experience' => $job['experience'] ?? '',
              'Pay' => $job['salary'] ?? '',
          ];
          foreach ($facts as $k => $val):
              if (trim((string) $val) === '') { continue; } ?>
            <dt><?= e($k) ?></dt><dd><?= rich((string) $val) ?></dd>
          <?php endforeach; ?>
          <?php if (trim((string) ($job['closing'] ?? '')) !== ''): ?>
            <dt>Closes</dt><dd><?= e(date('j F Y', (int) strtotime((string) $job['closing']))) ?></dd>
          <?php endif; ?>
        </dl>
        <?php if (!$closed && !$sent): ?>
          <a class="btn btn-primary" href="#apply" style="width:100%">Apply for this role</a>
        <?php endif; ?>
      </div>

      <div class="card mt-m">
        <h3>Not sure you qualify?</h3>
        <p class="small">Almost nobody in Nepal arrives with freight experience, and our three-week paid
           Freight Academy exists for exactly that reason. If you have careful attention to detail and
           good written English, apply and let us decide.</p>
      </div>
    </div>
  </div>
</section>

<section class="on-paper" id="apply">
  <div class="wrap-narrow">
    <?php if ($closed): ?>
      <div class="placeholder-note" data-reveal>
        <h3>Applications for this role have closed</h3>
        <p>This role closed on <?= e(date('j F Y', (int) strtotime((string) $job['closing']))) ?>.
           We hire in cohorts, so a similar role usually opens within a few weeks.</p>
        <div class="actions"><a class="btn btn-primary" href="jobs.php">See roles that are open</a></div>
      </div>

    <?php elseif ($sent): ?>
      <div class="form-card" data-reveal>
        <h2 style="font-size:var(--t-2)">Application received</h2>
        <p>Thank you. Your application for <b><?= rich((string) $job['title']) ?></b> is with our HR team in Pokhara.</p>
        <p>We reply to every application, including the unsuccessful ones. If you have not heard from us within
           two weeks of the closing date, email <a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a>
           and we will chase it.</p>
        <div class="actions">
          <a class="btn btn-ghost" href="jobs.php">See other open roles</a>
          <a class="btn btn-ghost" href="careers.php">More about working here</a>
        </div>
      </div>

    <?php else: ?>
      <div style="max-width:58ch;margin-bottom:28px" data-reveal>
        <p class="eyebrow">Apply</p>
        <h2>Apply for <?= rich((string) $job['title']) ?></h2>
        <p class="lead"><?= rich((string) c('pages.jobs.apply_intro')) ?></p>
      </div>

      <form class="form-card" method="post" action="job.php?j=<?= e(urlencode($slug)) ?>#apply"
            enctype="multipart/form-data" data-reveal novalidate>
        <?= csrf_field() ?>
        <?= honeypot_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= CV_MAX_BYTES ?>">

        <?php if ($errors): ?>
          <div class="form-errors" role="alert">
            <b>Please check the following:</b>
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <div class="field-row">
          <div class="field">
            <label for="a-name">Full name</label>
            <input id="a-name" name="name" type="text" autocomplete="name" maxlength="120" value="<?= $v('name') ?>" required>
          </div>
          <div class="field">
            <label for="a-phone">Phone number</label>
            <input id="a-phone" name="phone" type="tel" autocomplete="tel" maxlength="40" value="<?= $v('phone') ?>" required>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-email">Email address</label>
            <input id="a-email" name="email" type="email" autocomplete="email" maxlength="190" value="<?= $v('email') ?>" required>
          </div>
          <div class="field">
            <label for="a-address">Where you live now</label>
            <input id="a-address" name="address" type="text" maxlength="160" placeholder="e.g. Lakeside, Pokhara" value="<?= $v('address') ?>">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-education">Highest qualification</label>
            <input id="a-education" name="education" type="text" maxlength="160" placeholder="e.g. BBA, Pokhara University" value="<?= $v('education') ?>">
          </div>
          <div class="field">
            <label for="a-start">When you could start</label>
            <input id="a-start" name="start" type="text" maxlength="90" placeholder="e.g. immediately, or 1 month" value="<?= $v('start') ?>">
          </div>
        </div>

        <div class="field">
          <label for="a-shift">Which shift can you work?</label>
          <select id="a-shift" name="shift">
            <?php foreach (['Either day or night', 'Night shift only', 'Day shift only'] as $o): ?>
              <option<?= ($old['shift'] ?? '') === $o ? ' selected' : '' ?>><?= e($o) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="a-experience">Work experience <span class="hint">optional if you are a fresh graduate</span></label>
          <textarea id="a-experience" name="experience" maxlength="3000" placeholder="Where you have worked, what you did, and for how long."><?= $v('experience') ?></textarea>
        </div>

        <div class="field">
          <label for="a-why">Why do you want this role?</label>
          <textarea id="a-why" name="why" maxlength="3000" required placeholder="A few honest sentences. We read these properly; there is no need for a formal cover letter."><?= $v('why') ?></textarea>
        </div>

        <div class="field">
          <label for="a-cv">Your CV</label>
          <input id="a-cv" name="cv" type="file" accept=".pdf,.doc,.docx,.odt,.rtf,.txt" required>
          <p class="hint" style="margin-top:6px">PDF, Word, ODT, RTF or text. Up to 5 MB.
             Please do not send certificates or citizenship documents at this stage.</p>
        </div>

        <button class="btn btn-primary" type="submit" style="width:100%">Send application</button>
        <p class="form-note"><?= rich((string) c('pages.jobs.privacy_note')) ?>
           See our <a href="privacy.php">privacy notice</a>.</p>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php
$others = array_values(array_filter(jobs_all(true), static fn($o) => ($o['slug'] ?? '') !== $slug));
if ($others): ?>
<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal><p class="eyebrow">Other roles</p><h2>Also hiring in Pokhara</h2></div>
    <div class="grid g-3 mt-l">
      <?php foreach (array_slice($others, 0, 3) as $o): ?>
        <article class="card svc" data-reveal>
          <p class="eyebrow"><?= e((string) ($o['dept'] ?? '')) ?></p>
          <h3><?= rich((string) ($o['title'] ?? '')) ?></h3>
          <p><?= rich((string) ($o['summary'] ?? '')) ?></p>
          <a class="arrow-link" href="job.php?j=<?= e(urlencode((string) $o['slug'])) ?>">View this role <?= KS_ARROW ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif;
ks_footer();
