<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$data = content();
$h = is_array($data['home'] ?? null) ? $data['home'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    foreach (['badge_prefix'=>80,'badge_place'=>80,'h1_prefix'=>300,'lead'=>900,'cta1_text'=>60,
              'cta1_link'=>200,'cta2_text'=>60,'cta2_link'=>200,'hero_note'=>300,'board_caption'=>300,
              'stats_note'=>900,'marquee_label'=>120,'marquee_note'=>600,
              'pillars_eyebrow'=>60,'pillars_h2'=>200,'pillars_lead'=>700,
              'services_eyebrow'=>60,'services_h2'=>200,'services_lead'=>700,
              'desks_eyebrow'=>60,'desks_h2'=>200,'desks_lead'=>700,
              'desk_us_title'=>160,'desk_us_text'=>700,'desk_us_note'=>300,
              'desk_fw_title'=>160,'desk_fw_text'=>700,'desk_fw_note'=>300,
              'steps_eyebrow'=>60,'steps_h2'=>200,
              'vetting_eyebrow'=>60,'vetting_h2'=>200,'vetting_p1'=>900,'vetting_p2'=>900,'vetting_note'=>400,
              'industries_eyebrow'=>60,'industries_h2'=>200,'industries_lead'=>700,
              'proof_eyebrow'=>60,'proof_h2'=>200,'proof_lead'=>900,'proof_p'=>900,'proof_card_title'=>200,
              'insights_eyebrow'=>60,'insights_h2'=>200,'insights_lead'=>700,
              'faq_eyebrow'=>60,'faq_h2'=>200] as $k => $max) {
        $h[$k] = post_str($k, $max);
    }
    $h['rotator'] = post_lines('rotator');
    $h['desk_us_points'] = post_lines('desk_us_points');
    $h['desk_fw_points'] = post_lines('desk_fw_points');
    $h['desk_us_rows'] = post_pairs('desk_us_rows');
    $h['desk_fw_rows'] = post_pairs('desk_fw_rows');
    $h['vetting_rows'] = post_pairs('vetting_rows');
    $h['proof_card_points'] = post_lines('proof_card_points');

    // Stats: four fixed slots.
    $stats = [];
    for ($i = 0; $i < 4; $i++) {
        $lit = post_str("stat{$i}_literal", 20);
        $val = post_str("stat{$i}_value", 20);
        $lab = post_str("stat{$i}_label", 300);
        if ($lab === '' && $lit === '' && $val === '') { continue; }
        $stats[] = ['literal' => $lit, 'value' => $val,
                    'prefix' => post_str("stat{$i}_prefix", 5),
                    'suffix' => post_str("stat{$i}_suffix", 5), 'label' => $lab];
    }
    $h['stats'] = $stats;

    // Pillars: four fixed slots.
    $pill = [];
    for ($i = 0; $i < 4; $i++) {
        $t = post_str("pillar{$i}_title", 160);
        if ($t === '') { continue; }
        $pill[] = ['icon' => post_str("pillar{$i}_icon", 30) ?: 'check',
                   'title' => $t, 'text' => post_str("pillar{$i}_text", 700)];
    }
    $h['pillars'] = $pill;

    // Steps: three fixed slots.
    $steps = [];
    for ($i = 0; $i < 3; $i++) {
        $t = post_str("step{$i}_title", 160);
        if ($t === '') { continue; }
        $steps[] = ['title' => $t, 'text' => post_str("step{$i}_text", 700)];
    }
    $h['steps'] = $steps;

    // Board rows.
    $rows = [];
    foreach (post_lines('board_rows') as $line) {
        $bits = array_map('trim', explode('|', $line));
        $rows[] = ['item' => $bits[0] ?? '', 'lane' => $bits[1] ?? '',
                   'now' => $bits[2] ?? '', 'then' => $bits[3] ?? '',
                   'state' => (($bits[4] ?? 'done') === 'flag') ? 'flag' : 'done'];
    }
    $h['board_rows'] = $rows;

    $data['home'] = $h;
    $saved = content_save($data);
    flash($saved ? 'Home page saved.' : 'Could not save. Check that data/ is writable.', $saved ? 'ok' : 'bad');
    redirect('index.php?p=home');
}

$ICONS = ['users','message','globe','shield','clock','chart','lock','spark','layers','cycle','check'];
$stats = clist('home.stats');
$pillars = clist('home.pillars');
$steps = clist('home.steps');

$boardText = [];
foreach (clist('home.board_rows') as $r) {
    $boardText[] = implode(' | ', [(string) ($r['item'] ?? ''), (string) ($r['lane'] ?? ''),
        (string) ($r['now'] ?? ''), (string) ($r['then'] ?? ''), (string) ($r['state'] ?? 'done')]);
}

