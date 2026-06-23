<?php
if (!defined('ABSPATH')) {
    exit;
}

$content = Colt_Experience::special_page('mystery-product');
$logo_url = Colt_Experience::logo_url();
$product_url = !empty($atts['product_url']) ? (string) $atts['product_url'] : home_url('/shop/');
$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$whatsapp_url = !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : $contact_url;
$price = $content['price'] ?? '299 ש"ח';
$ledger = $content['ledger'] ?? [];
$contents = $content['contents'] ?? [];
$features = $content['features'] ?? [];
$worlds = $content['worlds'] ?? [];
$steps = $content['steps'] ?? [];
$hero_cards = $content['hero_cards'] ?? [];
$hero_box_image = (string) ($content['hero_box_image'] ?? '');
$hero_box_alt = (string) ($content['hero_box_alt'] ?? '');
?>

<section class="colt-xp colt-mystery-product" dir="rtl" data-colt-xp data-version="2.0.9">
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-mystery-product__nav" aria-label="<?php echo esc_attr($content['nav_label'] ?? 'COLT Mystery Box'); ?>">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#mystery-product-hero"><?php echo esc_html($content['nav_open_label'] ?? 'המוצר'); ?></a>
            <a href="#mystery-product-inside"><?php echo esc_html($content['nav_contents_label'] ?? 'מה מקבלים'); ?></a>
            <a href="#mystery-product-auth"><?php echo esc_html($content['nav_options_label'] ?? 'מקוריות'); ?></a>
            <a href="#mystery-product-buy"><?php echo esc_html($content['nav_buy_label'] ?? 'הזמנה'); ?></a>
        </div>
    </nav>

    <section class="colt-mystery-product__hero" id="mystery-product-hero">
        <div class="colt-mystery-product__hero-copy">
            <p class="colt-kicker"><?php echo esc_html($content['hero_kicker'] ?? ''); ?></p>
            <h1><?php echo esc_html($content['hero_title'] ?? ''); ?></h1>
            <p><?php echo esc_html($content['hero_text'] ?? ''); ?></p>
            <div class="colt-mystery-product__price">
                <span><?php echo esc_html($price); ?></span>
                <small><?php echo esc_html($content['hero_meta'] ?? ''); ?></small>
            </div>
            <div class="colt-mystery-product__actions">
                <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($content['hero_primary'] ?? 'רכישה'); ?></a>
                <a href="<?php echo esc_url($whatsapp_url); ?>"><?php echo esc_html($content['hero_secondary'] ?? 'שאלה לפני רכישה'); ?></a>
            </div>
        </div>

        <div class="colt-mystery-product__visual" aria-hidden="true">
            <div class="colt-mystery-product__box <?php echo $hero_box_image !== '' ? 'has-image' : ''; ?>">
                <?php if ($hero_box_image !== '') : ?>
                    <img class="colt-mystery-product__box-image" src="<?php echo esc_url($hero_box_image); ?>" alt="<?php echo esc_attr($hero_box_alt); ?>" loading="eager">
                <?php else : ?>
                    <span class="colt-mystery-product__lid"></span>
                    <span class="colt-mystery-product__body">
                        <strong>COLT</strong>
                        <em>MYSTERY BOX</em>
                    </span>
                <?php endif; ?>
                <span class="colt-mystery-product__seal"><?php echo esc_html($price); ?></span>
            </div>
            <?php foreach ($hero_cards as $index => $card) : ?>
                <?php
                $fallback_modifiers = ['slab', 'pack', 'single'];
                $modifier_value = (string) ($card['modifier'] ?? '');
                $modifier = sanitize_html_class($modifier_value !== '' ? $modifier_value : ($fallback_modifiers[$index] ?? 'single'));
                $card_image = (string) ($card['image'] ?? '');
                $card_label = (string) ($card['label'] ?? '');
                $card_alt = (string) ($card['alt'] ?? $card_label);
                ?>
                <span class="colt-mystery-product__pull colt-mystery-product__pull--<?php echo esc_attr($modifier); ?> <?php echo $card_image !== '' ? 'has-image' : ''; ?>">
                    <?php if ($card_image !== '') : ?>
                        <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card_alt); ?>" loading="eager">
                    <?php else : ?>
                        <i></i>
                    <?php endif; ?>
                    <b><?php echo esc_html($card_label); ?></b>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="colt-mystery-product__ledger" aria-label="נתוני מיסטרי בוקס">
            <?php foreach ($ledger as $item) : ?>
                <span>
                    <b><?php echo esc_html($item['value'] ?? ''); ?></b>
                    <?php echo esc_html($item['label'] ?? ''); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-mystery-product__inside" id="mystery-product-inside">
        <div class="colt-mystery-product__section-head">
            <p class="colt-kicker"><?php echo esc_html($content['inside_kicker'] ?? ''); ?></p>
            <h2><?php echo esc_html($content['inside_title'] ?? ''); ?></h2>
            <p><?php echo esc_html($content['inside_text'] ?? ''); ?></p>
        </div>

        <div class="colt-mystery-product__items">
            <?php foreach ($contents as $index => $item) : ?>
                <article class="colt-mystery-product__item" style="--i: <?php echo esc_attr((string) $index); ?>">
                    <span class="colt-mystery-product__item-media" aria-hidden="<?php echo empty($item['image']) ? 'true' : 'false'; ?>">
                        <?php if (!empty($item['image'])) : ?>
                            <img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) ($item['alt'] ?? $item['title'] ?? '')); ?>" loading="lazy">
                        <?php endif; ?>
                    </span>
                    <small><?php echo esc_html($item['meta'] ?? ''); ?></small>
                    <strong><?php echo esc_html($item['title'] ?? ''); ?></strong>
                    <p><?php echo esc_html($item['text'] ?? ''); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-mystery-product__auth" id="mystery-product-auth">
        <div class="colt-mystery-product__promise">
            <p class="colt-kicker"><?php echo esc_html($content['reveal_kicker'] ?? ''); ?></p>
            <h2><?php echo esc_html($content['reveal_title'] ?? ''); ?></h2>
            <p><?php echo esc_html($content['reveal_text'] ?? ''); ?></p>
        </div>

        <div class="colt-mystery-product__worlds">
            <p class="colt-kicker"><?php echo esc_html($content['options_kicker'] ?? ''); ?></p>
            <h2><?php echo esc_html($content['options_title'] ?? ''); ?></h2>
            <p><?php echo esc_html($content['options_text'] ?? ''); ?></p>
            <div class="colt-mystery-product__world-grid" aria-label="עולמות אספנות">
                <?php foreach ($worlds as $world) : ?>
                    <span>
                        <strong><?php echo esc_html($world['label'] ?? ''); ?></strong>
                        <em><?php echo esc_html($world['text'] ?? ''); ?></em>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-mystery-product__chase">
        <div class="colt-mystery-product__feature-grid">
            <?php foreach ($features as $feature) : ?>
                <article class="colt-mystery-product__feature">
                    <small><?php echo esc_html($feature['meta'] ?? ''); ?></small>
                    <strong><?php echo esc_html($feature['title'] ?? ''); ?></strong>
                    <p><?php echo esc_html($feature['text'] ?? ''); ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="colt-mystery-product__steps" aria-label="איך זה עובד">
            <?php foreach ($steps as $step) : ?>
                <article>
                    <small><?php echo esc_html($step['step'] ?? ''); ?></small>
                    <strong><?php echo esc_html($step['title'] ?? ''); ?></strong>
                    <p><?php echo esc_html($step['text'] ?? ''); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="colt-mystery-product__buy" id="mystery-product-buy">
        <div>
            <p class="colt-kicker"><?php echo esc_html($content['buy_kicker'] ?? ''); ?></p>
            <h2><?php echo esc_html($content['buy_title'] ?? ''); ?></h2>
            <p><?php echo esc_html($content['buy_text'] ?? ''); ?></p>
        </div>
        <div class="colt-mystery-product__buy-actions">
            <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($content['buy_primary'] ?? 'רכישה עכשיו'); ?></a>
            <a href="<?php echo esc_url($whatsapp_url); ?>"><?php echo esc_html($content['buy_secondary'] ?? 'שאלה בוואטסאפ'); ?></a>
            <a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($content['buy_third'] ?? 'יצירת קשר'); ?></a>
        </div>
    </section>
</section>
