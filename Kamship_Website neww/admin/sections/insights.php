<?php
/** Blog: write, edit, illustrate, draft and publish. */
declare(strict_types=1);
if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once ROOT . '/inc/media.php';
$me = auth_require();

$data = content();
$items = is_array($data['insights'] ?? null) ? $data['insights'] : [];
$cats = clist('blog.categories');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $idx = isset($_POST['idx']) ? (int) $_POST['idx'] : -1;
    $exists = $idx >= 0 && isset($items[$idx]);

    if ($action === 'save') {
        $slug = slugify(post_str('slug', 100) ?: post_str('title', 160));
        $taken = [];
        foreach ($items as $i => $p) {
            if ($i !== $idx) { $taken[] = (string) ($p['slug'] ?? ''); }
        }
        $base = $slug; $n = 2;
        while (in_array($slug, $taken, true)) { $slug = $base . '-' . $n++; }

        $date = post_str('date', 20);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = date('Y-m-d'); }

        // Keep the existing picture unless a new one is uploaded or it is cleared.
        $image = $exists ? (string) ($items[$idx]['image'] ?? '') : '';
        if (!empty($_POST['remove_image'])) {
            $image = '';
        }
        $imgErr = null;
        if (!empty($_FILES['image']['name'])) {
            [$rec, $imgErr] = media_store($_FILES['image'], 'post');
            if ($rec !== null) { $image = $rec['file']; }
        }
        $pick = post_str('pick_image', 160);
        if ($pick !== '') { $image = basename($pick); }

        $cat = post_str('cat', 80);
        if ($cat === '__new') { $cat = post_str('cat_new', 80); }

        $post = [
            'slug' => $slug,
            'title' => post_str('title', 200),
            'date' => $date,
            'date_display' => date('j F Y', (int) strtotime($date)),
            'cat' => $cat,
            'author' => post_str('author', 120) ?: (string) $me['name'],
            'excerpt' => post_str('excerpt', 700),
            'body' => text_to_blocks(post_str('body', 80000)),
            'image' => $image,
            'image_alt' => post_str('image_alt', 250),
            'seo_title' => post_str('seo_title', 200),
            'seo_desc' => post_str('seo_desc', 300),
            'status' => post_str('status', 20) === 'draft' ? 'draft' : 'published',
            'featured' => post_bool('featured'),
        ];

        if ($post['title'] === '') {
            flash('An article needs a title.', 'bad');
            redirect('index.php?p=insights');
        }

        // Only one article can be featured.
        if ($post['featured']) {
            foreach ($items as $i => $_) { $items[$i]['featured'] = false; }
        }

        // A new category typed in gets added to the managed list.
        if ($cat !== '' && !in_array($cat, $cats, true)) {
            $cats[] = $cat;
            sort($cats);
            $data['blog']['categories'] = $cats;
        }

        if ($exists) { $items[$idx] = $post; } else { array_unshift($items, $post); }
        $data['insights'] = array_values($items);

        $saved = content_save($data);
        $msg = $saved
            ? ($post['status'] === 'draft'
                ? 'Saved as a draft. It is not on the website yet.'
                : 'Published. It is live on the website now.')
            : 'Could not save. Check that data/ is writable.';
        if ($imgErr !== null) { $msg .= ' Note: ' . $imgErr; }
        flash($msg, $saved && $imgErr === null ? 'ok' : ($saved ? 'bad' : 'bad'));
        redirect('index.php?p=insights');
    }

    if ($action === 'status' && $exists) {
        $items[$idx]['status'] = ($items[$idx]['status'] ?? 'published') === 'published' ? 'draft' : 'published';
        $data['insights'] = array_values($items);
        content_save($data);
        flash($items[$idx]['status'] === 'published'
            ? 'Published. It is live on the website now.'
            : 'Moved back to draft. It is no longer on the website.');
        redirect('index.php?p=insights');
    }

    if ($action === 'delete' && $exists) {
        $t = strip_tags((string) ($items[$idx]['title'] ?? ''));
        array_splice($items, $idx, 1);
        $data['insights'] = array_values($items);
        content_save($data);
        flash('Deleted "' . $t . '". The picture is kept in the media library.');
        redirect('index.php?p=insights');
    }

    if ($action === 'settings') {
        $data['blog'] = [
            'h1' => post_str('h1', 200),
            'lead' => post_str('lead', 700),
            'empty' => post_str('empty', 500),
            'per_page' => max(3, min(30, (int) post_str('per_page', 4))),
            'categories' => post_lines('categories'),
        ];
        flash(content_save($data) ? 'Blog settings saved.' : 'Could not save.', 'ok');
        redirect('index.php?p=insights');
    }
}

