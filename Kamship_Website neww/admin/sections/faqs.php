<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$GROUPS = [
    'home' => ['Home page', 'Shown near the bottom of the home page'],
    'jobs' => ['Jobs page', 'Shown on the jobs board for candidates'],
    'careers' => ['Careers page', 'Shown on the careers page'],
    'pricing' => ['Pricing page', 'Shown under the rate tiers'],
    'pilot' => ['Pilot page', 'Shown on the "how the pilot works" page'],
    'contact' => ['Contact page', 'Shown under the contact form'],
];

$data = content();
$faqs = is_array($data['faqs'] ?? null) ? $data['faqs'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $group = (string) ($_POST['group'] ?? '');
    if (isset($GROUPS[$group])) {
        $items = [];
        // Questions and answers arrive as parallel arrays.
        $qs = (array) ($_POST['q'] ?? []);
        $as = (array) ($_POST['a'] ?? []);
        foreach ($qs as $i => $q) {
            $q = is_string($q) ? mb_substr(trim($q), 0, 300) : '';
            $a = isset($as[$i]) && is_string($as[$i]) ? mb_substr(trim($as[$i]), 0, 3000) : '';
            if ($q !== '' && $a !== '') { $items[] = [$q, $a]; }
        }
        $faqs[$group] = $items;
        $data['faqs'] = $faqs;
        $saved = content_save($data);
        flash($saved ? 'Saved ' . count($items) . ' questions.' : 'Could not save.', $saved ? 'ok' : 'bad');
    }
    redirect('index.php?p=faqs&g=' . urlencode($group));
}

$g = (string) ($_GET['g'] ?? 'home');
if (!isset($GROUPS[$g])) { $g = 'home'; }
$items = is_array($faqs[$g] ?? null) ? $faqs[$g] : [];

admin_shell_open('faqs', 'FAQs', 'Question-and-answer blocks. Each page has its own set.');
?>
<nav class="atabs">
  <?php foreach ($GROUPS as $k => [$label, $desc]): ?>
    <a href="index.php?p=faqs&amp;g=<?= e($k) ?>"<?= $k === $g ? ' class="on"' : '' ?>>
      <?= e($label) ?> <span><?= count($faqs[$k] ?? []) ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<form method="post" action="index.php?p=faqs" class="apanel">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="group" value="<?= e($g) ?>">
  <h2><?= e($GROUPS[$g][0]) ?></h2>
  <p class="aempty" style="margin-bottom:22px"><?= e($GROUPS[$g][1]) ?>.
     Clear both boxes of a pair to remove that question.</p>

  <div id="faq-rows">
    <?php $n = max(count($items) + 2, 4); ?>
    <?php for ($i = 0; $i < $n; $i++): $it = $items[$i] ?? ['', '']; ?>
      <div class="asubpanel afaq">
        <div class="afield afield-wide">
          <label for="q<?= $i ?>">Question <?= $i + 1 ?></label>
          <input id="q<?= $i ?>" type="text" name="q[]" maxlength="300" value="<?= e((string) ($it[0] ?? '')) ?>">
        </div>
        <div class="afield afield-wide">
          <label for="a<?= $i ?>">Answer</label>
          <textarea id="a<?= $i ?>" name="a[]" rows="3"><?= e((string) ($it[1] ?? '')) ?></textarea>
        </div>
      </div>
    <?php endfor; ?>
  </div>

  <div class="aactions">
    <button class="btn btn-ghost" type="button" data-add-faq>Add another question</button>
    <button class="btn btn-primary" type="submit">Save questions</button>
  </div>
</form>
<?php admin_shell_close();
