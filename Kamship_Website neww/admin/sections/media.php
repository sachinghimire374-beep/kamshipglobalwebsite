<?php
/**
 * Media library: every picture the site uses, in one place.
 *
 * Uploads, deletes, and — importantly — tells the editor where each picture is
 * actually used before they delete it, because a missing picture on a live page
 * is the kind of mistake that is easy to make and hard to notice.
 */
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

auth_require();
require_once ROOT . '/inc/media.php';

/**
 * Walk the whole content tree looking for a filename, so we can say "used by
 * the article X" rather than leaving the editor to guess.
 */
function media_usage(string $file): array
{
    $used = [];
    $file = basename($file);

    foreach (clist('insights') as $p) {
        if (basename((string) ($p['image'] ?? '')) === $file) {
            $used[] = 'Article: ' . strip_tags((string) ($p['title'] ?? 'untitled'));
        }
    }
    foreach (clist('jobs') as $j) {
        if (basename((string) ($j['image'] ?? '')) === $file) {
            $used[] = 'Job: ' . strip_tags((string) ($j['title'] ?? 'untitled'));
        }
    }

    // Anything else stored anywhere in the tree — the logo, an og:image, a
    // picture on a page we add later. A recursive walk means this keeps
    // working without being updated every time a new field is added.
    $walk = static function (mixed $node, array $trail) use (&$walk, $file, &$used): void {
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $walk($v, array_merge($trail, [(string) $k]));
            }
            return;
        }
        if (!is_string($node) || $node === '') {
            return;
        }
        if (basename($node) !== $file) {
            return;
        }
        $top = $trail[0] ?? '';
        if ($top === 'insights' || $top === 'jobs') {
            return; // already named above
        }
        $label = implode(' &rsaquo; ', array_slice($trail, 0, 3));
        $used[] = 'Content: ' . $label;
    };
    $walk(content(), []);

    return array_values(array_unique($used));
}

$items = media_all();

// --------------------------------------------------------------- actions --
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'upload') {
        $ok = 0;
        $errs = [];

        // The file input is multiple, so PHP hands us parallel arrays. Rebuild
        // them into one record per file before handing each to media_store.
        $f = $_FILES['images'] ?? null;
        if (is_array($f) && is_array($f['name'] ?? null)) {
            $n = count($f['name']);
            for ($i = 0; $i < $n; $i++) {
                [$rec, $err] = media_store([
                    'name'     => (string) $f['name'][$i],
                    'type'     => (string) ($f['type'][$i] ?? ''),
                    'tmp_name' => (string) $f['tmp_name'][$i],
                    'error'    => (int) $f['error'][$i],
                    'size'     => (int) $f['size'][$i],
                ], 'img');
                if ($err !== null) {
                    $errs[] = trim((string) $f['name'][$i]) . ': ' . $err;
                } elseif ($rec !== null) {
                    $ok++;
                }
            }
        }

        if ($ok > 0 && !$errs) {
            flash($ok === 1 ? 'Uploaded one picture.' : 'Uploaded ' . $ok . ' pictures.');
        } elseif ($ok > 0) {
            flash('Uploaded ' . $ok . ', but: ' . implode(' ', $errs), 'bad');
        } elseif ($errs) {
            flash(implode(' ', $errs), 'bad');
        } else {
            flash('Choose at least one picture to upload.', 'bad');
        }
        redirect('index.php?p=media');
    }

    if ($action === 'delete') {
        $file = basename((string) ($_POST['file'] ?? ''));
        $usage = media_usage($file);
        if ($usage && empty($_POST['force'])) {
            flash('That picture is still in use (' . count($usage) . ' place'
                . (count($usage) === 1 ? '' : 's') . '). Remove it there first, or tick the box to delete it anyway.', 'bad');
        } elseif (media_delete($file)) {
            flash('Deleted ' . $file . '.');
        } else {
            flash('Could not delete that file. Check that assets/uploads is writable.', 'bad');
        }
        redirect('index.php?p=media');
    }
}

// ----------------------------------------------------------------- view ---
$totalBytes = array_sum(array_column($items, 'size'));
$writable = is_dir(MEDIA_DIR) ? is_writable(MEDIA_DIR) : is_writable(ROOT . '/assets');

admin_shell_open(
    'media',
    'Pictures',
    'Every picture uploaded through the admin panel. Upload once here, then pick it when you write an article or edit a page.'
);

if (!$writable): ?>
  <div class="anote anote-bad" role="status">
    The folder <code>assets/uploads</code> is not writable, so uploads will fail.
    In cPanel, set its permissions to 755 (or 775 if 755 is not enough).
  </div>
<?php endif; ?>

<section class="apanel">
  <h2>Upload pictures</h2>
  <form method="post" action="index.php?p=media" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
    <div class="afield afield-wide">
      <label for="f_images">Choose one or more files
        <span class="ahint">JPG, PNG, WebP or GIF, up to 6 MB each. Anything wider than 2000px is shrunk to fit.</span>
      </label>
      <input type="file" id="f_images" name="images[]" multiple
             accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="aactions"><button class="btn btn-primary" type="submit">Upload</button></div>
  </form>
  <p class="aempty" style="margin-top:14px">
    Pictures are stripped of camera and location data on the way in, and stored under a
    name we generate, so nothing about the original file leaks onto the website.
  </p>
</section>

<?php if (!$items): ?>
  <div class="apanel"><p class="aempty">No pictures yet. Upload one above and it becomes available everywhere.</p></div>
<?php else: ?>

  <div class="apanel">
    <h2>
      <?= count($items) === 1 ? 'One picture' : count($items) . ' pictures' ?>
      <span class="ahint" style="font-weight:400"><?= e(size_h($totalBytes)) ?> in total</span>
    </h2>

    <div class="amedia-grid">
      <?php foreach ($items as $m): ?>
        <?php
        $usage = media_usage((string) $m['file']);
        $dim = @getimagesize(MEDIA_DIR . '/' . $m['file']);
        ?>
        <figure class="amedia-card">
          <a class="amedia-shot" href="../<?= e((string) $m['url']) ?>" target="_blank" rel="noopener"
             title="Open the full size picture">
            <img src="../<?= e((string) $m['url']) ?>" alt="" loading="lazy">
          </a>
          <figcaption>
            <b title="<?= e((string) $m['file']) ?>"><?= e((string) $m['file']) ?></b>
            <span class="asub2">
              <?= $dim ? (int) $dim[0] . ' &times; ' . (int) $dim[1] : 'unknown size' ?>
              &middot; <?= e(size_h((int) $m['size'])) ?>
              &middot; <?= e(date('j M Y', (int) $m['time'])) ?>
            </span>
            <?php if ($usage): ?>
              <span class="amedia-used">
                <?php foreach ($usage as $u): ?><span><?= $u ?></span><?php endforeach; ?>
              </span>
            <?php else: ?>
              <span class="atag atag-quiet">Not used anywhere</span>
            <?php endif; ?>

            <div class="amedia-foot">
              <input class="amedia-name" type="text" value="<?= e((string) $m['file']) ?>" readonly
                     aria-label="File name for <?= e((string) $m['file']) ?>"
                     onfocus="this.select()">
              <form method="post" action="index.php?p=media" class="ainline"
                    data-confirm="Delete this picture? Any page still using it will show a broken image.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="file" value="<?= e((string) $m['file']) ?>">
                <?php if ($usage): ?>
                  <label class="acheck amedia-force">
                    <input type="checkbox" name="force" value="1"> <span>Delete anyway</span>
                  </label>
                <?php endif; ?>
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </div>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>

<?php endif;
admin_shell_close();
