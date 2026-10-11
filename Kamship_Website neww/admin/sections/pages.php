<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

const ICON_HINT = 'Icons: shield, radar, receipt, box, file, stamp, calc, headset, users, clock, '
                . 'message, globe, chart, lock, truck, ship, plane, search, check, mail, phone, pin, '
                . 'spark, layers, cycle, briefcase, upload.';

$PAGES = [
    'about'     => ['About', 'about.php'],
    'why_nepal' => ['Why Nepal', 'why-nepal.php'],
    'security'  => ['Security', 'security.php'],
    'pilot'     => ['Pilot', 'pilot.php'],
    'careers'   => ['Careers', 'careers.php'],
    'jobs'      => ['Jobs page wording', 'jobs.php'],
];

$data = content();
$pages = is_array($data['pages'] ?? null) ? $data['pages'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $key = (string) ($_POST['page'] ?? '');
    if (isset($PAGES[$key])) {
        $pg = is_array($pages[$key] ?? null) ? $pages[$key] : [];

        foreach (['h1' => 200, 'lead' => 900, 'empty' => 700, 'apply_intro' => 500,
                  'privacy_note' => 700, 'card_title' => 200, 'commitments_h2' => 200,
                  'coverage_title' => 160, 'continuity_title' => 160, 'cert_h2' => 200,
                  'cert_p1' => 900, 'cert_p2' => 900, 'timeline_h2' => 200,
                  'timeline_lead' => 700, 'offer_h2' => 200, 'process_title' => 160,
                  'women_note' => 700] as $k => $max) {
            if (isset($_POST[$k])) { $pg[$k] = post_str($k, $max); }
        }
        if (isset($_POST['body'])) { $pg['body'] = text_to_blocks(post_str('body', 60000)); }
        foreach (['card_points', 'continuity', 'offer'] as $k) {
            if (isset($_POST[$k])) { $pg[$k] = post_lines($k); }
        }
        foreach (['coverage_rows', 'timeline', 'process'] as $k) {
            if (isset($_POST[$k])) { $pg[$k] = post_pairs($k); }
        }
        if (isset($_POST['commitments'])) {
            $pg['commitments'] = post_tuples('commitments', ['title', 'icon', 'text']);
        }
        if (isset($_POST['steps'])) {
            $pg['steps'] = post_tuples('steps', ['title', 'text']);
        }
        if (isset($_POST['stats'])) {
            $pg['stats'] = post_tuples('stats', ['value', 'suffix', 'label']);
        }
        if (isset($_POST['groups'])) {
            $pg['groups'] = text_to_groups(post_str('groups', 20000));
        }
        if (isset($_POST['cert_faqs'])) {
            $qa = [];
            foreach (post_lines('cert_faqs') as $line) {
                $bits = explode('|', $line, 2);
                if (trim($bits[0]) !== '' && trim($bits[1] ?? '') !== '') {
                    $qa[] = [trim($bits[0]), trim($bits[1])];
                }
            }
            $pg['cert_faqs'] = $qa;
        }
        $pages[$key] = $pg;
        $data['pages'] = $pages;
        $saved = content_save($data);
        flash($saved ? e($PAGES[$key][0]) . ' page saved.' : 'Could not save.', $saved ? 'ok' : 'bad');
        redirect('index.php?p=pages&g=' . urlencode($key));
    }
    redirect('index.php?p=pages');
}

$g = (string) ($_GET['g'] ?? 'about');
if (!isset($PAGES[$g])) { $g = 'about'; }
$pg = is_array($pages[$g] ?? null) ? $pages[$g] : [];
$get = static fn(string $k, $d = '') => $pg[$k] ?? $d;

