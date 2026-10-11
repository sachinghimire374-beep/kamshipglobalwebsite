<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
auth_require();

$data = content();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $s = is_array($data['site'] ?? null) ? $data['site'] : [];
    foreach (['brand' => 120, 'company' => 140, 'domain' => 200, 'email' => 190,
              'email_label' => 80, 'email_ceo' => 190, 'email_ceo_label' => 80, 'email_ceo_note' => 300,
              'phone_display' => 60, 'phone_link' => 40, 'whatsapp' => 30, 'city' => 120,
              'address' => 220, 'linkedin' => 250, 'facebook' => 250, 'jobs_external' => 250,
              'founded' => 10, 'topbar_live' => 80, 'office_hours' => 400,
              'footer_blurb' => 400, 'footer_note' => 300] as $k => $max) {
        $s[$k] = post_str($k, $max);
    }
    foreach (['email' => 'primary', 'email_ceo' => 'operations'] as $k => $what) {
        if ($s[$k] !== '' && !filter_var($s[$k], FILTER_VALIDATE_EMAIL)) {
            flash('The ' . $what . ' email address does not look valid. Nothing was saved.', 'bad');
            redirect('index.php?p=settings');
        }
    }
    $data['site'] = $s;

    $cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
    foreach (['eyebrow' => 60, 'title' => 200, 'text' => 700, 'btn1' => 60, 'btn1_link' => 200,
              'btn2' => 60, 'btn2_link' => 200, 'reply_note' => 160] as $k => $max) {
        $cta[$k] = post_str('cta_' . $k, $max);
    }
    $data['cta'] = $cta;

    $saved = content_save($data);
    flash(
        $saved ? 'Settings saved. Every page now shows the new details.'
               : 'Could not save. Check that the data folder is writable by the web server.',
        $saved ? 'ok' : 'bad'
    );
    redirect('index.php?p=settings');
}

admin_shell_open('settings', 'Settings', 'Contact details and social links, used across every page and in the footer.');
?>
<form method="post" action="index.php?p=settings">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">

  <section class="apanel">
    <h2>Contact details</h2>
    <div class="arow">
      <?php fld('email', 'Primary email', (string) c('site.email'), 'email', ['max' => 190, 'hint' => 'Where enquiries and job applications are sent']); ?>
      <?php fld('email_label', 'Label for it', (string) c('site.email_label'), 'text', ['max' => 80, 'hint' => 'e.g. General enquiries']); ?>
    </div>
    <div class="arow">
      <?php fld('email_ceo', 'Operations email', (string) c('site.email_ceo'), 'email', ['max' => 190, 'hint' => 'Shown beside the primary one. Leave empty to hide it.']); ?>
      <?php fld('email_ceo_label', 'Label for it', (string) c('site.email_ceo_label'), 'text', ['max' => 80, 'hint' => 'e.g. Operations and partnerships']); ?>
    </div>
    <?php fld('email_ceo_note', 'One line explaining when to use it', (string) c('site.email_ceo_note'), 'text', ['max' => 300, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('phone_display', 'Phone, as shown', (string) c('site.phone_display'), 'text', ['max' => 60]); ?>
      <?php fld('phone_link', 'Phone, for tap-to-call', (string) c('site.phone_link'), 'text', ['max' => 40, 'hint' => 'No spaces, e.g. +9779856078508']); ?>
    </div>
    <?php fld('whatsapp', 'WhatsApp number', (string) c('site.whatsapp'), 'text', ['max' => 30, 'wide' => true, 'hint' => 'Digits only, with country code']); ?>
    <?php fld('address', 'Address', (string) c('site.address'), 'text', ['max' => 220, 'wide' => true]); ?>
    <?php fld('office_hours', 'When you are reachable', (string) c('site.office_hours'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>Brand and web address</h2>
    <div class="arow">
      <?php fld('brand', 'Brand name', (string) c('site.brand'), 'text', ['max' => 120]); ?>
      <?php fld('company', 'Legal company name', (string) c('site.company'), 'text', ['max' => 140]); ?>
      <?php fld('founded', 'Division founded', (string) c('site.founded'), 'text', ['max' => 10]); ?>
    </div>
    <?php fld('domain', 'Website address', (string) c('site.domain'), 'url', ['max' => 200, 'wide' => true, 'hint' => 'With https:// and no trailing slash. Used in search listings and share previews.']); ?>
    <?php fld('city', 'City', (string) c('site.city'), 'text', ['max' => 120]); ?>
  </section>

  <section class="apanel">
    <h2>Social links</h2>
    <div class="arow">
      <?php fld('linkedin', 'LinkedIn page', (string) c('site.linkedin'), 'url', ['max' => 250]); ?>
      <?php fld('facebook', 'Facebook page', (string) c('site.facebook'), 'url', ['max' => 250]); ?>
    </div>
    <?php fld('jobs_external', 'KamShip AI jobs link', (string) c('site.jobs_external'), 'url', ['max' => 250, 'wide' => true, 'hint' => 'Kept for reference. The website now lists jobs itself.']); ?>
  </section>

  <section class="apanel">
    <h2>Footer and top bar</h2>
    <?php fld('topbar_live', 'Top bar text', (string) c('site.topbar_live'), 'text', ['max' => 80, 'hint' => 'Shown beside the live Pokhara clock']); ?>
    <?php fld('footer_blurb', 'Footer description', (string) c('site.footer_blurb'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('footer_note', 'Footer note', (string) c('site.footer_note'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
  </section>

  <section class="apanel">
    <h2>The call-to-action band</h2>
    <p class="aempty" style="margin-bottom:18px">The dark band with the two buttons that appears near the bottom of most pages.</p>
    <div class="arow">
      <?php fld('cta_eyebrow', 'Small label', (string) c('cta.eyebrow'), 'text', ['max' => 60]); ?>
      <?php fld('cta_title', 'Heading', (string) c('cta.title'), 'text', ['max' => 200, 'wide' => true]); ?>
    </div>
    <?php fld('cta_text', 'Paragraph', (string) c('cta.text'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('cta_btn1', 'First button', (string) c('cta.btn1'), 'text', ['max' => 60]); ?>
      <?php fld('cta_btn1_link', 'First button link', (string) c('cta.btn1_link'), 'text', ['max' => 200]); ?>
    </div>
    <div class="arow">
      <?php fld('cta_btn2', 'Second button', (string) c('cta.btn2'), 'text', ['max' => 60]); ?>
      <?php fld('cta_btn2_link', 'Second button link', (string) c('cta.btn2_link'), 'text', ['max' => 200]); ?>
    </div>
    <?php fld('cta_reply_note', 'Reply note', (string) c('cta.reply_note'), 'text', ['max' => 160, 'wide' => true]); ?>
  </section>

  <div class="aactions asticky"><button class="btn btn-primary" type="submit">Save settings</button></div>
</form>
<?php admin_shell_close();
