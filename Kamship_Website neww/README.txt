KAMSHIP GLOBAL — WEBSITE WITH ADMIN PANEL
=========================================

A 21-page website plus an admin panel where you edit everything yourself, and a
jobs section where people apply directly on the site.

IMPORTANT: this version needs PHP, which your cPanel hosting has. It will NOT
work on Cloudflare Pages or any other static host. If you previously planned to
use Cloudflare Pages, use cPanel instead.

Requires PHP 8.1 or newer. cPanel shows your version under "Select PHP Version".


WHAT CHANGED IN THIS VERSION
----------------------------
- The Kamship Global logo is now the site's logo, in the header, the footer, the
  browser tab and the picture shown when a page is shared.
- The colours come from that logo: the red in the mark, the teal of its ring and
  the navy of the wordmark. Every colour on the site is now one of those or a
  readable variation of one.
- Two email addresses instead of one:
      info@kamshipglobal.com   general enquiries and job applications
      ceo@kamshipglobal.com    operations and partnerships
  Both appear on the contact page, in the footer and in the band above it.
- The website address is set to https://kamshipglobal.com
- New: Pictures — a library you upload images to once and then pick from
- New: the blog is a real blog — categories, a picture per article, drafts you
  can preview before publishing, reading times, and pages when it grows
- New: Logo & colours — replace the logo and change the palette yourself
- Everything that previously needed the content file edited by hand is now
  editable in the panel.

FROM THE VERSION BEFORE THAT
- Pages are .php instead of .html, and read their text from a single content
  file the admin panel edits. Old .html links still redirect.
- The admin panel at /admin/, the jobs board at /jobs.php, and stored enquiries
  and applications.


PUTTING IT ONLINE (cPanel)
--------------------------
 1. In cPanel, open File Manager and go to the folder your domain serves
    (public_html for the main domain; for a subdomain, create it first under
    Domains > Subdomains and use the folder it makes).

 2. Upload everything in this package as a ZIP, then use "Extract". Much
    faster than uploading files one at a time.

 3. Turn on "Show Hidden Files" in File Manager settings, and check that these
    came across — they are the security rules and are easy to miss:
        .htaccess
        data/.htaccess
        inc/.htaccess
        uploads/.htaccess
        assets/uploads/.htaccess
        admin/.htaccess

 4. Set folder permissions so PHP can save your edits:
        data            -> 755   (right-click > Change Permissions)
        uploads         -> 755
        assets/uploads  -> 755
    If saving fails later with "Check that data/ is writable", try 775.

 5. Run SSL/TLS Status > Run AutoSSL so the site loads over https.

 6. Open https://your-domain/admin/ and create your administrator account.
    Do this straight away: until you do, the first person to find that page
    can create it instead.

 7. Check the site: open the home page, the jobs page, and send yourself a
    test enquiry through the contact form.


FIRST THINGS TO DO IN THE ADMIN PANEL
-------------------------------------
 1. Create both mailboxes before the site goes live, and send each a test
    message:
        info@kamshipglobal.com   on the contact page, in the footer, and where
                                 enquiries and job applications are sent
        ceo@kamshipglobal.com    shown beside it for operations and partnerships

    Until these exist, anyone emailing the site gets a bounce. Both addresses,
    their labels and the line explaining when to use the second one are under
    Settings.

 2. Settings — check the website address is https://kamshipglobal.com with no
    trailing slash. It is used in search listings and share previews, so a wrong
    one shows the wrong link on Google and LinkedIn.

 3. Settings — check the phone number (+977 9856078508, also used as WhatsApp),
    and confirm or remove the LinkedIn and Facebook links.

 4. Jobs — check the five roles. They are realistic drafts, not real vacancies:
    check the pay ranges, the number of positions and the closing dates before
    anyone sees them. Close any you are not actually hiring for.

 5. Your account — if anyone else will edit the site, add them here.


HOW THE ADMIN PANEL IS ORGANISED
--------------------------------
Dashboard       Counts and the newest enquiries and applications
Enquiries       Messages from the contact form. Reply, mark, export to CSV
Applications    Job applications with CV downloads. Track status, export
Jobs            Add, edit, reorder, close and delete roles
Services        The eight service desks
Pricing         Rate tiers and the "what's included" section
Home page       Every piece of text on the home page
Other pages     About, Why Nepal, Security, Pilot, Careers, Jobs wording —
                including the commitment cards, the security control groups,
                the pilot steps and the careers statistics
