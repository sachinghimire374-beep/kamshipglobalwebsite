<?php
/** Job listings: create, edit, reorder, open/close and delete. */
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

auth_require();

$data = content();
$jobs = is_array($data['jobs'] ?? null) ? $data['jobs'] : [];
$editIdx = null;

// --------------------------------------------------------------- actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $idx = isset($_POST['idx']) ? (int) $_POST['idx'] : -1;
    $exists = $idx >= 0 && isset($jobs[$idx]);

    if ($action === 'save') {
        $slug = slugify(post_str('slug', 80) ?: post_str('title', 120));
        // Keep slugs unique so two roles never share a URL.
        $taken = [];
        foreach ($jobs as $i => $j) {
            if ($i !== $idx) {
                $taken[] = (string) ($j['slug'] ?? '');
            }
        }
        $base = $slug;
        $n = 2;
        while (in_array($slug, $taken, true)) {
            $slug = $base . '-' . $n++;
        }

        $closing = post_str('closing', 20);
        if ($closing !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $closing)) {
            $closing = '';
        }

        $job = [
            'slug'       => $slug,
            'title'      => post_str('title', 140),
            'dept'       => post_str('dept', 80),
            'location'   => post_str('location', 120),
            'type'       => post_str('type', 60),
            'shift'      => post_str('shift', 120),
            'openings'   => post_str('openings', 10),
            'closing'    => $closing,
            'salary'     => post_str('salary', 160),
            'experience' => post_str('experience', 160),
            'status'     => post_str('status', 20) === 'closed' ? 'closed' : 'open',
            'featured'   => post_bool('featured'),
            'summary'    => post_str('summary', 500),
            'about'      => post_str('about', 4000),
            'responsibilities' => post_lines('responsibilities'),
            'requirements'     => post_lines('requirements'),
            'nice'             => post_lines('nice'),
            'benefits'         => post_lines('benefits'),
            'posted'     => $exists ? (string) ($jobs[$idx]['posted'] ?? date('Y-m-d')) : date('Y-m-d'),
        ];

        if ($job['title'] === '') {
            flash('A job needs a title before it can be saved.', 'bad');
        } else {
            if ($exists) {
                $jobs[$idx] = $job;
                flash('Saved "' . strip_tags($job['title']) . '".');
            } else {
                $jobs[] = $job;
                flash('Added "' . strip_tags($job['title']) . '". It is now live on the jobs page.');
            }
            $data['jobs'] = array_values($jobs);
            if (!content_save($data)) {
                flash('Could not write the change. Check that data/ is writable by the web server.', 'bad');
            }
        }
        redirect('index.php?p=jobs');
    }

    if ($action === 'toggle' && $exists) {
        $jobs[$idx]['status'] = ($jobs[$idx]['status'] ?? 'open') === 'open' ? 'closed' : 'open';
        $data['jobs'] = array_values($jobs);
        content_save($data);
        flash($jobs[$idx]['status'] === 'open'
            ? 'That role is open again and back on the website.'
            : 'That role is closed and no longer listed on the website.');
        redirect('index.php?p=jobs');
    }

    if ($action === 'move' && $exists) {
        $dir = (string) ($_POST['dir'] ?? '');
        $to = $dir === 'up' ? $idx - 1 : $idx + 1;
        if (isset($jobs[$to])) {
            [$jobs[$idx], $jobs[$to]] = [$jobs[$to], $jobs[$idx]];
            $data['jobs'] = array_values($jobs);
            content_save($data);
        }
        redirect('index.php?p=jobs');
    }

    if ($action === 'delete' && $exists) {
        $title = strip_tags((string) ($jobs[$idx]['title'] ?? 'that role'));
        array_splice($jobs, $idx, 1);
        $data['jobs'] = array_values($jobs);
        content_save($data);
        flash('Deleted "' . $title . '". Applications already received are kept.');
        redirect('index.php?p=jobs');
    }
}