admin_shell_open('home', 'Home page', 'Every piece of text on the home page.');
?>
<form method="post" action="index.php?p=home">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">

  <section class="apanel">
    <h2>Hero</h2>
    <div class="arow">
      <?php fld('badge_prefix', 'Badge text', (string) c('home.badge_prefix'), 'text', ['max' => 80]); ?>
      <?php fld('badge_place', 'Badge place name', (string) c('home.badge_place'), 'text', ['max' => 80]); ?>
    </div>
    <?php fld('h1_prefix', 'Headline', (string) c('home.h1_prefix'), 'textarea', ['rows' => 2, 'wide' => true, 'hint' => 'The rotating word is added at the end']); ?>
    <?php fld_lines('rotator', 'Rotating words', clist('home.rotator'), 'One per line. They fade through in order.'); ?>
    <?php fld('lead', 'Introduction', (string) c('home.lead'), 'textarea', ['rows' => 4, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('cta1_text', 'First button', (string) c('home.cta1_text'), 'text', ['max' => 60]); ?>
      <?php fld('cta1_link', 'First button link', (string) c('home.cta1_link'), 'text', ['max' => 200]); ?>
    </div>
    <div class="arow">
      <?php fld('cta2_text', 'Second button', (string) c('home.cta2_text'), 'text', ['max' => 60]); ?>
      <?php fld('cta2_link', 'Second button link', (string) c('home.cta2_link'), 'text', ['max' => 200]); ?>
    </div>
    <?php fld('hero_note', 'Note under the buttons', (string) c('home.hero_note'), 'text', ['max' => 300, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>The desk queue illustration</h2>
    <?php fld_lines('board_rows', 'Rows', $boardText,
        'One per line, five parts separated by | : item | lane | first status | second status | done or flag. The second status replaces the first a moment after the page loads.'); ?>
    <?php fld('board_caption', 'Caption', (string) c('home.board_caption'), 'text', ['max' => 300, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>Statistics band</h2>
    <p class="aempty" style="margin-bottom:18px">Leave "fixed text" empty to have the number count up when it scrolls into view. Fill it in for something like 24/7 that should not animate.</p>
    <?php for ($i = 0; $i < 4; $i++): $s = $stats[$i] ?? ['literal'=>'','value'=>'','prefix'=>'','suffix'=>'','label'=>'']; ?>
      <div class="asubpanel">
        <h3>Statistic <?= $i + 1 ?></h3>
        <div class="arow">
          <?php fld("stat{$i}_value", 'Number to count to', (string) ($s['value'] ?? ''), 'text', ['max' => 20]); ?>
          <?php fld("stat{$i}_prefix", 'Before', (string) ($s['prefix'] ?? ''), 'text', ['max' => 5, 'hint' => 'e.g. $']); ?>
          <?php fld("stat{$i}_suffix", 'After', (string) ($s['suffix'] ?? ''), 'text', ['max' => 5, 'hint' => 'e.g. %']); ?>
          <?php fld("stat{$i}_literal", 'Or fixed text', (string) ($s['literal'] ?? ''), 'text', ['max' => 20]); ?>
        </div>
        <?php fld("stat{$i}_label", 'Label', (string) ($s['label'] ?? ''), 'textarea', ['rows' => 2, 'wide' => true]); ?>
      </div>
    <?php endfor; ?>
    <?php fld('stats_note', 'Note under the statistics', (string) c('home.stats_note'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>Systems strip</h2>
    <?php fld('marquee_label', 'Label above', (string) c('home.marquee_label'), 'text', ['max' => 120, 'wide' => true]); ?>
    <?php fld('marquee_note', 'Note below', (string) c('home.marquee_note'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <p class="aempty">The list of system names is edited under <a href="index.php?p=industries">Industries</a>.</p>
  </section>

  <section class="apanel">
    <h2>Why teams choose us</h2>
    <div class="arow">
      <?php fld('pillars_eyebrow', 'Small label', (string) c('home.pillars_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('pillars_h2', 'Heading', (string) c('home.pillars_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('pillars_lead', 'Introduction', (string) c('home.pillars_lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php for ($i = 0; $i < 4; $i++): $pl = $pillars[$i] ?? ['icon'=>'check','title'=>'','text'=>'']; ?>
      <div class="asubpanel">
        <div class="arow">
          <?php fld("pillar{$i}_title", 'Point ' . ($i + 1) . ' heading', (string) ($pl['title'] ?? ''), 'text', ['max' => 160, 'wide' => true]); ?>
          <?php fld("pillar{$i}_icon", 'Icon', (string) ($pl['icon'] ?? 'check'), 'select', ['options' => array_combine($ICONS, $ICONS)]); ?>
        </div>
        <?php fld("pillar{$i}_text", 'Text', (string) ($pl['text'] ?? ''), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      </div>
    <?php endfor; ?>
  </section>

  <section class="apanel">
    <h2>Services section heading</h2>
    <div class="arow">
      <?php fld('services_eyebrow', 'Small label', (string) c('home.services_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('services_h2', 'Heading', (string) c('home.services_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('services_lead', 'Introduction', (string) c('home.services_lead'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <p class="aempty">The desks themselves are edited under <a href="index.php?p=services">Services</a>.</p>
  </section>

  <section class="apanel">
    <h2>Two desks section</h2>
    <div class="arow">
      <?php fld('desks_eyebrow', 'Small label', (string) c('home.desks_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('desks_h2', 'Heading', (string) c('home.desks_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('desks_lead', 'Introduction', (string) c('home.desks_lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <div class="asubpanel">
      <h3>US night desk tab</h3>
      <?php fld('desk_us_title', 'Heading', (string) c('home.desk_us_title'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld('desk_us_text', 'Paragraph', (string) c('home.desk_us_text'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld_lines('desk_us_points', 'Bullet points', clist('home.desk_us_points')); ?>
      <?php fld_pairs('desk_us_rows', 'Shift table', clist('home.desk_us_rows'), 'One per line: time zone | our shift'); ?>
      <?php fld('desk_us_note', 'Note under the table', (string) c('home.desk_us_note'), 'text', ['max' => 300, 'wide' => true]); ?>
    </div>
    <div class="asubpanel">
      <h3>Forwarding day desk tab</h3>
      <?php fld('desk_fw_title', 'Heading', (string) c('home.desk_fw_title'), 'text', ['max' => 160, 'wide' => true]); ?>
      <?php fld('desk_fw_text', 'Paragraph', (string) c('home.desk_fw_text'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      <?php fld_lines('desk_fw_points', 'Bullet points', clist('home.desk_fw_points')); ?>
      <?php fld_pairs('desk_fw_rows', 'Shift table', clist('home.desk_fw_rows'), 'One per line: region | our shift'); ?>
      <?php fld('desk_fw_note', 'Note under the table', (string) c('home.desk_fw_note'), 'text', ['max' => 300, 'wide' => true]); ?>
    </div>
  </section>

  <section class="apanel">
    <h2>Three steps</h2>
    <div class="arow">
      <?php fld('steps_eyebrow', 'Small label', (string) c('home.steps_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('steps_h2', 'Heading', (string) c('home.steps_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php for ($i = 0; $i < 3; $i++): $st = $steps[$i] ?? ['title'=>'','text'=>'']; ?>
      <div class="asubpanel">
        <?php fld("step{$i}_title", 'Step ' . ($i + 1) . ' heading', (string) ($st['title'] ?? ''), 'text', ['max' => 160, 'wide' => true]); ?>
        <?php fld("step{$i}_text", 'Step ' . ($i + 1) . ' text', (string) ($st['text'] ?? ''), 'textarea', ['rows' => 3, 'wide' => true]); ?>
      </div>
    <?php endfor; ?>
  </section>

  <section class="apanel">
    <h2>Carrier vetting band</h2>
    <div class="arow">
      <?php fld('vetting_eyebrow', 'Small label', (string) c('home.vetting_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('vetting_h2', 'Heading', (string) c('home.vetting_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('vetting_p1', 'First paragraph', (string) c('home.vetting_p1'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('vetting_p2', 'Second paragraph', (string) c('home.vetting_p2'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld_pairs('vetting_rows', 'Commitments table', clist('home.vetting_rows'), 'One per line: commitment | standard'); ?>
    <?php fld('vetting_note', 'Small print', (string) c('home.vetting_note'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>Industries section heading</h2>
    <div class="arow">
      <?php fld('industries_eyebrow', 'Small label', (string) c('home.industries_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('industries_h2', 'Heading', (string) c('home.industries_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('industries_lead', 'Introduction', (string) c('home.industries_lead'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>Honesty section</h2>
    <div class="arow">
      <?php fld('proof_eyebrow', 'Small label', (string) c('home.proof_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('proof_h2', 'Heading', (string) c('home.proof_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('proof_lead', 'First paragraph', (string) c('home.proof_lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('proof_p', 'Second paragraph', (string) c('home.proof_p'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('proof_card_title', 'Box heading', (string) c('home.proof_card_title'), 'text', ['max' => 200, 'wide' => true]); ?>
    <?php fld_lines('proof_card_points', 'Box bullet points', clist('home.proof_card_points')); ?>
  </section>

  <section class="apanel">
    <h2>Insights and FAQ headings</h2>
    <div class="arow">
      <?php fld('insights_eyebrow', 'Insights label', (string) c('home.insights_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('insights_h2', 'Insights heading', (string) c('home.insights_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('insights_lead', 'Insights introduction', (string) c('home.insights_lead'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('faq_eyebrow', 'FAQ label', (string) c('home.faq_eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('faq_h2', 'FAQ heading', (string) c('home.faq_h2'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <p class="aempty">The questions themselves are under <a href="index.php?p=faqs">FAQs</a>.</p>
  </section>

  <div class="aactions asticky"><button class="btn btn-primary" type="submit">Save home page</button></div>
</form>
<?php admin_shell_close();
