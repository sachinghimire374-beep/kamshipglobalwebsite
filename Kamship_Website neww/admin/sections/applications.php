<?php
/** Job applications: read, mark, download the CV, export, delete. */
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

auth_require();

$apps = store_read('applications', []);

// ------------------------------------------------------------- download --
// Streams a stored CV. The file is named only by its id in the record, so a
// request can never reach outside the uploads folder.
if (isset($_GET['cv'])) {
    $id = (string) $_GET['cv'];
    foreach ($apps as $a) {
        if ((string) ($a['id'] ?? '') !== $id || empty($a['cv']['stored'])) {
            continue;
        }
        $stored = basename((string) $a['cv']['stored']);
        $path = UPLOAD_DIR . '/' . $stored;
        $real = realpath($path);
        $base = realpath(UPLOAD_DIR);
        if ($real === false || $base === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            http_response_code(404);
            exit('Not found');
        }
        $name = (string) ($a['cv']['original'] ?? 'cv');
        $safe = preg_replace('/[^\w .\-()]+/u', '_', $name) ?: 'cv';
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($real));
        header('Content-Disposition: attachment; filename="' . $safe . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($real);
        exit;
    }
    http_response_code(404);
    exit('Not found');
}

// --------------------------------------------------------------- export --
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="kamship-applications-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads Nepali names correctly
    fputcsv($out, ['Received', 'Role', 'Name', 'Email', 'Phone', 'Lives in', 'Education',
                   'Shift', 'Can start', 'Experience', 'Why', 'Status', 'CV file']);
    foreach ($apps as $a) {
        fputcsv($out, [
            date('Y-m-d H:i', (int) strtotime((string) ($a['received'] ?? ''))),
            $a['job_title'] ?? '', $a['name'] ?? '', $a['email'] ?? '', $a['phone'] ?? '',
            $a['address'] ?? '', $a['education'] ?? '', $a['shift'] ?? '', $a['start'] ?? '',
            $a['experience'] ?? '', $a['why'] ?? '', $a['status'] ?? 'new',
            $a['cv']['original'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

// -------------------------------------------------------------- actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $id = (string) ($_POST['id'] ?? '');

    if ($action === 'status') {
        $new = (string) ($_POST['status'] ?? 'new');
        if (!in_array($new, ['new', 'reviewing', 'shortlisted', 'interviewed', 'hired', 'rejected'], true)) {
            $new = 'new';
        }
        foreach ($apps as $i => $a) {
            if ((string) ($a['id'] ?? '') === $id) {
                $apps[$i]['status'] = $new;
                break;
            }
        }
        store_write('applications', $apps);
        flash('Status updated.');
        redirect('index.php?p=applications');
    }

    if ($action === 'delete') {
        foreach ($apps as $i => $a) {
            if ((string) ($a['id'] ?? '') === $id) {
                if (!empty($a['cv']['stored'])) {
                    @unlink(UPLOAD_DIR . '/' . basename((string) $a['cv']['stored']));
                }
                array_splice($apps, $i, 1);
                break;
            }
        }
        store_write('applications', $apps);
        flash('Application deleted, along with the CV file.');
        redirect('index.php?p=applications');
    }
}

// ---------------------------------------------------------------- view ---
$fRole = (string) ($_GET['role'] ?? '');
$fStatus = (string) ($_GET['status'] ?? '');
$roles = array_values(array_unique(array_map(static fn($a) => (string) ($a['job_title'] ?? ''), $apps)));
sort($roles);

$shown = array_values(array_filter($apps, static function ($a) use ($fRole, $fStatus) {
    if ($fRole !== '' && (string) ($a['job_title'] ?? '') !== $fRole) {
        return false;
    }
    if ($fStatus !== '' && (string) ($a['status'] ?? 'new') !== $fStatus) {
        return false;
    }
    return true;
}));

$STATUSES = ['new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted',
             'interviewed' => 'Interviewed', 'hired' => 'Hired', 'rejected' => 'Not selected'];

admin_shell_open('applications', 'Job applications',
    'Everyone who has applied through the jobs page. CVs download only for signed-in staff.');
?>

<?php if (!$apps): ?>
  <div class="apanel">
    <p class="aempty">No applications yet. They arrive here as soon as someone applies through
      <a href="../jobs.php" target="_blank" rel="noopener">the jobs page</a>.</p>
  </div>
<?php else: ?>

  <form class="afilter" method="get" action="index.php">
    <input type="hidden" name="p" value="applications">
    <label class="vh" for="fr">Role</label>
    <select id="fr" name="role">
      <option value="">All roles</option>
      <?php foreach ($roles as $r): ?>
        <option value="<?= e($r) ?>"<?= $fRole === $r ? ' selected' : '' ?>><?= e($r) ?></option>
      <?php endforeach; ?>
    </select>
    <label class="vh" for="fs">Status</label>
    <select id="fs" name="status">
      <option value="">Any status</option>
      <?php foreach ($STATUSES as $k => $v): ?>
        <option value="<?= e($k) ?>"<?= $fStatus === $k ? ' selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
    <?php if ($fRole !== '' || $fStatus !== ''): ?>
      <a class="btn btn-ghost btn-sm" href="index.php?p=applications">Clear</a>
    <?php endif; ?>
    <a class="btn btn-ghost btn-sm" href="index.php?p=applications&amp;export=1">Download all as CSV</a>
    <span class="acount"><?= count($shown) ?> of <?= count($apps) ?></span>
  </form>

  <?php foreach ($shown as $a): ?>
    <?php $st = (string) ($a['status'] ?? 'new'); ?>
    <article class="apanel amsg<?= $st === 'new' ? ' isnew' : '' ?>" id="a-<?= e((string) $a['id']) ?>">
      <header class="amsg-head">
        <div>
          <h2><?= e((string) ($a['name'] ?? '')) ?></h2>
          <p class="amsg-meta">
            <?= e((string) ($a['job_title'] ?? '')) ?>
            &middot; <?= e(date('j M Y, H:i', (int) strtotime((string) ($a['received'] ?? '')))) ?>
            <?php if (trim((string) ($a['address'] ?? '')) !== ''): ?> &middot; <?= e((string) $a['address']) ?><?php endif; ?>
          </p>
        </div>
        <span class="apill apill-<?= e($st) ?>"><?= e($STATUSES[$st] ?? $st) ?></span>
      </header>

      <dl class="amsg-facts">
        <dt>Email</dt><dd><a href="mailto:<?= e((string) $a['email']) ?>"><?= e((string) $a['email']) ?></a></dd>
        <dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', (string) $a['phone']) ?? '') ?>"><?= e((string) $a['phone']) ?></a></dd>
        <?php if (trim((string) ($a['education'] ?? '')) !== ''): ?><dt>Education</dt><dd><?= e((string) $a['education']) ?></dd><?php endif; ?>
        <?php if (trim((string) ($a['shift'] ?? '')) !== ''): ?><dt>Shift</dt><dd><?= e((string) $a['shift']) ?></dd><?php endif; ?>
        <?php if (trim((string) ($a['start'] ?? '')) !== ''): ?><dt>Can start</dt><dd><?= e((string) $a['start']) ?></dd><?php endif; ?>
      </dl>

      <?php if (trim((string) ($a['experience'] ?? '')) !== ''): ?>
        <h3>Experience</h3><p class="amsg-body"><?= nl2br(e((string) $a['experience'])) ?></p>
      <?php endif; ?>
      <?php if (trim((string) ($a['why'] ?? '')) !== ''): ?>
        <h3>Why this role</h3><p class="amsg-body"><?= nl2br(e((string) $a['why'])) ?></p>
      <?php endif; ?>

      <footer class="amsg-foot">
        <?php if (!empty($a['cv']['stored'])): ?>
          <a class="btn btn-primary btn-sm" href="index.php?p=applications&amp;cv=<?= e(urlencode((string) $a['id'])) ?>">
            Download CV (<?= e((string) ($a['cv']['original'] ?? 'file')) ?>)
          </a>
        <?php else: ?>
          <span class="aempty">No CV attached</span>
        <?php endif; ?>

        <form method="post" action="index.php?p=applications" class="ainline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
          <input type="hidden" name="action" value="status">
          <label class="vh" for="s-<?= e((string) $a['id']) ?>">Status</label>
          <select id="s-<?= e((string) $a['id']) ?>" name="status">
            <?php foreach ($STATUSES as $k => $v): ?>
              <option value="<?= e($k) ?>"<?= $st === $k ? ' selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-ghost btn-sm" type="submit">Update</button>
        </form>

        <a class="btn btn-ghost btn-sm" href="mailto:<?= e((string) $a['email']) ?>?subject=<?= e(rawurlencode('Your application: ' . (string) ($a['job_title'] ?? ''))) ?>">Reply by email</a>

        <form method="post" action="index.php?p=applications" class="ainline"
              data-confirm="Delete this application and its CV? This cannot be undone.">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
          <input type="hidden" name="action" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">Delete</button>
        </form>
      </footer>
    </article>
  <?php endforeach; ?>

  <?php if (!$shown): ?>
    <div class="apanel"><p class="aempty">Nothing matches that filter.</p></div>
  <?php endif; ?>
<?php endif;

admin_shell_close();