// ----------------------------------------------------------------- view ---
if (isset($_GET['edit'])) {
    $editIdx = (int) $_GET['edit'];
    if (!isset($jobs[$editIdx])) {
        $editIdx = null;
    }
}
$adding = isset($_GET['new']);

admin_shell_open(
    'jobs',
    'Jobs',
    'Roles listed here appear on the careers and jobs pages straight away. Closing a role hides it without losing the applications.'
);

$blank = [
    'slug' => '', 'title' => '', 'dept' => 'Operations', 'location' => 'Pokhara (on-site)',
    'type' => 'Full-time', 'shift' => '', 'openings' => '1', 'closing' => '',
    'salary' => '', 'experience' => '', 'status' => 'open', 'featured' => false,
    'summary' => '', 'about' => '', 'responsibilities' => [], 'requirements' => [],
    'nice' => [], 'benefits' => [],
];

if ($adding || $editIdx !== null):
    $j = $editIdx !== null ? array_merge($blank, $jobs[$editIdx]) : $blank;
    ?>
    <form method="post" action="index.php?p=jobs" class="apanel">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <?php if ($editIdx !== null): ?><input type="hidden" name="idx" value="<?= (int) $editIdx ?>"><?php endif; ?>

      <h2><?= $editIdx !== null ? 'Edit role' : 'Add a role' ?></h2>

      <div class="arow">
        <?php fld('title', 'Job title', (string) $j['title'], 'text', ['max' => 140, 'wide' => true]); ?>
        <?php fld('dept', 'Team', (string) $j['dept'], 'text', ['max' => 80, 'hint' => 'e.g. Operations, Quality, People']); ?>
      </div>

      <?php fld('summary', 'One-line summary', (string) $j['summary'], 'textarea',
          ['rows' => 2, 'wide' => true, 'hint' => 'Shown on the jobs list. One or two sentences.']); ?>

      <div class="arow">
        <?php fld('shift', 'Shift', (string) $j['shift'], 'text',
            ['max' => 120, 'placeholder' => 'e.g. Night shift (US desk)', 'hint' => 'Include the word "night" for night roles so the filter works']); ?>
        <?php fld('location', 'Location', (string) $j['location'], 'text', ['max' => 120]); ?>
      </div>

      <div class="arow">
        <?php fld('type', 'Contract type', (string) $j['type'], 'text', ['max' => 60, 'placeholder' => 'Full-time']); ?>
        <?php fld('openings', 'Number of positions', (string) $j['openings'], 'text', ['max' => 10]); ?>
        <?php fld('closing', 'Applications close', (string) $j['closing'], 'date',
            ['hint' => 'Leave empty for no closing date']); ?>
      </div>

      <div class="arow">
        <?php fld('salary', 'Pay', (string) $j['salary'], 'text',
            ['max' => 160, 'wide' => true, 'placeholder' => 'NPR 35,000 – 45,000 per month, plus night allowance']); ?>
        <?php fld('experience', 'Experience needed', (string) $j['experience'], 'text',
            ['max' => 160, 'placeholder' => 'No experience required']); ?>
      </div>

      <?php fld('about', 'About this role', (string) $j['about'], 'textarea',
          ['rows' => 5, 'wide' => true, 'hint' => 'A paragraph on what the job is really like.']); ?>

      <?php fld_lines('responsibilities', 'What you will do', (array) $j['responsibilities']); ?>
      <?php fld_lines('requirements', 'What we are looking for', (array) $j['requirements']); ?>
      <?php fld_lines('nice', 'Helpful, but not required', (array) $j['nice']); ?>
      <?php fld_lines('benefits', 'What you get', (array) $j['benefits']); ?>

      <div class="arow">
        <?php fld('status', 'Status', (string) $j['status'], 'select',
            ['options' => ['open' => 'Open — shown on the website', 'closed' => 'Closed — hidden']]); ?>
        <?php fld('featured', 'Highlight', !empty($j['featured']) ? '1' : '', 'checkbox',
            ['checkLabel' => 'Show a "Hiring now" badge and list this role first']); ?>
        <?php fld('slug', 'Web address', (string) $j['slug'], 'text',
            ['max' => 80, 'hint' => 'Left empty, this is made from the title']); ?>
      </div>

      <div class="aactions">
        <button class="btn btn-primary" type="submit">Save role</button>
        <a class="btn btn-ghost" href="index.php?p=jobs">Cancel</a>
      </div>
    </form>

