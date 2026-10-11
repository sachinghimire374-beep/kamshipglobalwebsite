<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$data = content();
$items = is_array($data['industries'] ?? null) ? $data['industries'] : [];
$ICONS = ['truck','ship','plane','stamp','box','chart','globe','layers','users','briefcase','check'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $idx = isset($_POST['idx']) ? (int) $_POST['idx'] : -1;
    if ($action === 'save') {
        $item = [
            'name' => post_str('name', 140),
            'icon' => in_array(post_str('icon', 30), $ICONS, true) ? post_str('icon', 30) : 'check',
            'text' => post_str('text', 700),
            'tags' => post_lines('tags'),
        ];
        if ($item['name'] === '') { flash('This needs a name.', 'bad'); }
        else {
            if ($idx >= 0 && isset($items[$idx])) { $items[$idx] = $item; } else { $items[] = $item; }
            $data['industries'] = array_values($items);
            flash(content_save($data) ? 'Saved.' : 'Could not save.', 'ok');
        }
        redirect('index.php?p=industries');
    }
    if ($action === 'delete' && isset($items[$idx])) {
        array_splice($items, $idx, 1);
        $data['industries'] = array_values($items);
        content_save($data);
        flash('Deleted.');
        redirect('index.php?p=industries');
    }
    if ($action === 'systems') {
        $data['systems'] = post_lines('systems');
        flash(content_save($data) ? 'Systems list saved.' : 'Could not save.', 'ok');
        redirect('index.php?p=industries');
    }
}

$editIdx = isset($_GET['edit']) && isset($items[(int) $_GET['edit']]) ? (int) $_GET['edit'] : null;
$adding = isset($_GET['new']);

admin_shell_open('industries', 'Industries', 'The sectors shown on the home page and the industries page.');

if ($adding || $editIdx !== null):
  $it = $editIdx !== null ? $items[$editIdx] : ['name'=>'','icon'=>'check','text'=>'','tags'=>[]];
?>
  <form method="post" action="index.php?p=industries" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editIdx !== null): ?><input type="hidden" name="idx" value="<?= (int) $editIdx ?>"><?php endif; ?>
    <h2><?= $editIdx !== null ? 'Edit sector' : 'Add a sector' ?></h2>
    <div class="arow">
      <?php fld('name', 'Sector name', (string) ($it['name'] ?? ''), 'text', ['max' => 140, 'wide' => true]); ?>
      <?php fld('icon', 'Icon', (string) ($it['icon'] ?? 'check'), 'select', ['options' => array_combine($ICONS, $ICONS)]); ?>
    </div>
    <?php fld('text', 'Description', (string) ($it['text'] ?? ''), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld_lines('tags', 'Tags', (array) ($it['tags'] ?? [])); ?>
    <div class="aactions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn btn-ghost" href="index.php?p=industries">Cancel</a>
    </div>
  </form>
<?php else: ?>
  <div class="aactions" style="margin-bottom:22px">
    <a class="btn btn-primary" href="index.php?p=industries&amp;new=1">Add a sector</a>
    <a class="btn btn-ghost" href="../industries.php" target="_blank" rel="noopener">View the page &#8599;</a>
  </div>
  <div class="apanel">
    <table class="atable atable-rows">
      <thead><tr><th>Sector</th><th class="aright">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($items as $i => $it): ?>
          <tr>
            <td><b><?= e(strip_tags((string) ($it['name'] ?? ''))) ?></b>
              <span class="asub2"><?= e(mb_substr(strip_tags((string) ($it['text'] ?? '')), 0, 100)) ?>…</span></td>
            <td class="aright anowrap">
              <a class="btn btn-ghost btn-sm" href="index.php?p=industries&amp;edit=<?= (int) $i ?>">Edit</a>
              <form method="post" action="index.php?p=industries" class="ainline" data-confirm="Delete this sector?">
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

  <form method="post" action="index.php?p=industries" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="systems">
    <h2>Systems strip</h2>
    <p class="aempty" style="margin-bottom:18px">The scrolling list of software names shown on the home page and services page.</p>
    <?php fld_lines('systems', 'Systems', clist('systems')); ?>
    <div class="aactions"><button class="btn btn-primary" type="submit">Save systems list</button></div>
  </form>
<?php endif;
admin_shell_close();
