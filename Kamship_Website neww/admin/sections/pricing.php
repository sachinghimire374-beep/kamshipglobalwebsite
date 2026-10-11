<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$data = content();
$p = is_array($data['pricing'] ?? null) ? $data['pricing'] : [];
$tiers = is_array($p['tiers'] ?? null) ? $p['tiers'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $idx = isset($_POST['idx']) ? (int) $_POST['idx'] : -1;

    if ($action === 'savepage') {
        foreach (['h1' => 200, 'lead' => 700, 'note' => 1500, 'included_h2' => 200,
                  'included_lead' => 700, 'compare_title' => 160, 'compare_note' => 900] as $k => $max) {
            $p[$k] = post_str($k, $max);
        }
        $p['included'] = post_lines('included');
        $p['compare_rows'] = post_pairs('compare_rows');
        $data['pricing'] = $p;
        flash(content_save($data) ? 'Pricing page saved.' : 'Could not save.', 'ok');
        redirect('index.php?p=pricing');
    }

    if ($action === 'savetier') {
        $tier = [
            'name' => post_str('name', 120),
            'day' => post_str('day', 20),
            'night' => post_str('night', 20),
            'monthly' => post_str('monthly', 120),
            'blurb' => post_str('blurb', 500),
            'features' => post_lines('features'),
            'featured' => post_bool('featured'),
        ];
        if ($tier['name'] === '') {
            flash('A tier needs a name.', 'bad');
        } else {
            if ($idx >= 0 && isset($tiers[$idx])) { $tiers[$idx] = $tier; } else { $tiers[] = $tier; }
            // Only one tier can be highlighted.
            if ($tier['featured']) {
                foreach ($tiers as $i => $t) {
                    $tiers[$i]['featured'] = ($i === ($idx >= 0 && isset($tiers[$idx]) ? $idx : count($tiers) - 1));
                }
            }
            $p['tiers'] = array_values($tiers);
            $data['pricing'] = $p;
            flash(content_save($data) ? 'Tier saved.' : 'Could not save.', 'ok');
        }
        redirect('index.php?p=pricing');
    }

    if ($action === 'deletetier' && isset($tiers[$idx])) {
        array_splice($tiers, $idx, 1);
        $p['tiers'] = array_values($tiers);
        $data['pricing'] = $p;
        content_save($data);
        flash('Tier deleted.');
        redirect('index.php?p=pricing');
    }
}

$editIdx = isset($_GET['edit']) && isset($tiers[(int) $_GET['edit']]) ? (int) $_GET['edit'] : null;
$adding = isset($_GET['new']);

admin_shell_open('pricing', 'Pricing', 'The rate tiers shown on the pricing page.');

if ($adding || $editIdx !== null):
  $t = $editIdx !== null ? $tiers[$editIdx] : ['name'=>'','day'=>'','night'=>'','monthly'=>'','blurb'=>'','features'=>[],'featured'=>false];
?>
  <form method="post" action="index.php?p=pricing" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="savetier">
    <?php if ($editIdx !== null): ?><input type="hidden" name="idx" value="<?= (int) $editIdx ?>"><?php endif; ?>
    <h2><?= $editIdx !== null ? 'Edit tier' : 'Add a tier' ?></h2>
    <?php fld('name', 'Tier name', (string) ($t['name'] ?? ''), 'text', ['max' => 120, 'wide' => true]); ?>
    <?php fld('blurb', 'What this tier covers', (string) ($t['blurb'] ?? ''), 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('day', 'Day rate (USD/hour)', (string) ($t['day'] ?? ''), 'text', ['max' => 20, 'hint' => 'Number only, no $ sign']); ?>
      <?php fld('night', 'Night rate (USD/hour)', (string) ($t['night'] ?? ''), 'text', ['max' => 20, 'hint' => 'Number only']); ?>
      <?php fld('monthly', 'Monthly per seat', (string) ($t['monthly'] ?? ''), 'text', ['max' => 120, 'placeholder' => '$1,211 – $1,332']); ?>
    </div>
    <?php fld_lines('features', "What's included", (array) ($t['features'] ?? [])); ?>
    <?php fld('featured', 'Highlight', !empty($t['featured']) ? '1' : '', 'checkbox', ['checkLabel' => 'Mark this tier "Most chosen"']); ?>
    <div class="aactions">
      <button class="btn btn-primary" type="submit">Save tier</button>
      <a class="btn btn-ghost" href="index.php?p=pricing">Cancel</a>
    </div>
  </form>
<?php else: ?>
  <div class="aactions" style="margin-bottom:22px">
    <a class="btn btn-primary" href="index.php?p=pricing&amp;new=1">Add a tier</a>
    <a class="btn btn-ghost" href="../pricing.php" target="_blank" rel="noopener">View the pricing page &#8599;</a>
  </div>
  <div class="apanel">
    <table class="atable atable-rows">
      <thead><tr><th>Tier</th><th>Day</th><th>Night</th><th>Monthly</th><th class="aright">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($tiers as $i => $t): ?>
          <tr>
            <td><b><?= e(strip_tags((string) ($t['name'] ?? ''))) ?></b>
              <?php if (!empty($t['featured'])): ?><span class="atag">Most chosen</span><?php endif; ?></td>
            <td>$<?= e((string) ($t['day'] ?? '')) ?></td>
            <td>$<?= e((string) ($t['night'] ?? '')) ?></td>
            <td><?= e(strip_tags((string) ($t['monthly'] ?? ''))) ?></td>
            <td class="aright anowrap">
              <a class="btn btn-ghost btn-sm" href="index.php?p=pricing&amp;edit=<?= (int) $i ?>">Edit</a>
              <form method="post" action="index.php?p=pricing" class="ainline" data-confirm="Delete this pricing tier?">
                <?= csrf_field() ?><input type="hidden" name="idx" value="<?= (int) $i ?>">
                <input type="hidden" name="action" value="deletetier">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <form method="post" action="index.php?p=pricing" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="savepage">
    <h2>Page wording</h2>
    <?php fld('h1', 'Page heading', (string) c('pricing.h1'), 'text', ['max' => 200, 'wide' => true]); ?>
    <?php fld('lead', 'Introduction', (string) c('pricing.lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('note', 'Small print under the tiers', (string) c('pricing.note'), 'textarea', ['rows' => 5, 'wide' => true]); ?>
    <h2 style="margin-top:28px">What's included section</h2>
    <?php fld('included_h2', 'Heading', (string) c('pricing.included_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    <?php fld('included_lead', 'Introduction', (string) c('pricing.included_lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld_lines('included', 'Included items', clist('pricing.included')); ?>
    <h2 style="margin-top:28px">Cost comparison box</h2>
    <?php fld('compare_title', 'Box heading', (string) c('pricing.compare_title'), 'text', ['max' => 160, 'wide' => true]); ?>
    <?php fld_pairs('compare_rows', 'Rows', clist('pricing.compare_rows'), 'One per line: label | value'); ?>
    <?php fld('compare_note', 'Note under the box', (string) c('pricing.compare_note'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <div class="aactions"><button class="btn btn-primary" type="submit">Save page wording</button></div>
  </form>
<?php endif;
admin_shell_close();
