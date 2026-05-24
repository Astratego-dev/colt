<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$service_key = sanitize_html_class($service_page['key'] ?? 'singles');
$service_title = (string) ($service_page['title'] ?? '');
$service_nav_label = (string) ($service_page['nav_label'] ?? $service_title);
$hero_id = 'service-' . $service_key . '-hero';
$brief_id = 'service-' . $service_key . '-brief';
$process_id = 'service-' . $service_key . '-process';
$detail_id = 'service-' . $service_key . '-detail';
$contact_id = 'service-' . $service_key . '-contact';
?>

<section
    class="colt-xp colt-service-page colt-service-page--<?php echo esc_attr($service_key); ?>"
    dir="rtl"
    data-colt-xp
    data-service-page
    data-service-key="<?php echo esc_attr($service_key); ?>"
    data-version="1.9.0"
>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-service-page__nav" aria-label="<?php echo esc_attr($service_nav_label); ?>">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#<?php echo esc_attr($hero_id); ?>">התחלה</a>
            <a href="#<?php echo esc_attr($brief_id); ?>">מה מקבלים</a>
            <a href="#<?php echo esc_attr($process_id); ?>">תהליך</a>
            <a href="#<?php echo esc_attr($contact_id); ?>">פנייה</a>
        </div>
    </nav>

    <section class="colt-service-page__hero" id="<?php echo esc_attr($hero_id); ?>" data-service-hero>
        <div class="colt-service-page__aura" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>
        <div class="colt-service-page__cards" aria-hidden="true" data-service-cards>
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="colt-service-page__guardian" aria-hidden="true" data-service-guardian>
            <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="eager">
        </div>

        <div class="colt-service-page__hero-copy" data-service-hero-copy>
            <p class="colt-kicker"><?php echo esc_html($service_page['eyebrow']); ?></p>
            <h1><?php echo esc_html($service_title); ?></h1>
            <p><?php echo esc_html($service_page['lead']); ?></p>
            <div class="colt-service-page__actions">
                <a href="<?php echo esc_url($service_page['primary_url']); ?>"><?php echo esc_html($service_page['primary_label']); ?></a>
                <a href="<?php echo esc_url($service_page['secondary_url']); ?>"><?php echo esc_html($service_page['secondary_label']); ?></a>
            </div>
        </div>

        <div class="colt-service-page__metrics" aria-label="מאפייני שירות" data-service-metrics>
            <?php foreach ($service_page['metrics'] as $metric) : ?>
                <span>
                    <b><?php echo esc_html($metric['value']); ?></b>
                    <?php echo esc_html($metric['label']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-service-page__brief" id="<?php echo esc_attr($brief_id); ?>">
        <div class="colt-service-page__section-head" data-service-reveal>
            <p class="colt-kicker">SERVICE DESIGN</p>
            <h2><?php echo esc_html($service_page['intro_title']); ?></h2>
            <p><?php echo esc_html($service_page['intro_text']); ?></p>
        </div>

        <div class="colt-service-page__feature-grid">
            <?php foreach ($service_page['features'] as $feature) : ?>
                <article class="colt-service-page__feature" data-service-reveal>
                    <small><?php echo esc_html($feature['meta']); ?></small>
                    <strong><?php echo esc_html($feature['title']); ?></strong>
                    <p><?php echo esc_html($feature['text']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-service-page__process" id="<?php echo esc_attr($process_id); ?>">
        <div class="colt-service-page__process-orbit" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>
        <div class="colt-service-page__section-head" data-service-reveal>
            <p class="colt-kicker">THE FLOW</p>
            <h2>מהרגע הראשון ועד הפעולה הבאה.</h2>
            <p>כל שירות מקבל מסלול ברור, כדי שהלקוח יבין מה שולחים, מה מקבלים, מה קורה אחרי הפנייה ואיפה הערך האמיתי של COLT נכנס לתמונה.</p>
        </div>

        <div class="colt-service-page__steps">
            <?php foreach ($service_page['process'] as $step) : ?>
                <article class="colt-service-page__step" data-service-reveal>
                    <small><?php echo esc_html($step['step']); ?></small>
                    <strong><?php echo esc_html($step['title']); ?></strong>
                    <p><?php echo esc_html($step['text']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-service-page__detail" id="<?php echo esc_attr($detail_id); ?>">
        <div class="colt-service-page__detail-panel" data-service-reveal>
            <p class="colt-kicker">DETAILS</p>
            <h2>הדברים הקטנים שמייצרים אמון.</h2>
            <ul>
                <?php foreach ($service_page['details'] as $detail) : ?>
                    <li><?php echo esc_html($detail); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="colt-service-page__fit" data-service-reveal>
            <p class="colt-kicker">BEST FIT</p>
            <h3>למי זה מתאים?</h3>
            <?php foreach ($service_page['fit'] as $fit_item) : ?>
                <span><?php echo esc_html($fit_item); ?></span>
            <?php endforeach; ?>
            <em><?php echo esc_html($service_page['note']); ?></em>
        </div>
    </section>

    <section class="colt-service-page__contact" id="<?php echo esc_attr($contact_id); ?>">
        <div class="colt-service-page__contact-card" data-service-reveal>
            <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
            <div>
                <p class="colt-kicker">START WITH COLT</p>
                <h2><?php echo esc_html($service_page['primary_label']); ?></h2>
                <p>שלח לנו כמה פרטים, תמונות או כיוון כללי. משם נבנה לך את הצעד הבא בצורה מסודרת, נקייה ועם תשומת לב לפרטים הקטנים.</p>
            </div>
            <div class="colt-service-page__contact-actions">
                <a href="<?php echo esc_url($service_page['primary_url']); ?>"><?php echo esc_html($service_page['primary_label']); ?></a>
                <a href="<?php echo esc_url($service_page['secondary_url']); ?>"><?php echo esc_html($service_page['secondary_label']); ?></a>
            </div>
        </div>
    </section>
</section>
