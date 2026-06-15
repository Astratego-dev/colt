<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$product_url = !empty($atts['product_url']) ? (string) $atts['product_url'] : home_url('/shop/');
$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$whatsapp_url = !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : $contact_url;
$mystery_content = Colt_Experience::special_page('mystery');
$price = $mystery_content['price'] ?? '250 ש"ח';
$contents = $mystery_content['contents'] ?? [];
$worlds = $mystery_content['worlds'] ?? [];
$languages = $mystery_content['languages'] ?? [];
$rail_items = $mystery_content['rail'] ?? [];
$stream_labels = array_values($mystery_content['stream_labels'] ?? []);
$showcase_labels = array_values($mystery_content['showcase_labels'] ?? []);
?>

<section
    class="colt-xp colt-mystery-xp"
    dir="rtl"
    data-colt-xp
    data-mystery-xp
    data-version="1.9.5"
>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-mystery-nav" aria-label="<?php echo esc_attr($mystery_content['nav_label'] ?? 'COLT Mystery Box'); ?>">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#mystery-open"><?php echo esc_html($mystery_content['nav_open_label'] ?? 'הפתיחה'); ?></a>
            <a href="#mystery-contents"><?php echo esc_html($mystery_content['nav_contents_label'] ?? 'מה בפנים'); ?></a>
            <a href="#mystery-options"><?php echo esc_html($mystery_content['nav_options_label'] ?? 'בחירה'); ?></a>
            <a href="#mystery-buy"><?php echo esc_html($mystery_content['nav_buy_label'] ?? 'רכישה'); ?></a>
        </div>
    </nav>

    <section class="colt-mystery-hero" id="mystery-open" data-mystery-hero>
        <div class="colt-mystery-hero__pin">
            <div class="colt-mystery-hero__backdrop" data-mystery-hero-bg aria-hidden="true"></div>
            <div class="colt-mystery-orbit" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-box" data-mystery-box aria-hidden="true">
                <span class="colt-mystery-box__shadow"></span>
                <span class="colt-mystery-box__tray"></span>
                <span class="colt-mystery-box__lid"></span>
                <span class="colt-mystery-box__core"></span>
                <span class="colt-mystery-box__seal"></span>
                <span class="colt-mystery-box__glow"></span>
            </div>
            <div class="colt-mystery-stream" data-mystery-stream aria-hidden="true">
                <span class="colt-mystery-stream__slab"><i></i><em><?php echo esc_html($stream_labels[0]['label'] ?? 'Graded'); ?></em></span>
                <span class="colt-mystery-stream__booster"><i></i><em><?php echo esc_html($stream_labels[1]['label'] ?? 'Booster'); ?></em></span>
                <span class="colt-mystery-stream__single"><i></i><em><?php echo esc_html($stream_labels[2]['label'] ?? 'Single'); ?></em></span>
            </div>
            <div class="colt-mystery-foil" data-mystery-foil aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-mystery-hero__copy" data-mystery-hero-copy>
                <p class="colt-kicker"><?php echo esc_html($mystery_content['hero_kicker'] ?? ''); ?></p>
                <h1><?php echo esc_html($mystery_content['hero_title'] ?? ''); ?></h1>
                <p><?php echo esc_html($mystery_content['hero_text'] ?? ''); ?></p>
                <div class="colt-mystery-price">
                    <span><?php echo esc_html($price); ?></span>
                    <small><?php echo esc_html($mystery_content['hero_meta'] ?? ''); ?></small>
                </div>
            </div>

            <div class="colt-mystery-hero__rail" data-mystery-rail aria-hidden="true">
                <?php foreach ($rail_items as $item) : ?>
                    <span><?php echo esc_html($item['label'] ?? ''); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-mystery-reveal" id="mystery-contents" data-mystery-reveal>
        <div class="colt-mystery-reveal__pin">
            <div class="colt-mystery-reveal__backdrop" data-mystery-reveal-bg aria-hidden="true"></div>
            <div class="colt-mystery-reveal__burst" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-rip" data-mystery-rip aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="colt-mystery-showcase" data-mystery-showcase aria-hidden="true">
                <span class="colt-mystery-showcase__slab">
                    <i></i>
                    <em><?php echo esc_html($showcase_labels[0]['label'] ?? 'GRADED'); ?></em>
                </span>
                <span class="colt-mystery-showcase__booster">
                    <i></i>
                    <em><?php echo esc_html($showcase_labels[1]['label'] ?? 'BOOSTER'); ?></em>
                </span>
                <span class="colt-mystery-showcase__single">
                    <i></i>
                    <em><?php echo esc_html($showcase_labels[2]['label'] ?? 'SINGLE'); ?></em>
                </span>
            </div>

            <div class="colt-mystery-reveal__copy" data-mystery-reveal-copy>
                <p class="colt-kicker"><?php echo esc_html($mystery_content['reveal_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($mystery_content['reveal_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($mystery_content['reveal_text'] ?? ''); ?></p>
            </div>

            <div class="colt-mystery-items" aria-label="תכולת המיסטרי בוקס">
                <?php foreach ($contents as $index => $item) : ?>
                    <article class="colt-mystery-item" data-mystery-item style="--i: <?php echo esc_attr((string) $index); ?>">
                        <small><?php echo esc_html($item['meta']); ?></small>
                        <strong><?php echo esc_html($item['title']); ?></strong>
                        <p><?php echo esc_html($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-mystery-options" id="mystery-options" data-mystery-options>
        <div class="colt-mystery-options__shell">
            <div class="colt-mystery-options__copy" data-mystery-options-copy>
                <p class="colt-kicker"><?php echo esc_html($mystery_content['options_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($mystery_content['options_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($mystery_content['options_text'] ?? ''); ?></p>
            </div>

            <div class="colt-mystery-picker" data-mystery-picker data-product-url="<?php echo esc_url($product_url); ?>">
                <div class="colt-mystery-picker__group" aria-label="בחירת עולם">
                    <small><?php echo esc_html($mystery_content['world_group_label'] ?? 'עולם'); ?></small>
                    <?php foreach ($worlds as $index => $world) : ?>
                        <button type="button" data-mystery-choice="world" data-value="<?php echo esc_attr($world['value']); ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>">
                            <span><?php echo esc_html($world['label']); ?></span>
                            <em><?php echo esc_html($world['text']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="colt-mystery-picker__group" aria-label="בחירת שפה">
                    <small><?php echo esc_html($mystery_content['language_group_label'] ?? 'שפה'); ?></small>
                    <?php foreach ($languages as $index => $language) : ?>
                        <button type="button" data-mystery-choice="language" data-value="<?php echo esc_attr($language['value']); ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>">
                            <span><?php echo esc_html($language['label']); ?></span>
                            <em><?php echo esc_html($language['text']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="colt-mystery-summary" data-mystery-summary>
                <small><?php echo esc_html($mystery_content['summary_label'] ?? ''); ?></small>
                <strong><span data-mystery-summary-world><?php echo esc_html($mystery_content['summary_world_default'] ?? 'Pokemon'); ?></span> · <span data-mystery-summary-language><?php echo esc_html($mystery_content['summary_language_default'] ?? 'English'); ?></span></strong>
                <em><?php echo esc_html($price); ?></em>
            </div>
        </div>
    </section>

    <section class="colt-mystery-buy" id="mystery-buy" data-mystery-buy>
        <div class="colt-mystery-buy__shell">
            <div class="colt-mystery-buy__brand" data-mystery-buy-brand>
                <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
                <div>
                    <p class="colt-kicker"><?php echo esc_html($mystery_content['buy_kicker'] ?? ''); ?></p>
                    <h2><?php echo esc_html($mystery_content['buy_title'] ?? ''); ?></h2>
                    <p><?php echo esc_html($mystery_content['buy_text'] ?? ''); ?></p>
                </div>
            </div>

            <div class="colt-mystery-buy__panel" data-mystery-buy-panel>
                <a href="<?php echo esc_url($product_url); ?>" data-mystery-checkout><?php echo esc_html($mystery_content['buy_primary'] ?? ''); ?></a>
                <a href="<?php echo esc_url($whatsapp_url); ?>"><?php echo esc_html($mystery_content['buy_secondary'] ?? ''); ?></a>
                <a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($mystery_content['buy_third'] ?? ''); ?></a>
            </div>
        </div>
    </section>
</section>