<?php else: ?>

  <div class="aactions" style="margin-bottom:22px">
    <a class="btn btn-primary" href="index.php?p=jobs&amp;new=1">Add a role</a>
    <a class="btn btn-ghost" href="../jobs.php" target="_blank" rel="noopener">View the jobs page &#8599;</a>
  </div>

  <?php if (!$jobs): ?>
    <div class="apanel"><p class="aempty">No roles yet. Add one and it appears on the website immediately.</p></div>
  <?php else: ?>
    <div class="apanel">
      <table class="atable atable-rows">
        <thead>
          <tr><th>Role</th><th>Team</th><th>Shift</th><th>Closes</th><th>Status</th><th class="aright">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($jobs as $i => $j): ?>
            <?php $isOpen = ($j['status'] ?? 'open') === 'open'; ?>
            <tr>
              <td>
                <b><?= e(strip_tags((string) ($j['title'] ?? ''))) ?></b>
                <?php if (!empty($j['featured'])): ?><span class="atag">Highlighted</span><?php endif; ?>
                <?php if ($isOpen && job_closed($j)): ?><span class="atag atag-warn">Past closing date</span><?php endif; ?>
                <span class="asub2"><?= e((string) ($j['openings'] ?? '')) ?> <?= (int) ($j['openings'] ?? 0) === 1 ? 'position' : 'positions' ?></span>
              </td>
              <td><?= e((string) ($j['dept'] ?? '')) ?></td>
              <td><?= e((string) ($j['shift'] ?? '')) ?></td>
              <td class="anowrap"><?= $j['closing'] ? e(date('j M Y', (int) strtotime((string) $j['closing']))) : '—' ?></td>
              <td><span class="apill <?= $isOpen ? 'apill-on' : 'apill-off' ?>"><?= $isOpen ? 'Open' : 'Closed' ?></span></td>
              <td class="aright anowrap">
                <a class="btn btn-ghost btn-sm" href="index.php?p=jobs&amp;edit=<?= (int) $i ?>">Edit</a>
                <form method="post" action="index.php?p=jobs" class="ainline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="toggle">
                  <button class="btn btn-ghost btn-sm" type="submit"><?= $isOpen ? 'Close' : 'Reopen' ?></button>
                </form>
                <form method="post" action="index.php?p=jobs" class="ainline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="move">
                  <input type="hidden" name="dir" value="up">
                  <button class="btn btn-ghost btn-sm" type="submit" title="Move up"<?= $i === 0 ? ' disabled' : '' ?>>&uarr;</button>
                </form>
                <form method="post" action="index.php?p=jobs" class="ainline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="move">
                  <input type="hidden" name="dir" value="down">
                  <button class="btn btn-ghost btn-sm" type="submit" title="Move down"<?= $i === count($jobs) - 1 ? ' disabled' : '' ?>>&darr;</button>
                </form>
                <form method="post" action="index.php?p=jobs" class="ainline"
                      data-confirm="Delete this role? Applications already received are kept.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="delete">
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <section class="apanel">
    <h2>Jobs page wording</h2>
    <form method="post" action="index.php?p=pages">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="page" value="jobs">
      <?php fld('h1', 'Page heading', (string) c('pages.jobs.h1'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld('lead', 'Introduction', (string) c('pages.jobs.lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld('empty', 'Shown when no roles are open', (string) c('pages.jobs.empty'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld('apply_intro', 'Text above the application form', (string) c('pages.jobs.apply_intro'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
      <?php fld('privacy_note', 'Privacy note under the form', (string) c('pages.jobs.privacy_note'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <div class="aactions"><button class="btn btn-primary" type="submit">Save wording</button></div>
    </form>
  </section>

<?php endif;
admin_shell_close();
