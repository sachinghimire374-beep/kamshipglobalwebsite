<?php
/**
 * Branding: the logo files and the colour palette.
 *
 * Colours are checked for contrast before they are accepted. The site's buttons
 * put white text on the accent colour, so an accent that is too light makes the
 * buttons unreadable — and that is exactly the kind of change that looks fine
 * to whoever made it and fails for everyone else.
 */
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

auth_require();
require_once ROOT . '/inc/media.php';

const BRAND_DEFAULTS = [
    'logo'             => 'assets/img/kamship-logo.png',
    'logo_2x'          => 'assets/img/kamship-logo@2x.png',
    'logo_3x'          => 'assets/img/kamship-logo@3x.png',
    'logo_reversed'    => 'assets/img/kamship-logo-reversed.png',
    'logo_reversed_2x' => 'assets/img/kamship-logo-reversed@2x.png',
    'logo_reversed_3x' => 'assets/img/kamship-logo-reversed@3x.png',
    'icon'             => 'assets/img/kamship-icon-192.png',
    'og'               => 'assets/img/og-image-1200x630.png',
    'logo_w'           => '199',
    'logo_h'           => '44',
    'navy'             => '#172D45',
    'accent'           => '#C20F15',
    'accent_deep'      => '#A80D12',
    'accent_light'     => '#F07B80',
    'cyan'             => '#2A93B4',
    'cyan_ink'         => '#1F6B85',
    'cyan_deep'        => '#185466',
    'cyan_light'       => '#5FB8D4',
];

/** #RGB or #RRGGBB to [r,g,b]. */
function hex_rgb(string $hex): ?array
{
    $hex = ltrim(trim($hex), '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
        return null;
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

/** WCAG relative luminance. */
function rel_lum(array $rgb): float
{
    $c = [];
    foreach ($rgb as $v) {
        $s = $v / 255;
        $c[] = $s <= 0.03928 ? $s / 12.92 : (($s + 0.055) / 1.055) ** 2.4;
    }
    return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
}

/** WCAG contrast ratio between two hex colours, 1 to 21. */
function contrast(string $a, string $b): float
{
    $ra = hex_rgb($a);
    $rb = hex_rgb($b);
    if ($ra === null || $rb === null) {
        return 0.0;
    }
    $la = rel_lum($ra);
    $lb = rel_lum($rb);
    [$hi, $lo] = $la > $lb ? [$la, $lb] : [$lb, $la];
    return round(($hi + 0.05) / ($lo + 0.05), 2);
}

$data = content();
$brand = is_array($data['brand'] ?? null) ? $data['brand'] : [];

// --------------------------------------------------------------- actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'reset') {
        $data['brand'] = BRAND_DEFAULTS;
        content_save($data);
        flash('Put back the colours and logo files the site shipped with.');
        redirect('index.php?p=branding');
    }

    if ($action === 'colours') {
        $errs = [];
        $next = $brand;

        foreach (['navy', 'accent', 'accent_deep', 'accent_light',
                  'cyan', 'cyan_ink', 'cyan_deep', 'cyan_light'] as $k) {
            $v = strtoupper(post_str($k, 10));
            if ($v === '') {
                $v = BRAND_DEFAULTS[$k];
            }
            if (hex_rgb($v) === null) {
                $errs[] = 'That is not a colour: ' . $v;
                continue;
            }
            // Normalise #abc to #AABBCC so the stored value is predictable.
            [$r, $g, $b] = hex_rgb($v);
            $next[$k] = sprintf('#%02X%02X%02X', $r, $g, $b);
        }

        // The pairs the site actually relies on. 4.5:1 is the WCAG AA bar for
        // normal-size text, which is what these colours carry.
        if (!$errs) {
            $checks = [
                ['White text on a primary button', '#FFFFFF', $next['navy'], 4.5],
                ['White text on an accent button', '#FFFFFF', $next['accent'], 4.5],
                ['Accent link text on white', $next['accent'], '#FFFFFF', 4.5],
                ['Teal link text on white', $next['cyan_ink'], '#FFFFFF', 4.5],
            ];
            foreach ($checks as [$label, $fg, $bg, $min]) {
                $r = contrast($fg, $bg);
                if ($r < $min) {
                    $errs[] = $label . ' would only reach ' . $r . ':1, and needs ' . $min . ':1.';
                }
            }
        }

        if ($errs) {
            flash(implode(' ', $errs) . ' Nothing was changed.', 'bad');
        } else {
            $data['brand'] = $next;
            $saved = content_save($data);
            flash($saved
                ? 'Colours saved. Every page uses them from now on.'
                : 'Could not save. Check that the data folder is writable.', $saved ? 'ok' : 'bad');
        }
        redirect('index.php?p=branding');
    }

    if ($action === 'logos') {
        $errs = [];
        $next = $brand;

        // Each slot takes either an upload or a pick from the library. An
        // untouched slot keeps whatever it already had.
        $slots = [
            'logo'             => 'logo',
            'logo_2x'          => 'logo2x',
            'logo_reversed'    => 'logorev',
            'logo_reversed_2x' => 'logorev2x',
            'icon'             => 'icon',
            'og'               => 'og',
        ];
        foreach ($slots as $key => $field) {
            if (!empty($_POST['clear_' . $field])) {
                $next[$key] = '';
                continue;
            }
            $picked = basename(post_str('pick_' . $field, 200));
            if ($picked !== '') {
                $next[$key] = 'assets/uploads/' . $picked;
                continue;
            }
            if (isset($_FILES[$field])) {
                [$rec, $err] = media_store($_FILES[$field], $key);
                if ($err !== null) {
                    $errs[] = $err;
                } elseif ($rec !== null) {
                    $next[$key] = (string) $rec['url'];
                    // A fresh main logo brings its own proportions with it.
                    if ($key === 'logo') {
                        $h = 44;
                        $w = max(40, (int) round(((int) $rec['w']) * $h / max(1, (int) $rec['h'])));
                        $next['logo_w'] = (string) $w;
                        $next['logo_h'] = (string) $h;
                    }
                }
            }
        }

        // An explicit size overrides what we worked out from the upload.
        $w = (int) post_str('logo_w', 6);
        $h = (int) post_str('logo_h', 6);
        if ($w >= 40 && $w <= 900) {
            $next['logo_w'] = (string) $w;
        }
        if ($h >= 12 && $h <= 200) {
            $next['logo_h'] = (string) $h;
        }

        // The 3x slot is dropped whenever the 2x one changes, because a
        // mismatched set — one size showing the old logo — is worse than none.
        if (($next['logo_2x'] ?? '') !== ($brand['logo_2x'] ?? '')) {
            $next['logo_3x'] = '';
        }
        if (($next['logo_reversed_2x'] ?? '') !== ($brand['logo_reversed_2x'] ?? '')) {
            $next['logo_reversed_3x'] = '';
        }

        $data['brand'] = $next;
        $saved = content_save($data);
        if ($errs) {
            flash(implode(' ', $errs), 'bad');
        } else {
            flash($saved ? 'Logo updated.' : 'Could not save the change.', $saved ? 'ok' : 'bad');
        }
        redirect('index.php?p=branding');
    }
}