Insights        The blog: write, add a picture, save a draft, publish
Pictures        Every image you have uploaded, in one place
Industries      Sectors served, and the scrolling systems strip
FAQs            Question-and-answer blocks, one set per page
Settings        Contact details, social links, the call-to-action band
Logo & colours  The logo files and the palette
Your account    Change your password, add or remove people

Every change is saved straight to the live site. There is no publish step.


WRITING IN THE TEXT BOXES
-------------------------
Most boxes are plain text. A few formatting marks work in the longer ones:

    **Lists**        Put each item on its own line where the label says so
    **Two columns**  Put a | between them:   US Eastern | 18:45 – 03:45
    **Headings**     Start the line with "## "
    **Bullets**      Start the line with "- "
    **Numbered**     Start the line with "1. "
    **Small label**  Start the line with "> "
    **Bold**         <b>like this</b>
    **Italic**       <i>like this</i>
    **Link**         <a href="contact.php">like this</a>

Anything else you type is shown exactly as written. You cannot break the site
by pasting the wrong thing into a box — HTML you are not meant to use is
removed automatically.


PICTURES
--------
Upload an image once under Pictures, then pick it anywhere it is needed. You can
also upload straight from the article editor — it lands in the library either way.

    What is accepted   JPG, PNG, WebP or GIF, up to 6 MB each. Several at once.
    What happens to it Camera and location data are stripped, the file is given
                       a name we generate, and anything wider than 2000 pixels
                       is shrunk. Nothing about your original file reaches the
                       website.
    Where used         Each picture says which article or page is using it.
    Deleting           A picture in use will not delete until you tick "Delete
                       anyway", because a page that loses its picture shows a
                       broken image and nobody notices for weeks.

Pictures live in assets/uploads. That folder must be writable (755, or 775 if
755 is not enough) or uploads fail with a message saying so.


THE BLOG
--------
Insights is a blog. Each article has:

    Title, summary    The summary is what shows on the list and on Google
    Category          Pick one, or choose "type a new one" and name it. The
                      category filter on the blog only shows categories that
                      have a published article behind them.
    Picture           Uploaded or picked from the library. Wide pictures (16:9)
                      look best. Always describe it — that text is what someone
                      using a screen reader hears.
    Article           Same writing marks as everywhere else (see above)
    Search listing    Optional. Leave both empty and the title and summary are
                      used instead.
    Status            Draft or Published. A draft is not on the website and is
                      hidden from Google, but you can open its page while signed
                      in to read it as a visitor would. Use the Preview button.
    Feature it        Puts one article first on the blog. Only one at a time.

Reading times are worked out from the article, and the list breaks into pages
once there are more than nine articles. Both are under the blog settings at the
bottom of the Insights screen, along with the page heading and introduction.


LOGO AND COLOURS
----------------
Under "Logo & colours" you can replace the logo and change the palette.

The logo has several slots. The main one is the header. The reversed one is the
white version for the dark footer. The "double size" versions are what keep the
logo sharp on phones and laptops — upload them if you have them, and the site
manages without if you do not. Uploading a new main logo works out its
proportions for you; you can override the width and height underneath.

Colours are checked before they are saved. The site puts white text on the
primary and accent buttons, so a colour too light for white text is refused with
a message saying what it would have scored. The table underneath shows where
each colour stands against the 4.5:1 minimum for readable text — everything the
site ships with passes.

"Reset branding" puts back the original logo and colours. Your uploads stay in
the library, so nothing is lost by trying something and changing your mind.


JOBS AND APPLICATIONS
---------------------
Adding a role puts it on /jobs.php and on the careers page immediately, and
adds it to the sitemap so Google can find it. Each role gets its own page with
an application form.

    Closing a role   Hides it from the site. Applications already received are
                     kept. Use this rather than deleting.
    Deleting a role  Removes it entirely. Applications are still kept.
    Highlight        Shows a "Hiring now" badge and lists the role first.
    Closing date     After it passes, the role shows as closed and the form is
                     replaced with a note. Leave it empty for no closing date.

