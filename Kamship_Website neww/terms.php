<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/render.php';
ks_head('Terms | ' . c('site.brand'),
    'The terms on which the Kamship Global website is made available, including pricing, targets and third-party software names.',
    'terms.php');
ks_header('');
ks_page_head([['index.php', 'Home']], 'Website terms', 'The terms on which this website is made available. Draft for legal review.', 93);
?>
<section><div class="wrap-narrow prose" data-reveal="fade">
<p class="small"><b>Status:</b> template prepared for review by Kamship's legal adviser before publication.</p>
<h2>About these terms</h2>
<p>This website is published by <?= e((string) c('site.company')) ?>, <?= e((string) c('site.address')) ?>. By using it you accept these terms.</p>
<h2>Nothing here is an offer or a contract</h2>
<p>Descriptions of our services, targets and prices are general information to help you decide whether to talk to us. They are not an offer capable of acceptance and they do not form a contract. Services are provided only under a separately signed agreement, and where that agreement differs from this website, the agreement governs.</p>
<h2>Job listings</h2>
<p>Roles listed on this website are genuine openings at the time of publication. Listing a role is not an offer of employment, and we may change or withdraw a listing at any time. Any employment is subject to a written contract and to Nepali law.</p>
<h2>Prices</h2>
<p>Prices shown are in US dollars, are indicative and may change. The price stated in your signed agreement is the price that applies to you.</p>
<h2>Targets and standards</h2>
<p>Performance targets described on this website are the standards we commit to in engagements. Specific targets for your engagement are agreed in writing during scoping, and those agreed targets are the ones that apply.</p>
<h2>Not professional advice</h2>
<p>Content on this website, including articles about legal developments affecting freight brokers, is general commentary and is not legal, tax, customs or financial advice. Kamship staff are not licensed customs brokers, lawyers or accountants. Take your own professional advice before acting.</p>
<h2>Third-party names</h2>
<p>Software and platform names appearing on this website are the property of their respective owners. Their use describes systems our teams are trained to work in and does not imply partnership, endorsement or certification by those owners.</p>
<h2>External links</h2>
<p>We are not responsible for the content of websites we link to.</p>
<h2>Intellectual property</h2>
<p>The content and design of this website belong to <?= e((string) c('site.company')) ?>. You may read, print and share it for your own business purposes, but not republish it as your own.</p>
<h2>Liability</h2>
<p>We take care to keep this website accurate but do not guarantee it is free from error or always available. To the extent permitted by law, we are not liable for loss arising from reliance on website content. Nothing here limits liability that cannot lawfully be limited.</p>
<h2>Governing law</h2>
<p>These terms are governed by the laws of Nepal. Client agreements may specify a different governing law, agreed with each client.</p>
<h2>Contact</h2>
<p><a href="mailto:<?= e((string) c('site.email')) ?>"><?= e((string) c('site.email')) ?></a> &middot; <?= e((string) c('site.phone_display')) ?></p>
</div></section>
<?php ks_footer();