// ----------------------------------------------------------------- view ---
$get = static fn(string $k) => (string) ($brand[$k] ?? (BRAND_DEFAULTS[$k] ?? ''));
$lib = array_slice(media_all(), 0, 12);

/** One logo slot: what is there now, an upload box, and the library picker. */
function brand_slot(string $field, string $label, string $current, string $hint, array $lib, bool $dark = false): void
{
    $src = media_src($current);
    ?>
    <div class="asubpanel">
      <h3><?= e($label) ?> <span class="ahint" style="font-weight:400"><?= e($hint) ?></span></h3>
      <?php if ($src !== ''): ?>
        <p class="abrand-shot<?= $dark ? ' abrand-shot-dark' : '' ?>">
          <img src="../<?= e($src) ?>" alt="">
        </p>
        <p class="aempty" style="word-break:break-all"><code><?= e($src) ?></code></p>
      <?php else: ?>
        <p class="aempty">Nothing set. The site falls back to the file it shipped with.</p>
      <?php endif; ?>

      <div class="afield afield-wide">
        <label for="f_<?= e($field) ?>">Upload a replacement</label>
        <input type="file" id="f_<?= e($field) ?>" name="<?= e($field) ?>"
               accept="image/jpeg,image/png,image/webp,image/gif">
      </div>

      <?php if ($lib): ?>
        <div class="amedia-pick">
          <label class="amedia-item">
            <input type="radio" name="pick_<?= e($field) ?>" value="" checked>
            <span class="amedia-none">Keep this</span>
          </label>
          <?php foreach ($lib as $m): ?>
            <label class="amedia-item">
              <input type="radio" name="pick_<?= e($field) ?>" value="<?= e((string) $m['file']) ?>">
              <img src="../<?= e((string) $m['url']) ?>" alt="">
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <label class="acheck"><input type="checkbox" name="clear_<?= e($field) ?>" value="1">
        <span>Clear this and use the shipped file</span></label>
    </div>
    <?php
}

admin_shell_open(
    'branding',
    'Logo and colours',
    'The logo files and the palette every page is built from. Colours are checked for readability before they are saved.'
);
?>