$editIdx = isset($_GET['edit']) && isset($items[(int) $_GET['edit']]) ? (int) $_GET['edit'] : null;
$adding = isset($_GET['new']);

admin_shell_open('insights', 'Blog', 'Write articles, add a picture, save as a draft, and publish when you are ready.');

if ($adding || $editIdx !== null):
    $p = $editIdx !== null ? $items[$editIdx] : [
        'slug' => '', 'title' => '', 'date' => date('Y-m-d'), 'cat' => '',
        'author' => (string) $me['name'], 'excerpt' => '', 'body' => [],
        'image' => '', 'image_alt' => '', 'seo_title' => '', 'seo_desc' => '',
        'status' => 'draft', 'featured' => false,
    ];
    $curImg = media_src((string) ($p['image'] ?? ''));
    $catOpts = ['' => '— none —'];
    foreach ($cats as $c) { $catOpts[$c] = $c; }
    $catOpts['__new'] = '+ type a new one below';
    ?>
    <form method="post" action="index.php?p=insights" enctype="multipart/form-data" class="apanel">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <?php if ($editIdx !== null): ?><input type="hidden" name="idx" value="<?= (int) $editIdx ?>"><?php endif; ?>

      <h2><?= $editIdx !== null ? 'Edit article' : 'Write an article' ?></h2>

      <?php fld('title', 'Title', (string) $p['title'], 'text', ['max' => 200, 'wide' => true]); ?>
      <?php fld('excerpt', 'Summary', (string) $p['excerpt'], 'textarea',
          ['rows' => 3, 'wide' => true, 'hint' => 'Shown on the blog list and in search results. Two sentences is about right.']); ?>

      <div class="arow">
        <?php fld('cat', 'Category', (string) $p['cat'], 'select', ['options' => $catOpts]); ?>
        <?php fld('cat_new', 'New category name', '', 'text', ['max' => 80, 'hint' => 'Only if you chose "type a new one"']); ?>
        <?php fld('date', 'Date', (string) $p['date'], 'date'); ?>
        <?php fld('author', 'Author', (string) ($p['author'] ?? ''), 'text', ['max' => 120]); ?>
      </div>

      <div class="asubpanel">
        <h3>Picture</h3>
        <?php if ($curImg !== ''): ?>
          <p><img src="../<?= e($curImg) ?>" alt="" style="max-width:340px;border-radius:8px;border:1px solid var(--line)"></p>
          <label class="acheck"><input type="checkbox" name="remove_image" value="1"> <span>Remove this picture</span></label>
        <?php endif; ?>
        <div class="afield afield-wide">
          <label for="f_image">Upload a new one <span class="ahint">JPG, PNG, WebP or GIF, up to 6 MB. Wide pictures look best (16:9).</span></label>
          <input type="file" id="f_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
        <?php $lib = array_slice(media_all(), 0, 12); ?>
        <?php if ($lib): ?>
          <p class="aempty" style="margin-bottom:10px">Or reuse one you have already uploaded:</p>
          <div class="amedia-pick">
            <label class="amedia-item">
              <input type="radio" name="pick_image" value="" checked>
              <span class="amedia-none">Keep current</span>
            </label>
            <?php foreach ($lib as $m): ?>
              <label class="amedia-item">
                <input type="radio" name="pick_image" value="<?= e($m['file']) ?>">
                <img src="../<?= e($m['url']) ?>" alt="">
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php fld('image_alt', 'Describe the picture', (string) ($p['image_alt'] ?? ''), 'text',
            ['max' => 250, 'wide' => true, 'hint' => 'For people using a screen reader. Say what the picture shows.']); ?>
      </div>

      <?php fld('body', 'Article', blocks_to_text((array) ($p['body'] ?? [])), 'textarea',
          ['rows' => 26, 'wide' => true, 'hint' => BLOCK_HELP]); ?>

      <div class="asubpanel">
        <h3>Search engine listing <span class="ahint" style="font-weight:400">optional</span></h3>
        <?php fld('seo_title', 'Title for Google', (string) ($p['seo_title'] ?? ''), 'text',
            ['max' => 200, 'wide' => true, 'hint' => 'Leave empty to use the article title']); ?>
        <?php fld('seo_desc', 'Description for Google', (string) ($p['seo_desc'] ?? ''), 'textarea',
            ['rows' => 2, 'wide' => true, 'hint' => 'Leave empty to use the summary. Aim for 120 to 155 characters.']); ?>
      </div>

      <div class="arow">
        <?php fld('status', 'Status', (string) ($p['status'] ?? 'draft'), 'select',
            ['options' => ['draft' => 'Draft — only you can see it', 'published' => 'Published — live on the website']]); ?>
        <?php fld('featured', 'Feature it', !empty($p['featured']) ? '1' : '', 'checkbox',
            ['checkLabel' => 'Show this article first on the blog']); ?>
        <?php fld('slug', 'Web address', (string) $p['slug'], 'text',
            ['max' => 100, 'hint' => 'Left empty, made from the title']); ?>
      </div>

      <div class="aactions">
        <button class="btn btn-primary" type="submit">Save article</button>
        <a class="btn btn-ghost" href="index.php?p=insights">Cancel</a>
        <?php if ($editIdx !== null): ?>
          <a class="btn btn-ghost" href="../insight.php?p=<?= e(urlencode((string) $p['slug'])) ?>" target="_blank" rel="noopener">Preview &#8599;</a>
        <?php endif; ?>
      </div>
    </form>

