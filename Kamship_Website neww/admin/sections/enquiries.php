<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$all = store_read('enquiries', []);

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="kamship-enquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Received', 'Name', 'Company', 'Email', 'Phone', 'Country', 'Seats', 'Desk', 'Message', 'Status']);
    foreach ($all as $x) {
        fputcsv($out, [date('Y-m-d H:i', (int) strtotime((string) ($x['received'] ?? ''))),
            $x['name'] ?? '', $x['company'] ?? '', $x['email'] ?? '', $x['phone'] ?? '',
            $x['country'] ?? '', $x['seats'] ?? '', $x['service'] ?? '', $x['message'] ?? '', $x['status'] ?? 'new']);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $id = (string) ($_POST['id'] ?? '');
    if ($action === 'status') {
        $new = in_array((string) ($_POST['status'] ?? ''), ['new', 'replied', 'archived'], true)
            ? (string) $_POST['status'] : 'new';
        foreach ($all as $i => $x) {
            if ((string) ($x['id'] ?? '') === $id) { $all[$i]['status'] = $new; break; }
        }
        store_write('enquiries', $all);
        flash('Status updated.');
        redirect('index.php?p=enquiries');
    }
    if ($action === 'delete') {
        foreach ($all as $i => $x) {
            if ((string) ($x['id'] ?? '') === $id) { array_splice($all, $i, 1); break; }
        }
        store_write('enquiries', $all);
        flash('Enquiry deleted.');
        redirect('index.php?p=enquiries');
    }
}

$ST = ['new' => 'New', 'replied' => 'Replied', 'archived' => 'Archived'];
$f = (string) ($_GET['status'] ?? '');
$shown = $f === '' ? $all : array_values(array_filter($all, static fn($x) => ($x['status'] ?? 'new') === $f));

admin_shell_open('enquiries', 'Enquiries', 'Messages sent through the contact form on the website.');
?>
<?php if (!$all): ?>
  <div class="apanel"><p class="aempty">No enquiries yet. They arrive here as soon as someone uses the contact form.</p></div>
<?php else: ?>
  <form class="afilter" method="get" action="index.php">
    <input type="hidden" name="p" value="enquiries">
    <label class="vh" for="fs">Status</label>
    <select id="fs" name="status">
      <option value="">All</option>
      <?php foreach ($ST as $k => $v): ?><option value="<?= e($k) ?>"<?= $f === $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
    <a class="btn btn-ghost btn-sm" href="index.php?p=enquiries&amp;export=1">Download all as CSV</a>
    <span class="acount"><?= count($shown) ?> of <?= count($all) ?></span>
  </form>

  <?php foreach ($shown as $x): $st = (string) ($x['status'] ?? 'new'); ?>
    <article class="apanel amsg<?= $st === 'new' ? ' isnew' : '' ?>" id="e-<?= e((string) $x['id']) ?>">
      <header class="amsg-head">
        <div>
          <h2><?= e((string) ($x['name'] ?? '')) ?><?= trim((string) ($x['company'] ?? '')) !== '' ? ' — ' . e((string) $x['company']) : '' ?></h2>
          <p class="amsg-meta"><?= e(date('j M Y, H:i', (int) strtotime((string) ($x['received'] ?? '')))) ?>
            <?php if (trim((string) ($x['country'] ?? '')) !== ''): ?> &middot; <?= e((string) $x['country']) ?><?php endif; ?></p>
        </div>
        <span class="apill apill-<?= e($st) ?>"><?= e($ST[$st] ?? $st) ?></span>
      </header>
      <dl class="amsg-facts">
        <dt>Email</dt><dd><a href="mailto:<?= e((string) $x['email']) ?>"><?= e((string) $x['email']) ?></a></dd>
        <?php if (trim((string) ($x['phone'] ?? '')) !== ''): ?><dt>Phone</dt><dd><?= e((string) $x['phone']) ?></dd><?php endif; ?>
        <?php if (trim((string) ($x['seats'] ?? '')) !== ''): ?><dt>Seats</dt><dd><?= e((string) $x['seats']) ?></dd><?php endif; ?>
        <?php if (trim((string) ($x['service'] ?? '')) !== ''): ?><dt>Desk</dt><dd><?= e((string) $x['service']) ?></dd><?php endif; ?>
      </dl>
      <?php if (trim((string) ($x['message'] ?? '')) !== ''): ?>
        <h3>Message</h3><p class="amsg-body"><?= nl2br(e((string) $x['message'])) ?></p>
      <?php endif; ?>
      <footer class="amsg-foot">
        <a class="btn btn-primary btn-sm" href="mailto:<?= e((string) $x['email']) ?>?subject=<?= e(rawurlencode('Re: your enquiry to Kamship Global')) ?>">Reply by email</a>
        <form method="post" action="index.php?p=enquiries" class="ainline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $x['id']) ?>">
          <input type="hidden" name="action" value="status">
          <label class="vh" for="es-<?= e((string) $x['id']) ?>">Status</label>
          <select id="es-<?= e((string) $x['id']) ?>" name="status">
            <?php foreach ($ST as $k => $v): ?><option value="<?= e($k) ?>"<?= $st === $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-ghost btn-sm" type="submit">Update</button>
        </form>
        <form method="post" action="index.php?p=enquiries" class="ainline" data-confirm="Delete this enquiry permanently?">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $x['id']) ?>">
          <input type="hidden" name="action" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">Delete</button>
        </form>
      </footer>
    </article>
  <?php endforeach; ?>
  <?php if (!$shown): ?><div class="apanel"><p class="aempty">Nothing with that status.</p></div><?php endif; ?>
<?php endif;
admin_shell_close();
