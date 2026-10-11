<?php
/**
 * Public form handling: enquiries and job applications.
 *
 * Submissions are stored in the JSON store so nothing is lost if email
 * delivery fails, and a notification email is attempted separately.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

const FORM_MIN_SECONDS = 3;      // a human takes longer than this to fill a form
const FORM_MAX_PER_HOUR = 6;     // per IP, per form type
const CV_MAX_BYTES = 5 * 1024 * 1024;

/** Hidden field plus a timestamp. Bots fill the field; humans never see it. */
function honeypot_field(): string
{
    ks_session_start();
    $_SESSION['form_opened'] ??= time();
    return '<div class="hp" aria-hidden="true">'
        . '<label for="website-url">Leave this field empty</label>'
        . '<input type="text" id="website-url" name="website_url" tabindex="-1" autocomplete="off">'
        . '</div>'
        . '<input type="hidden" name="_t" value="' . e((string) $_SESSION['form_opened']) . '">';
}

function honeypot_tripped(): bool
{
    if (trim((string) ($_POST['website_url'] ?? '')) !== '') {
        return true;
    }
    $opened = (int) ($_POST['_t'] ?? 0);
    return $opened > 0 && (time() - $opened) < FORM_MIN_SECONDS;
}

/** Simple per-IP throttle held in the session plus the stored record count. */
function form_rate_limited(string $kind): bool
{
    ks_session_start();
    $key = 'rl_' . $kind;
    $now = time();
    $hits = array_values(array_filter(
        (array) ($_SESSION[$key] ?? []),
        static fn($t) => is_int($t) && $t > $now - 3600
    ));
    if (count($hits) >= FORM_MAX_PER_HOUR) {
        $_SESSION[$key] = $hits;
        return true;
    }
    $hits[] = $now;
    $_SESSION[$key] = $hits;
    return false;
}

