<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Privacy notice | ' . c('site.brand'),
    'How Kamship Global handles personal information collected through this website, including enquiries and job applications.',
    'privacy.php');
ks_header('');
ks_page_head([['index.php', 'Home']], 'Privacy notice',
    'How Kamship Global handles personal information collected through this website.', 91);
?>
<section><div class="wrap-narrow prose" data-reveal="fade">
<p class="small"><b>Status:</b> this notice is a template prepared for review by Kamship's legal adviser before publication. Last updated September 2026.</p>
<h2>Who we are</h2>
<p>This website is operated by <?= e((string) c('site.company')) ?> ("Kamship", "we", "us"), <?= e((string) c('site.address')) ?>.
For any question about this notice, contact <a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a>
or call <?= e((string) c('site.phone_display')) ?>.</p>
<h2>What we collect</h2>
<ul>
<li><b>Enquiries:</b> your name, work email, phone number, company, country and whatever you write in the contact form.</li>
<li><b>Job applications:</b> your name, email, phone, where you live, your education, work experience, shift preference, availability, your answer about why you are applying, and the CV file you attach.</li>
<li><b>Technical information:</b> basic request data recorded by our hosting provider, such as browser type and the pages requested, kept for security and troubleshooting. We store a one-way hash of the IP address with each submission to detect abuse, not the address itself.</li>
</ul>
<h2>How we use it</h2>
<ul>
<li>To reply to your enquiry, prepare proposals and arrange calls</li>
<li>To consider your application, contact you about it, and arrange assessments and interviews</li>
<li>To keep this website secure and working</li>
</ul>
<p>We do not sell personal information, and we do not use enquiry or application details for unrelated marketing.</p>
<h2>Legal basis for UK and EU visitors</h2>
<p>We process enquiry information on the basis of our legitimate interest in responding to business enquiries, or on the basis of your consent where consent is required. Applications are processed to take steps at your request before entering a contract of employment. You can withdraw consent or object at any time by emailing us.</p>
<h2>International transfers</h2>
<p>We are established in Nepal. Where personal data from the United Kingdom or the European Economic Area is transferred to us, we rely on appropriate safeguards, including the UK International Data Transfer Addendum or the EU Standard Contractual Clauses, which we enter into with clients before processing their data.</p>
<h2>How long we keep it</h2>
<ul>
<li><b>Enquiries:</b> up to 24 months unless a business relationship follows.</li>
<li><b>Job applications and CVs:</b> 12 months from the date you apply, so we can consider you for later openings. Ask us and we will delete yours sooner.</li>
</ul>
<h2>Who can see your application</h2>
<p>Applications and CVs are stored on our website's hosting and are visible only to Kamship staff who are signed in to the site's administration area. CV files are not publicly downloadable.</p>
<h2>Your rights</h2>
<p>Subject to applicable law, you may ask us to give you access to the personal information we hold about you, correct it, delete it, restrict how we use it, or provide it in a portable form. You may also object to our use of it. Email <a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a> and we will respond within 30 days.</p>
<h2>Data we process for clients</h2>
<p>When we carry out work for a client, any personal data in that client's systems is processed under our agreement with that client, who is the controller. This notice does not govern that processing; the client's own privacy notice does.</p>
<h2>Cookies</h2>
<p>This website sets no advertising or tracking cookies. A single session cookie is set when you submit a form or sign in to the administration area, and it is deleted when you close your browser. If we add analytics, this section will be updated to say which service and what it records before it goes live.</p>
<h2>Changes</h2>
<p>If we change this notice we will update the date above and, for material changes affecting existing enquiries, applications or clients, tell those people directly.</p>
</div></section>
<?php ks_footer();