Applicants send a CV as PDF, Word, ODT, RTF or text, up to 5 MB. CVs are stored
in the uploads folder and can only be downloaded by someone signed in to the
admin panel — the folder itself is blocked from the web.

Applications include a status you can set: New, Reviewing, Shortlisted,
Interviewed, Hired, Not selected. "Download all as CSV" opens in Excel.

The privacy notice says applications are kept for 12 months. Delete old ones
periodically so that stays true — deleting an application deletes its CV too.


EMAIL NOTIFICATIONS
-------------------
When an enquiry or application arrives, the site tries to email you at the
address in Settings. Some hosts block PHP's mail function, so treat email as a
bonus: everything is stored in the admin panel either way, and nothing is lost
if the email never arrives. Check the panel regularly at first to see whether
the emails are getting through.


IF YOU ARE LOCKED OUT
---------------------
Passwords are stored scrambled and cannot be recovered, only replaced.

  1. In cPanel File Manager, open the data folder.
  2. Rename users.json to users-old.json
  3. Open /admin/ — it will offer to create a new account.
  4. Create it, sign in, then delete users-old.json.

After six wrong passwords, sign-in is blocked for 15 minutes. To clear that
sooner, empty data/loginlog.json (replace its contents with {} ).


UNDOING A MISTAKE
-----------------
Before every save, the previous version is copied into data/backups/ and the
last 20 are kept. To go back:

  1. In File Manager, open data/backups/
  2. Find the file with the right date and time, e.g.
     content-20260925-143012.json
  3. Copy it into the data folder and rename it to content.json
     (overwriting the current one)

The site uses it immediately. Nothing else needs doing.


BACKING UP
----------
Everything that matters lives in two folders:

    data/            all your text, jobs, enquiries and applications
    uploads/         the CV files
    assets/uploads/  the pictures you have uploaded

Download both occasionally, or use cPanel's Backup Wizard.


WHAT IS NOT INSTALLED
---------------------
No analytics — nothing tracks your visitors. Cloudflare Web Analytics is free
and needs no cookie banner if you want numbers. Add its snippet to the
ks_footer() function in inc/render.php, then update privacy.php to say so.


FILE LAYOUT
-----------
  *.php                the public pages
  admin/               the admin panel
  inc/                 shared code (blocked from the web)
  data/                your content and submissions (blocked from the web)
  uploads/             CV files (blocked from the web)
  assets/              stylesheets, scripts, fonts, logos
  assets/uploads/      pictures you have uploaded (public, but nothing there
                       can ever run as code)
  .htaccess            HTTPS, clean URLs, old-link redirects, security headers

Only assets/ and the .php pages in the top folder are reachable from the web.


SECURITY
--------
Built in: passwords stored with bcrypt; sign-in blocked after six wrong
attempts; every form protected against cross-site request forgery; everything
typed in is escaped before being shown; CV uploads restricted by both extension
and actual file content and stored under random names outside the web path;
picture uploads checked to be genuine images, re-encoded so nothing can ride
along inside them, stored under names we generate, and served from a folder
where PHP is switched off;
session cookies marked HttpOnly and Secure; security headers including a
content security policy; the data, uploads and code folders blocked by
.htaccess.

Tested here: signed-out visitors cannot reach the admin panel or download a
CV; a request without a valid token is refused; a PHP script uploaded as a CV
is rejected, and so is one renamed to .pdf; script tags pasted into content
boxes are stripped rather than run; "../" in a web address does not escape the
site; six wrong passwords trigger the lockout.

Two things worth knowing:
  - The .htaccess files are what block the data and uploads folders. If they do
    not upload, those folders are exposed. Check step 3 above.
  - Run the site over https (step 5). Without it, passwords cross the network
    in the clear.

Please have a security-aware person review this before it handles real
applicant data, and have a lawyer review privacy.php and terms.php.


CONTACT DETAILS USED THROUGHOUT
-------------------------------
Primary email:     info@kamshipglobal.com
Operations email:  ceo@kamshipglobal.com
Phone:             +977 9856078508  (also linked as WhatsApp)
Address:           Pokhara, Gandaki Province, Nepal
Website:           https://kamshipglobal.com

All of these are editable under Settings. Create both mailboxes before the site
goes live.