function field(string $name, int $max = 500): string
{
    $v = $_POST[$name] ?? '';
    if (!is_string($v)) {
        return '';
    }
    // Strip control characters, including the CR/LF that enable header injection.
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

function valid_email(string $v): bool
{
    return $v !== ''
        && mb_strlen($v) <= 190
        && filter_var($v, FILTER_VALIDATE_EMAIL) !== false
        && !preg_match('/[\r\n]/', $v);
}

/**
 * Send a notification. Returns false silently when the host has no mail
 * transport — the submission is already saved, so nothing is lost.
 */
function notify(string $subject, string $body, string $replyTo = ''): bool
{
    if (!function_exists('mail')) {
        return false;
    }
    $to = (string) c('site.email');
    if (!valid_email($to)) {
        return false;
    }
    $domain = preg_replace('/^www\./', '', (string) parse_url((string) c('site.domain'), PHP_URL_HOST)) ?: 'kamshipglobal.com';
    $headers = [
        'From: Kamship website <noreply@' . $domain . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
    ];
    if ($replyTo !== '' && valid_email($replyTo)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $subject = mb_substr(preg_replace('/[\r\n]/', '', $subject) ?? '', 0, 160);
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

// ----------------------------------------------------------- enquiries ----
/** @return array{0:bool,1:array<string>,2:array<string,string>} */
function handle_enquiry(): array
{
    $old = [];
    foreach (['name', 'company', 'email', 'phone', 'country', 'seats', 'service', 'message'] as $f) {
        $old[$f] = field($f, $f === 'message' ? 4000 : 190);
    }

    if (honeypot_tripped()) {
        // Look successful to the bot; store nothing.
        return [true, [], []];
    }

    $errors = [];
    if ($old['name'] === '') {
        $errors[] = 'Please tell us your name.';
    }
    if (!valid_email($old['email'])) {
        $errors[] = 'Please give a valid email address so we can reply.';
    }
    if (mb_strlen($old['message']) > 4000) {
        $errors[] = 'Please keep the message under 4,000 characters.';
    }
    if ($errors) {
        return [false, $errors, $old];
    }

    if (form_rate_limited('enquiry')) {
        return [false, ['You have sent several enquiries already. Please email us directly instead.'], $old];
    }

    $rec = $old + [
        'id' => bin2hex(random_bytes(8)),
        'received' => date('c'),
        'ip' => hash('sha256', client_ip() . '|kamship'),  // hashed: we never store raw IPs
        'status' => 'new',
    ];
    $all = store_read('enquiries', []);
    array_unshift($all, $rec);
    store_write('enquiries', array_slice($all, 0, 2000));

    $body = "New website enquiry\n\n"
        . "Name: {$old['name']}\nCompany: {$old['company']}\nEmail: {$old['email']}\n"
        . "Phone: {$old['phone']}\nCountry/time zone: {$old['country']}\n"
        . "Seats: {$old['seats']}\nDesk: {$old['service']}\n\nMessage:\n{$old['message']}\n";
    notify('Website enquiry from ' . ($old['company'] ?: $old['name']), $body, $old['email']);

    return [true, [], []];
}

// -------------------------------------------------------- applications ----
/**
 * Validate and store an uploaded CV.
 * @return array{0:?array,1:?string} [fileRecord, error]
 */
function handle_cv_upload(): array
{
    if (!isset($_FILES['cv']) || !is_array($_FILES['cv'])) {
        return [null, null];
    }
    $f = $_FILES['cv'];
    $err = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($err === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'Your CV is larger than the 5 MB limit. Please send a smaller file.'];
    }
    if ($err !== UPLOAD_ERR_OK) {
        return [null, 'That file could not be uploaded. Please try again.'];
    }
    if (!is_uploaded_file((string) $f['tmp_name'])) {
        return [null, 'That upload was rejected.'];
    }
    if ((int) $f['size'] <= 0) {
        return [null, 'That file appears to be empty.'];
    }
    if ((int) $f['size'] > CV_MAX_BYTES) {
        return [null, 'Your CV is larger than the 5 MB limit. Please send a smaller file.'];
    }

    // Extension allowlist, checked against the real content type.
    $name = (string) $f['name'];
    $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
    $allowed = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                   'application/zip', 'application/octet-stream'],
        'odt'  => ['application/vnd.oasis.opendocument.text', 'application/zip'],
        'rtf'  => ['application/rtf', 'text/rtf', 'text/plain'],
        'txt'  => ['text/plain'],
    ];
    if (!isset($allowed[$ext])) {
        return [null, 'Please send your CV as a PDF, Word, ODT, RTF or text file.'];
    }

    $mime = 'application/octet-stream';
    if (class_exists('finfo')) {
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $detected = $fi->file((string) $f['tmp_name']);
        if (is_string($detected) && $detected !== '') {
            $mime = $detected;
        }
    }
    if (!in_array($mime, $allowed[$ext], true)) {
        return [null, 'That file does not look like a ' . strtoupper($ext) . ' document. Please check and try again.'];
    }

    if (!is_dir(UPLOAD_DIR) && !@mkdir(UPLOAD_DIR, 0750, true) && !is_dir(UPLOAD_DIR)) {
        error_log('kamship: cannot create upload directory');
        return [null, 'We could not store your CV just now. Please email it to us instead.'];
    }

    // Random stored name: the visitor never controls a path on our disk.
    $stored = date('Ymd') . '-' . bin2hex(random_bytes(10)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $stored;
    if (!move_uploaded_file((string) $f['tmp_name'], $dest)) {
        error_log('kamship: move_uploaded_file failed');
        return [null, 'We could not store your CV just now. Please email it to us instead.'];
    }
    @chmod($dest, 0640);

    return [[
        'stored' => $stored,
        'original' => mb_substr(preg_replace('/[^\w .\-()]+/u', '_', $name) ?? 'cv', 0, 120),
        'size' => (int) $f['size'],
        'mime' => $mime,
    ], null];
}

/** @return array{0:bool,1:array<string>,2:array<string,string>} */
function handle_application(array $job): array
{
    $old = [];
    foreach (['name', 'email', 'phone', 'address', 'education', 'experience', 'shift', 'start', 'why'] as $f) {
        $old[$f] = field($f, in_array($f, ['why', 'experience'], true) ? 3000 : 190);
    }

    if (honeypot_tripped()) {
        return [true, [], []];
    }

    $errors = [];
    if ($old['name'] === '') {
        $errors[] = 'Please tell us your full name.';
    }
    if (!valid_email($old['email'])) {
        $errors[] = 'Please give a valid email address so we can reply.';
    }
    if ($old['phone'] === '') {
        $errors[] = 'Please give a phone number we can reach you on.';
    }
    if ($old['why'] === '') {
        $errors[] = 'Please answer the question about why you are applying.';
    }

    [$file, $fileErr] = handle_cv_upload();
    if ($fileErr !== null) {
        $errors[] = $fileErr;
    }
    if ($file === null && $fileErr === null) {
        $errors[] = 'Please attach your CV.';
    }

    if ($errors) {
        if ($file !== null) {
            @unlink(UPLOAD_DIR . '/' . $file['stored']);
        }
        return [false, $errors, $old];
    }

    if (form_rate_limited('application')) {
        if ($file !== null) {
            @unlink(UPLOAD_DIR . '/' . $file['stored']);
        }
        return [false, ['You have submitted several applications already. Please email us instead.'], $old];
    }

    $rec = $old + [
        'id' => bin2hex(random_bytes(8)),
        'job_slug' => (string) ($job['slug'] ?? ''),
        'job_title' => strip_tags((string) ($job['title'] ?? '')),
        'received' => date('c'),
        'ip' => hash('sha256', client_ip() . '|kamship'),
        'status' => 'new',
        'cv' => $file,
    ];
    $all = store_read('applications', []);
    array_unshift($all, $rec);
    store_write('applications', $all);

    $body = "New job application\n\n"
        . "Role: {$rec['job_title']}\n\n"
        . "Name: {$old['name']}\nEmail: {$old['email']}\nPhone: {$old['phone']}\n"
        . "Address: {$old['address']}\nEducation: {$old['education']}\n"
        . "Shift preference: {$old['shift']}\nCan start: {$old['start']}\n\n"
        . "Experience:\n{$old['experience']}\n\nWhy this role:\n{$old['why']}\n\n"
        . "CV attached in the admin panel: " . ($file['original'] ?? '-') . "\n"
        . "View it at: " . rtrim((string) c('site.domain'), '/') . "/admin/?p=applications\n";
    notify('Job application: ' . $rec['job_title'] . ' — ' . $old['name'], $body, $old['email']);

    return [true, [], []];
}