<section class="apanel">
  <h2>Colours</h2>
  <p class="aempty" style="margin-bottom:18px">
    Taken from the logo. The accent is the red in the mark, the teal is its ring,
    and the navy is the wordmark. Changing one here changes it on every page.
  </p>

  <form method="post" action="index.php?p=branding">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="colours">

    <div class="abrand-cols">
      <?php
      $swatches = [
          ['navy',         'Navy',              'Headings, dark sections and the footer'],
          ['accent',       'Accent',            'Primary buttons and highlights'],
          ['accent_deep',  'Accent, pressed',   'The same red, one step darker'],
          ['accent_light', 'Accent, light',     'Decoration only — never text'],
          ['cyan',         'Teal',              'The logo ring. Decoration only.'],
          ['cyan_ink',     'Teal, for text',    'Links and small print'],
          ['cyan_deep',    'Teal, dark',        'Hover state for links'],
          ['cyan_light',   'Teal, light',       'Lines in the hero animation'],
      ];
      foreach ($swatches as [$k, $label, $desc]):
          $v = $get($k);
          ?>
          <div class="abrand-col">
            <label for="f_<?= e($k) ?>"><?= e($label) ?><span class="ahint"><?= e($desc) ?></span></label>
            <div class="abrand-input">
              <input type="color" value="<?= e($v) ?>" aria-hidden="true" tabindex="-1"
                     data-syncs="f_<?= e($k) ?>">
              <input type="text" id="f_<?= e($k) ?>" name="<?= e($k) ?>" value="<?= e($v) ?>"
                     maxlength="7" spellcheck="false">
            </div>
          </div>
      <?php endforeach; ?>
    </div>

    <div class="aactions"><button class="btn btn-primary" type="submit">Save colours</button></div>
  </form>

  <h3 style="margin-top:26px">Readability, as things stand</h3>
  <table class="atable">
    <thead><tr><th>Where</th><th>Contrast</th><th>Needs</th><th></th></tr></thead>
    <tbody>
      <?php
      $rows = [
          ['White text on a primary button', '#FFFFFF', $get('navy'), 4.5],
          ['White text on an accent button', '#FFFFFF', $get('accent'), 4.5],
          ['Accent link text on white',      $get('accent'), '#FFFFFF', 4.5],
          ['Teal link text on white',        $get('cyan_ink'), '#FFFFFF', 4.5],
          ['Navy body text on white',        $get('navy'), '#FFFFFF', 4.5],
      ];
      foreach ($rows as [$label, $fg, $bg, $min]):
          $r = contrast($fg, $bg);
          $pass = $r >= $min;
          ?>
          <tr>
            <td><?= e($label) ?></td>
            <td class="anowrap"><b><?= e((string) $r) ?>:1</b></td>
            <td class="anowrap"><?= e((string) $min) ?>:1</td>
            <td><span class="apill <?= $pass ? 'apill-on' : 'apill-off' ?>"><?= $pass ? 'Passes' : 'Fails' ?></span></td>
          </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<form method="post" action="index.php?p=branding" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="logos">

  <section class="apanel">
    <h2>Logo files</h2>
    <p class="aempty" style="margin-bottom:18px">
      PNG with a transparent background works best. Upload the ordinary logo and a
      reversed one for the dark footer; the double-size versions are what keeps the
      logo sharp on phones and laptops.
    </p>

    <?php
    brand_slot('logo', 'Main logo', $get('logo'), 'in the header', $lib);
    brand_slot('logo2x', 'Main logo, double size', $get('logo_2x'), 'twice the pixels, same picture', $lib);
    brand_slot('logorev', 'Reversed logo', $get('logo_reversed'), 'for the dark footer', $lib, true);
    brand_slot('logorev2x', 'Reversed logo, double size', $get('logo_reversed_2x'), 'optional', $lib, true);
    ?>

    <div class="arow">
      <?php fld('logo_w', 'Logo width', $get('logo_w'), 'number',
          ['hint' => 'In pixels, at normal size. Worked out for you when you upload.']); ?>
      <?php fld('logo_h', 'Logo height', $get('logo_h'), 'number', ['hint' => 'Usually 44']); ?>
    </div>

    <?php
    brand_slot('icon', 'App icon', $get('icon'), 'square, 192px or larger', $lib);
    brand_slot('og', 'Share picture', $get('og'), '1200 × 630, shown when a page is shared', $lib);
    ?>

    <div class="aactions">
      <button class="btn btn-primary" type="submit">Save logo files</button>
    </div>
  </section>
</form>

<section class="apanel">
  <h2>Start again</h2>
  <p class="aempty" style="margin-bottom:16px">
    Puts back the colours and logo files the site was built with. Uploaded pictures
    stay in the library, so nothing is lost.
  </p>
  <form method="post" action="index.php?p=branding"
        data-confirm="Put back the original colours and logo? Your uploads stay in the library.">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset">
    <button class="btn btn-ghost" type="submit">Reset branding</button>
  </form>
</section>

<?php admin_shell_close();
