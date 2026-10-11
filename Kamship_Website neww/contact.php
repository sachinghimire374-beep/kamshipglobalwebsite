<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
require __DIR__ . '/inc/forms.php';

// The page carries a form, so the session (and its CSRF token) must exist
// before a single byte of output is sent.
ks_session_start();

$errors = [];
$sent = false;
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    [$sent, $errors, $old] = handle_enquiry();
}

ks_head(
    'Contact | ' . c('site.brand'),
    'Email ' . c('site.email') . ' or call ' . c('site.phone_display') . '. Tell us which freight process is stretched and we will come back with a seat count and a proposed pilot.',
    'contact.php',
    ['@context' => 'https://schema.org', '@type' => 'ContactPage', 'name' => 'Contact ' . c('site.brand'),
     'mainEntity' => ['@type' => 'Organization', 'name' => c('site.brand'), 'email' => c('site.email'),
        'telephone' => c('site.phone_display'), 'url' => c('site.domain')]]
);
ks_header('contact.php');
ks_page_head([['index.php', 'Home']], 'Tell us which process is stretched',
    'Twenty minutes on a call is usually enough to tell whether there is a sensible pilot here. If there is not, we will say so.', 89);

$v = static fn(string $k): string => e((string) ($old[$k] ?? ''));
?>
<section>
  <div class="wrap split">
    <div data-reveal="left">
      <?php if ($sent): ?>
        <div class="form-card">
          <h2 style="font-size:var(--t-2)">Thank you &mdash; that reached us.</h2>
          <p>We reply to every enquiry within one business day, in your time zone. If it is urgent, call or WhatsApp
             <a href="tel:<?= e((string) c('site.phone_link')) ?>"><?= e((string) c('site.phone_display')) ?></a>.</p>
          <div class="actions">
            <a class="btn btn-ghost" href="services.php">Browse the desks</a>
            <a class="btn btn-ghost" href="pricing.php">See pricing</a>
          </div>
        </div>
      <?php else: ?>
        <form class="form-card" method="post" action="contact.php#form" id="form" novalidate>
          <?= csrf_field() ?>
          <?= honeypot_field() ?>
          <h2 style="font-size:var(--t-2)">Send us an enquiry</h2>
          <p class="small" style="margin-bottom:26px">Everything except your name and email is optional, but the more you tell us the more useful our first reply will be.</p>

          <?php if ($errors): ?>
            <div class="form-errors" role="alert">
              <b>Please check the following:</b>
              <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>

          <div class="field-row">
            <div class="field">
              <label for="f-name">Your name</label>
              <input id="f-name" name="name" type="text" autocomplete="name" maxlength="120" value="<?= $v('name') ?>" required>
            </div>
            <div class="field">
              <label for="f-company">Company</label>
              <input id="f-company" name="company" type="text" autocomplete="organization" maxlength="140" value="<?= $v('company') ?>">
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="f-email">Work email</label>
              <input id="f-email" name="email" type="email" autocomplete="email" maxlength="190" value="<?= $v('email') ?>" required>
            </div>
            <div class="field">
              <label for="f-phone">Phone or WhatsApp <span class="hint">optional</span></label>
              <input id="f-phone" name="phone" type="tel" autocomplete="tel" maxlength="40" value="<?= $v('phone') ?>">
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="f-country">Country and time zone</label>
              <input id="f-country" name="country" type="text" maxlength="90" placeholder="e.g. USA, Central time" value="<?= $v('country') ?>">
            </div>
            <div class="field">
              <label for="f-seats">Seats you think you need</label>
              <select id="f-seats" name="seats">
                <?php foreach (['Not sure yet', '2 – 3', '4 – 6', '7 – 12', 'More than 12'] as $o): ?>
                  <option<?= ($old['seats'] ?? '') === $o ? ' selected' : '' ?>><?= e($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="field">
            <label for="f-service">Which desk interests you?</label>
            <select id="f-service" name="service">
              <option>Not sure &mdash; help me choose</option>
              <?php foreach (clist('services') as $s): ?>
                <?php $t = strip_tags((string) $s['title']); ?>
                <option<?= ($old['service'] ?? '') === $t ? ' selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
              <option<?= ($old['service'] ?? '') === 'Something else' ? ' selected' : '' ?>>Something else</option>
            </select>
          </div>

          <div class="field">
            <label for="f-message">What is stretched right now?</label>
            <textarea id="f-message" name="message" maxlength="4000" placeholder="The process, roughly how many hours a week it takes, and which systems you run it in."><?= $v('message') ?></textarea>
          </div>

          <button class="btn btn-primary" type="submit" style="width:100%">Send enquiry</button>
          <p class="form-note">We use what you send only to reply to you. See our <a href="privacy.php">privacy notice</a>.</p>
        </form>
      <?php endif; ?>
    </div>

    <div data-reveal="right">
      <h2 style="font-size:var(--t-2)">Or reach us directly</h2>
      <div class="contact-tile">
        <span class="card-icon"><?= icon('mail', '', 22) ?></span>
        <span><b><?= e((string) c('site.email_label', 'General enquiries')) ?></b><a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a><br><span class="small">We reply within one business day</span></span>
      </div>
      <?php if (trim((string) c('site.email_ceo')) !== ''): ?>
      <div class="contact-tile">
        <span class="card-icon"><?= icon('spark', '', 22) ?></span>
        <span><b><?= e((string) c('site.email_ceo_label', 'Operations and partnerships')) ?></b><a href="mailto:<?= e((string) c('site.email_ceo')) ?>"><?= e((string) c('site.email_ceo')) ?></a><br><span class="small"><?= rich((string) c('site.email_ceo_note')) ?></span></span>
      </div>
      <?php endif; ?>
      <div class="contact-tile">
        <span class="card-icon"><?= icon('phone', '', 22) ?></span>
        <span><b>Phone and WhatsApp</b><a href="tel:<?= e((string) c('site.phone_link')) ?>"><?= e((string) c('site.phone_display')) ?></a><br><span class="small">Also on <a href="https://wa.me/<?= e((string) c('site.whatsapp')) ?>" rel="noopener">WhatsApp</a></span></span>
      </div>
      <div class="contact-tile">
        <span class="card-icon"><?= icon('pin', '', 22) ?></span>
        <span><b>Delivery centre</b><span class="small"><?= e((string) c('site.company')) ?><br><?= e((string) c('site.address')) ?></span></span>
      </div>
      <div class="contact-tile">
        <span class="card-icon"><?= icon('clock', '', 22) ?></span>
        <span><b>When we are reachable</b><span class="small"><?= rich((string) c('site.office_hours')) ?></span></span>
      </div>

      <div class="card mt-m">
        <h3>What to include for a faster answer</h3>
        <ul class="checks">
          <li>The process you would hand over first</li>
          <li>Roughly how many hours a week it takes today</li>
          <li>Which TMS or systems you run it in</li>
          <li>Your time zone and the hours you need covered</li>
          <li>When you would want to start</li>
        </ul>
        <p class="small mt-m">With those five things we can usually come back with a seat count, a monthly number and a proposed pilot in our first reply.</p>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Before you write</p>
      <h2>Quick answers</h2>
    </div>
    <div class="mt-l" data-reveal><?php ks_faq(clist('faqs.contact')); ?></div>
  </div>
</section>
<?php ks_footer();