<?php else: ?>

  <div class="aactions" style="margin-bottom:22px">
    <a class="btn btn-primary" href="index.php?p=insights&amp;new=1">Write an article</a>
    <a class="btn btn-ghost" href="index.php?p=media">Media library</a>
    <a class="btn btn-ghost" href="../insights.php" target="_blank" rel="noopener">View the blog &#8599;</a>
  </div>

  <?php if (!$items): ?>
    <div class="apanel"><p class="aempty">No articles yet. Write one and it appears on the blog as soon as you publish it.</p></div>
  <?php else: ?>
    <div class="apanel">
      <table class="atable atable-rows">
        <thead><tr><th></th><th>Article</th><th>Category</th><th>Date</th><th>Status</th><th class="aright">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($items as $i => $p): ?>
            <?php
            $published = ($p['status'] ?? 'published') === 'published';
            $thumb = media_src((string) ($p['image'] ?? ''));
            ?>
            <tr>
              <td style="width:78px">
                <?php if ($thumb !== ''): ?>
                  <img src="../<?= e($thumb) ?>" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:5px;display:block">
                <?php else: ?>
                  <span class="aempty" style="font-size:0.72rem">no picture</span>
                <?php endif; ?>
              </td>
              <td>
                <b><?= e(strip_tags((string) ($p['title'] ?? ''))) ?></b>
                <?php if (!empty($p['featured'])): ?><span class="atag">Featured</span><?php endif; ?>
                <span class="asub2"><?= e(mb_substr(strip_tags((string) ($p['excerpt'] ?? '')), 0, 96)) ?>…</span>
              </td>
              <td><?= e((string) ($p['cat'] ?? '')) ?></td>
              <td class="anowrap"><?= e((string) ($p['date_display'] ?? '')) ?></td>
              <td><span class="apill <?= $published ? 'apill-on' : 'apill-new' ?>"><?= $published ? 'Published' : 'Draft' ?></span></td>
              <td class="aright anowrap">
                <a class="btn btn-ghost btn-sm" href="index.php?p=insights&amp;edit=<?= (int) $i ?>">Edit</a>
                <a class="btn btn-ghost btn-sm" href="../insight.php?p=<?= e(urlencode((string) $p['slug'])) ?>" target="_blank" rel="noopener">View</a>
                <form method="post" action="index.php?p=insights" class="ainline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="status">
                  <button class="btn btn-ghost btn-sm" type="submit"><?= $published ? 'Unpublish' : 'Publish' ?></button>
                </form>
                <form method="post" action="index.php?p=insights" class="ainline" data-confirm="Delete this article?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idx" value="<?= (int) $i ?>">
                  <input type="hidden" name="action" value="delete">
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <form method="post" action="index.php?p=insights" class="apanel">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="settings">
    <h2>Blog settings</h2>
    <?php fld('h1', 'Page heading', (string) c('blog.h1'), 'text', ['max' => 200, 'wide' => true]); ?>
    <?php fld('lead', 'Introduction', (string) c('blog.lead'), 'textarea', ['rows' => 3, 'wide' => true]); ?>
    <?php fld('empty', 'Shown when nothing is published', (string) c('blog.empty'), 'textarea', ['rows' => 2, 'wide' => true]); ?>
    <div class="arow">
      <?php fld('per_page', 'Articles per page', (string) c('blog.per_page', '9'), 'number'); ?>
    </div>
    <?php fld_lines('categories', 'Categories', clist('blog.categories'),
        'One per line. Removing one here does not change articles already using it.'); ?>
    <div class="aactions"><button class="btn btn-primary" type="submit">Save blog settings</button></div>
  </form>

<?php endif;
admin_shell_close();