admin_shell_open('pages', 'Other pages', 'The longer written pages.');
?>
<nav class="atabs">
  <?php foreach ($PAGES as $k => [$label, $file]): ?>
    <a href="index.php?p=pages&amp;g=<?= e($k) ?>"<?= $k === $g ? ' class="on"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<form method="post" action="index.php?p=pages">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="page" value="<?= e($g) ?>">

  <section class="apanel">
    <div class="aactions" style="justify-content:space-between;margin-bottom:18px">
      <h2 style="margin:0"><?= e($PAGES[$g][0]) ?></h2>
      <a class="btn btn-ghost btn-sm" href="../<?= e($PAGES[$g][1]) ?>" target="_blank" rel="noopener">View page &#8599;</a>
    </div>

    <?php fld('h1', 'Page heading', (string) $get('h1'), 'text', ['max' => 200, 'wide' => true]); ?>
    <?php fld('lead', 'Introduction', (string) $get('lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>

    <?php if (in_array($g, ['about', 'why_nepal'], true)): ?>
      <?php fld('body', 'Main text', blocks_to_text((array) $get('body', [])), 'textarea',
          ['rows' => 24, 'wide' => true, 'hint' => BLOCK_HELP]); ?>
    <?php endif; ?>

    <?php if ($g === 'about'): ?>
      <?php fld('card_title', 'Side box heading', (string) $get('card_title'), 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld_lines('card_points', 'Side box points', (array) $get('card_points', [])); ?>
      <?php fld('commitments_h2', 'Commitments heading', (string) $get('commitments_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld_tuples('commitments', 'Commitment cards', (array) $get('commitments', []),
          ['title', 'icon', 'text'],
          'One card per line: heading | icon | the paragraph. ' . ICON_HINT); ?>
    <?php endif; ?>

    <?php if ($g === 'why_nepal'): ?>
      <?php fld('coverage_title', 'Coverage box heading', (string) $get('coverage_title'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld_pairs('coverage_rows', 'Coverage table', (array) $get('coverage_rows', []), 'One per line: region | when we cover it'); ?>
      <?php fld('continuity_title', 'Continuity box heading', (string) $get('continuity_title'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld_lines('continuity', 'Continuity points', (array) $get('continuity', [])); ?>
    <?php endif; ?>

    <?php if ($g === 'security'): ?>
      <?php fld('cert_h2', 'Certification heading', (string) $get('cert_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld('cert_p1', 'Certification paragraph 1', (string) $get('cert_p1'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld('cert_p2', 'Certification paragraph 2', (string) $get('cert_p2'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php
      $qaLines = [];
      foreach ((array) $get('cert_faqs', []) as $qa) { $qaLines[] = ($qa[0] ?? '') . ' | ' . ($qa[1] ?? ''); }
      fld('cert_faqs', 'Questions', implode("\n", $qaLines), 'textarea',
          ['rows' => 8, 'wide' => true, 'hint' => 'One per line: question | answer']);
      ?>
      <?php fld('groups', 'Control groups', groups_to_text((array) $get('groups', [])), 'textarea',
          ['rows' => 26, 'wide' => true,
           'hint' => 'Start each group with "## Heading | icon", then one "- " line per control. ' . ICON_HINT]); ?>
    <?php endif; ?>

    <?php if ($g === 'pilot'): ?>
      <?php fld('timeline_h2', 'Timeline heading', (string) $get('timeline_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld('timeline_lead', 'Timeline introduction', (string) $get('timeline_lead'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
      <?php fld_pairs('timeline', 'Timeline rows', (array) $get('timeline', []), 'One per line: when | what happens'); ?>
      <?php fld_tuples('steps', 'The big steps', (array) $get('steps', []), ['title', 'text'],
          'One step per line: heading | what happens. These are numbered automatically.'); ?>
    <?php endif; ?>

    <?php if ($g === 'careers'): ?>
      <?php fld('offer_h2', 'What we offer heading', (string) $get('offer_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld_lines('offer', 'What we offer', (array) $get('offer', [])); ?>
      <?php fld('process_title', 'Hiring process heading', (string) $get('process_title'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld_pairs('process', 'Hiring process steps', (array) $get('process', []), 'One per line: step | short explanation'); ?>
      <?php fld('women_note', 'Note about applications from women', (string) $get('women_note'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld_tuples('stats', 'Statistics', (array) $get('stats', []), ['value', 'suffix', 'label'],
          'One per line: number | what follows it (% or +, leave empty for none) | the caption. '
          . 'Numbers count up as the visitor scrolls, so keep the first column a plain number.'); ?>
    <?php endif; ?>

    <?php if ($g === 'jobs'): ?>
      <?php fld('empty', 'Shown when no roles are open', (string) $get('empty'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld('apply_intro', 'Text above the application form', (string) $get('apply_intro'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
      <?php fld('privacy_note', 'Privacy note under the form', (string) $get('privacy_note'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php endif; ?>

    <div class="aactions"><button class="btn btn-primary" type="submit">Save page</button></div>
  </section>
</form>
<?php admin_shell_close();
