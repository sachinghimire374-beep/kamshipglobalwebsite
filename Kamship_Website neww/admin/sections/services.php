<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$data = content();
$items = is_array($data['services'] ?? null) ? $data['services'] : [];
$ICONS = ['shield','radar','receipt','box','file','stamp','calc','headset','users','clock',
          'message','globe','chart','lock','truck','ship','plane','search','check','layers','cycle','spark','briefcase'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $idx = isset($_POST['idx']) ? (int) $_POST['idx'] : -1;
    $exists = $idx >= 0 && isset($items[$idx]);

    if ($action === 'save') {
        $slug = slugify(post_str('slug', 80) ?: post_str('title', 120));
        $taken = [];
        foreach ($items as $i => $s) { if ($i !== $idx) { $taken[] = (string) ($s['slug'] ?? ''); } }
        $base = $slug; $n = 2;
        while (in_array($slug, $taken, true)) { $slug = $base . '-' . $n++; }

        $svc = [
            'slug' => $slug,
            'icon' => in_array(post_str('icon', 30), $ICONS, true) ? post_str('icon', 30) : 'check',
            'nav' => post_str('nav', 80),
            'navdesc' => post_str('navdesc', 120),
            'title' => post_str('title', 140),
            'blurb' => post_str('blurb', 600),
            'lead' => post_str('lead', 600),
            'tags' => post_lines('tags'),
            'what' => post_pairs('what'),
            'targets' => post_pairs('targets'),
            'tools' => post_str('tools', 600),
            'note' => post_str('note', 900),
        ];
        if ($svc['title'] === '') {
            flash('A service needs a title.', 'bad');
        } else {
            if ($svc['nav'] === '') { $svc['nav'] = $svc['title']; }
            if ($exists) { $items[$idx] = $svc; } else { $items[] = $svc; }
            $data['services'] = array_values($items);
            flash(content_save($data) ? 'Saved.' : 'Could not save. Check that data/ is writable.', 'ok');
        }
        redirect('index.php?p=services');
    }
    if ($action === 'move' && $exists) {
        $to = ((string) ($_POST['dir'] ?? '')) === 'up' ? $idx - 1 : $idx + 1;
        if (isset($items[$to])) {
            [$items[$idx], $items[$to]] = [$items[$to], $items[$idx]];
            $data['services'] = array_values($items);
            content_save($data);
        }
        redirect('index.php?p=services');
    }
    if ($action === 'delete' && $exists) {
        $t = strip_tags((string) ($items[$idx]['title'] ?? ''));
        array_splice($items, $idx, 1);
        $data['services'] = array_values($items);
        content_save($data);
        flash('Deleted "' . $t . '".');
        redirect('index.php?p=services');
    }
}

$editIdx = isset($_GET['edit']) && isset($items[(int) $_GET['edit']]) ? (int) $_GET['edit'] : null;
$adding = isset($_GET['new']);

admin_shell_open('services', 'Services', 'The eight desks. Each one has its own page and appears on the home page and in the menu.');

$blank = ['slug'=>'','icon'=>'check','nav'=>'','navdesc'=>'','title'=>'','blurb'=>'','lead'=>'',
          'tags'=>[],'what'=>[],'targets'=>[],'tools'=>'','note'=>''];

if ($adding || $editIdx !== null):
  $s = $editIdx !== null ? array_merge($blank, $items[$editIdx]) : $blank;
?>
  <form method="post" action="index.php?p=services" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editIdx !== null): ?><input type="hidden" name="idx" value="<?= (int) $editIdx ?>"><?php endif; ?>
    <h2><?= $editIdx !== null ? 'Edit service' : 'Add a service' ?></h2>

    <div class="arow">
      <?php fld('title', 'Service title', (string) $s['title'], 'text', ['max' => 140, 'wide' => true]); ?>
      <?php fld('icon', 'Icon', (string) $s['icon'], 'select', ['options' => array_combine($ICONS, $ICONS)]); ?>
    </div>
    <div class="arow">
      <?php fld('nav', 'Short name for the menu', (string) $s['nav'], 'text', ['max' => 80]); ?>
      <?php fld('navdesc', 'One-line menu description', (string) $s['navdesc'], 'text', ['max' => 120, 'wide' => true]); ?>
    </div>
    <?php fld('blurb', 'Summary', (string) $s['blurb'], 'textarea', ['rows' => 3, 'wide' => true, 'hint' => 'Shown on the home page and the services list']); ?>
    <?php fld('lead', 'Opening line on its own page', (string) $s['lead'], 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld_lines('tags', 'Tags', (array) $s['tags'], 'One per line. Shown as small pills.'); ?>
    <?php fld_pairs('what', 'What this desk handles', (array) $s['what'], 'One per line: Heading | description'); ?>
    <?php fld_pairs('targets', 'What we commit to', (array) $s['targets'], 'One per line: Commitment | target'); ?>
    <?php fld('tools', 'Systems trained on', (string) $s['tools'], 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <?php fld('note', 'Small print', (string) $s['note'], 'textarea', ['rows' => 3, 'wide' => true, 'hint' => 'Optional disclaimer shown under the desk description']); ?>
    <?php fld('slug', 'Web address', (string) $s['slug'], 'text', ['max' => 80, 'hint' => 'Left empty, made from the title']); ?>

    <div class="aactions">
      <button class="btn btn-primary" type="submit">Save service</button>
      <a class="btn btn-ghost" href="index.php?p=services">Cancel</a>
    </div>
  </form>
<?php else: ?>
  <div class="aactions" style="margin-bottom:22px">
    <a class="btn btn-primary" href="index.php?p=services&amp;new=1">Add a service</a>
    <a class="btn btn-ghost" href="../services.php" target="_blank" rel="noopener">View the services page &#8599;</a>
  </div>
  <div class="apanel">
    <table class="atable atable-rows">
      <thead><tr><th>Service</th><th>Menu name</th><th class="aright">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($items as $i => $s): ?>
          <tr>
            <td><b><?= e(strip_tags((string) ($s['title'] ?? ''))) ?></b>
                <span class="asub2"><?= e(mb_substr(strip_tags((string) ($s['blurb'] ?? '')), 0, 90)) ?>…</span></td>
            <td><?= e(strip_tags((string) ($s['nav'] ?? ''))) ?></td>
            <td class="aright anowrap">
              <a class="btn btn-ghost btn-sm" href="index.php?p=services&amp;edit=<?= (int) $i ?>">Edit</a>
              <a class="btn btn-ghost btn-sm" href="../service.php?s=<?= e(urlencode((string) $s['slug'])) ?>" target="_blank" rel="noopener">View</a>
              <form method="post" action="index.php?p=services" class="ainline">
                <?= csrf_field() ?><input type="hidden" name="idx" value="<?= (int) $i ?>">
                <input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="up">
                <button class="btn btn-ghost btn-sm" type="submit"<?= $i === 0 ? ' disabled' : '' ?>>&uarr;</button>
              </form>
              <form method="post" action="index.php?p=services" class="ainline">
                <?= csrf_field() ?><input type="hidden" name="idx" value="<?= (int) $i ?>">
                <input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="down">
                <button class="btn btn-ghost btn-sm" type="submit"<?= $i === count($items) - 1 ? ' disabled' : '' ?>>&darr;</button>
              </form>
              <form method="post" action="index.php?p=services" class="ainline" data-confirm="Delete this service and its page?">
                <?= csrf_field() ?><input type="hidden" name="idx" value="<?= (int) $i ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif;
admin_shell_close();
