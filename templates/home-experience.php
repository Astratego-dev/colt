<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_limit = isset($atts['products']) ? (int) $atts['products'] : 8;
$services = Colt_Experience::services();
$products = Colt_Experience::featured_products($product_limit);
$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$guardian_url = Colt_Experience::asset_url('assets/img/colt-logo.png');
?>

<section class="colt-xp" dir="rtl" data-colt-xp>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__ambient" aria-hidden="true"></div>

    <nav class="colt-xp__nav" aria-label="COLT home experience">
        <a class="colt-xp__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-xp__nav-actions">
            <a href="#colt-world">העולם</a>
            <a href="#colt-services">שירותים</a>
            <a href="<?php echo esc_url(home_url('/shop/')); ?>">חנות</a>
            <a class="colt-xp__pill" href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">THE VAULT</a>
        </div>
    </nav>

    <header class="colt-xp__hero" id="colt-world" data-colt-scene="hero">
        <div class="colt-xp__hero-copy colt-reveal">
            <p class="colt-xp__kicker">COLLECT THE RARE. OWN THE LEGACY.</p>
            <h1>כניסה לעולם אספנות חי, יוקרתי ומדויק.</h1>
            <p class="colt-xp__lead">
                COLT היא לא רק חנות קלפים. זו מערכת שירותים לאספנים: מוצרים, איתור פריטים,
                דירוג, מכירה, כספת, תיקי אספנות ואירועים.
            </p>
            <div class="colt-xp__hero-actions">
                <a class="colt-xp__button colt-xp__button--primary" href="#colt-services">התחל את המסע</a>
                <a class="colt-xp__button colt-xp__button--ghost" href="<?php echo esc_url(home_url('/shop/')); ?>">כניסה לחנות</a>
            </div>
        </div>

        <div class="colt-xp__hero-stage" aria-hidden="true">
            <div class="colt-xp__portal" data-colt-portal></div>
            <img class="colt-xp__wordmark" src="<?php echo esc_url($mark_url); ?>" alt="">
            <img class="colt-xp__guardian" src="<?php echo esc_url($guardian_url); ?>" alt="">
            <div class="colt-xp__slab" data-colt-card>
                <span class="colt-xp__slab-top">COLT / VAULT READY</span>
                <span class="colt-xp__card-art"><i></i><b>10</b><small>Legacy Asset</small></span>
            </div>
        </div>

        <div class="colt-xp__scroll-cue" aria-hidden="true">
            <span></span>
            <em>גלול כדי להיכנס</em>
        </div>
    </header>

    <section class="colt-xp__cinema" data-colt-cinema data-colt-scene="cinema" aria-label="COLT cinematic collector journey">
        <div class="colt-xp__cinema-sticky">
            <div class="colt-xp__cinema-bg" aria-hidden="true">
                <span class="colt-xp__tunnel-ring colt-xp__tunnel-ring--one"></span>
                <span class="colt-xp__tunnel-ring colt-xp__tunnel-ring--two"></span>
                <span class="colt-xp__tunnel-ring colt-xp__tunnel-ring--three"></span>
                <span class="colt-xp__tunnel-glow"></span>
            </div>

            <div class="colt-xp__cinema-copy">
                <article class="colt-xp__cinema-panel is-active" data-cinema-panel="0">
                    <p class="colt-xp__kicker">THE ENTRY</p>
                    <h2>האספן נכנס דרך הקלף.</h2>
                    <p>החוויה מתחילה מפריט אחד שמקבל במה, אור, עומק ומשמעות.</p>
                </article>
                <article class="colt-xp__cinema-panel" data-cinema-panel="1">
                    <p class="colt-xp__kicker">THE VAULT</p>
                    <h2>הקלף עובר לתוך הכספת.</h2>
                    <p>שמירה, ביטוח, קטלוג וגישה הופכים מחלק טכני לרגע מותגי.</p>
                </article>
                <article class="colt-xp__cinema-panel" data-cinema-panel="2">
                    <p class="colt-xp__kicker">THE SYSTEM</p>
                    <h2>מתוך הכספת נפתחים המסלולים.</h2>
                    <p>חנות, חיפוש, דירוג, מכירה, השקעות ואירועים מחוברים למסע אחד.</p>
                </article>
                <article class="colt-xp__cinema-panel" data-cinema-panel="3">
                    <p class="colt-xp__kicker">CHOOSE A PATH</p>
                    <h2>מפה ממשיכים לשירות או מוצר.</h2>
                    <p>הסיפור מסתיים בהחלטה ברורה: לקנות, למכור, לשמור או לבנות אוסף.</p>
                </article>
            </div>

            <div class="colt-xp__cinema-stage" aria-hidden="true">
                <img class="colt-xp__cinema-guardian" src="<?php echo esc_url($guardian_url); ?>" alt="">
                <img class="colt-xp__cinema-wordmark" src="<?php echo esc_url($mark_url); ?>" alt="">
                <div class="colt-xp__cinema-card">
                    <span class="colt-xp__cinema-card-label">COLT / 10</span>
                    <b>RARE</b>
                    <i></i>
                </div>
                <div class="colt-xp__cinema-vault">
                    <span></span><span></span><span></span>
                    <strong>VAULT</strong>
                </div>
                <div class="colt-xp__cinema-paths">
                    <?php foreach (array_slice($services, 0, 6) as $index => $service) : ?>
                        <a href="<?php echo esc_url($service['url']); ?>" style="--i: <?php echo esc_attr((string) $index); ?>">
                            <small>0<?php echo esc_html((string) ($index + 1)); ?></small>
                            <b><?php echo esc_html($service['title']); ?></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="colt-xp__cinema-progress" aria-hidden="true">
                <span></span>
            </div>
        </div>
    </section>

    <div class="colt-xp__story-rail" aria-hidden="true">
        <span data-colt-rail></span>
    </div>

    <section class="colt-xp__chapter" data-colt-scene="commerce">
        <div class="colt-xp__chapter-copy colt-reveal">
            <p class="colt-xp__kicker">01 / STORE</p>
            <h2>החנות היא השער, לא כל הסיפור.</h2>
            <p>קלפים, בוקסים, אביזרים, סלאבים ומוצרים נדירים מוצגים כמו נכסי אספנות, לא כמו עוד קטלוג רגיל.</p>
        </div>
        <div class="colt-xp__floating-board colt-reveal">
            <span>SEALED</span><span>GRADED</span><span>SINGLES</span><span>ACCESSORIES</span>
        </div>
    </section>

    <section class="colt-xp__chapter colt-xp__chapter--reverse" data-colt-scene="vault">
        <div class="colt-xp__chapter-copy colt-reveal">
            <p class="colt-xp__kicker">02 / THE VAULT</p>
            <h2>פריטים יקרים צריכים להרגיש מוגנים עוד לפני שלוחצים.</h2>
            <p>הכספת מקבלת סצנה משלה: אבטחה, שמירה, ביטוח, קטלוג וגישה. תחושה של חדר פרטי לאספנים רציניים.</p>
            <a class="colt-xp__text-link" href="<?php echo esc_url(home_url('/services/the-vault/')); ?>">לעמוד הכספת</a>
        </div>
        <div class="colt-xp__vault-core colt-reveal" aria-hidden="true">
            <span></span><span></span><span></span>
            <b>VAULT</b>
        </div>
    </section>

    <section class="colt-xp__section colt-xp__section--services" id="colt-services" data-colt-scene="services">
        <div class="colt-xp__section-head colt-reveal">
            <p class="colt-xp__kicker">03 / SERVICES</p>
            <h2>בחר את המסלול שמתאים לאוסף שלך.</h2>
            <p>כל כרטיס שירות הוא שער לעמוד רגוע יותר, מפורט יותר, עם CTA ברור.</p>
        </div>

        <div class="colt-xp__services">
            <?php foreach ($services as $index => $service) : ?>
                <a class="colt-xp__service colt-reveal" style="--i: <?php echo esc_attr((string) $index); ?>" href="<?php echo esc_url($service['url']); ?>" data-tone="<?php echo esc_attr($service['tone']); ?>">
                    <img src="<?php echo esc_url($service['image']); ?>" alt="" loading="lazy">
                    <span class="colt-xp__service-index">0<?php echo esc_html((string) ($index + 1)); ?></span>
                    <span class="colt-xp__service-eyebrow"><?php echo esc_html($service['eyebrow']); ?></span>
                    <strong><?php echo esc_html($service['title']); ?></strong>
                    <em><?php echo esc_html($service['text']); ?></em>
                    <span class="colt-xp__service-arrow">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($products)) : ?>
        <section class="colt-xp__section" data-colt-scene="products">
            <div class="colt-xp__section-head colt-reveal">
                <p class="colt-xp__kicker">04 / DROPS</p>
                <h2>מהמסע אל המדף.</h2>
                <p>המוצרים נשארים WooCommerce, אבל מקבלים במה שמתאימה לעולם COLT.</p>
            </div>
            <div class="colt-xp__products">
                <?php foreach ($products as $product) : ?>
                    <a class="colt-xp__product colt-reveal" href="<?php echo esc_url($product['url']); ?>">
                        <span class="colt-xp__product-img">
                            <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                        </span>
                        <strong><?php echo esc_html($product['title']); ?></strong>
                        <?php if (!empty($product['price'])) : ?>
                            <span class="colt-xp__price"><?php echo wp_kses_post($product['price']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="colt-xp__closing colt-reveal" data-colt-scene="closing">
        <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
        <p class="colt-xp__kicker">COLT CLUB</p>
        <h2>האתר הופך ממקום שקונים בו למקום שנכנסים אליו.</h2>
        <div class="colt-xp__closing-actions">
            <a class="colt-xp__button colt-xp__button--primary" href="<?php echo esc_url(home_url('/shop/')); ?>">לקנות עכשיו</a>
            <a class="colt-xp__button colt-xp__button--ghost" href="<?php echo esc_url(home_url('/services/most-wanted/')); ?>">יש לי פריט למכור</a>
        </div>
    </section>
</section>
