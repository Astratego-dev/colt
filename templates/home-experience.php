<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_limit = isset($atts['products']) ? (int) $atts['products'] : 12;
$home_content = Colt_Experience::home_page();
$home_nav_links = $home_content['nav_links'] ?? [];
$services = Colt_Experience::services();
$core_services = array_values(array_filter($services, static function ($service) {
    return isset($service['group']) && $service['group'] === 'core';
}));
$support_services = array_values(array_filter($services, static function ($service) {
    return !isset($service['group']) || $service['group'] !== 'core';
}));
$products = Colt_Experience::featured_products($product_limit);
$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$product_showcase = !empty($products) ? $products : ($home_content['fallback_products'] ?? []);
$origin_world_url = Colt_Experience::asset_url('assets/scene-01-origin/colt-origin-world-clean.png');
$origin_frame_base_url = Colt_Experience::asset_url('assets/scene-01-origin/sequence/frame_');
$origin_frame_poster_url = Colt_Experience::asset_url('assets/scene-01-origin/sequence/frame_0000.webp');
$orbit_guardian_url = Colt_Experience::asset_url('assets/scene-01-origin/guardian-frame-01.png');
$orbit_model_urls = [
    Colt_Experience::asset_url('assets/models/colt-planet-saturnus.glb'),
    Colt_Experience::asset_url('assets/models/colt-planet-astronomy.glb'),
    Colt_Experience::asset_url('assets/models/colt-planet-yellow.glb'),
];
$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$product_categories = $home_content['product_categories'] ?? [];
$product_ticker = $home_content['product_ticker'] ?? [];
$origin_rail = $home_content['origin_rail'] ?? [];
$origin_chapters = array_values($home_content['origin_chapters'] ?? []);
$origin_metrics = $home_content['origin_metrics'] ?? [];
$social_links = $home_content['social_links'] ?? [];
$social_overrides = [
    'instagram' => !empty($atts['instagram']) ? (string) $atts['instagram'] : '',
    'tiktok' => !empty($atts['tiktok']) ? (string) $atts['tiktok'] : '',
    'whatnot' => !empty($atts['whatnot']) ? (string) $atts['whatnot'] : '',
    'whatsapp' => !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : '',
];
foreach ($social_links as $index => $social) {
    $tone = isset($social['tone']) ? (string) $social['tone'] : '';
    if ($tone !== '' && !empty($social_overrides[$tone])) {
        $social_links[$index]['url'] = $social_overrides[$tone];
    }
}
?>

<section class="colt-xp" dir="rtl" data-colt-xp data-version="2.0.6">
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav" aria-label="COLT">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <?php foreach ($home_nav_links as $link) : ?>
                <a href="<?php echo esc_url($link['url'] ?? '#'); ?>"><?php echo esc_html($link['label'] ?? ''); ?></a>
            <?php endforeach; ?>
        </div>
    </nav>

    <section
        class="colt-origin"
        data-origin-sequence
        data-colt-scene="origin"
        style="<?php echo esc_attr("--colt-origin-world: url(" . esc_url($origin_world_url) . ");"); ?>"
    >
        <div class="colt-origin__pin">
            <canvas
                class="colt-origin__sequence"
                data-origin-canvas
                data-frame-base="<?php echo esc_url($origin_frame_base_url); ?>"
                data-frame-count="180"
                data-frame-pad="4"
                data-frame-ext="webp"
                aria-hidden="true"
            ></canvas>
            <img
                class="colt-origin__poster"
                src="<?php echo esc_url($origin_frame_poster_url); ?>"
                alt=""
                loading="eager"
                decoding="sync"
                aria-hidden="true"
            >
            <div class="colt-origin__world" aria-hidden="true"></div>
            <div class="colt-origin__atmosphere" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="colt-origin__sky" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="colt-origin__rail" aria-label="ניווט פתיחת COLT">
                <p><?php echo esc_html($home_content['origin_rail_title'] ?? ''); ?></p>
                <?php foreach ($origin_rail as $item) : ?>
                    <a href="<?php echo esc_url($item['url'] ?? '#'); ?>" data-origin-step="<?php echo esc_attr($item['step'] ?? '0'); ?>"><?php echo esc_html($item['label'] ?? ''); ?></a>
                <?php endforeach; ?>
            </div>

            <a class="colt-origin__service-tab" href="#colt-core"><?php echo esc_html($home_content['origin_tab_label'] ?? ''); ?></a>

            <div class="colt-origin__copy">
                <?php foreach ($origin_chapters as $index => $chapter) : ?>
                    <div class="colt-origin__chapter" data-origin-chapter="<?php echo esc_attr((string) $index); ?>">
                        <p class="colt-kicker"><?php echo esc_html($chapter['kicker'] ?? ''); ?></p>
                        <?php if ($index === 0) : ?>
                            <h1><?php echo esc_html($chapter['title'] ?? ''); ?></h1>
                        <?php else : ?>
                            <h2><?php echo esc_html($chapter['title'] ?? ''); ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($chapter['text'])) : ?>
                            <p><?php echo esc_html($chapter['text']); ?></p>
                        <?php endif; ?>
                        <?php if ($index === 1 && !empty($origin_metrics)) : ?>
                            <div class="colt-origin__metrics" aria-label="<?php echo esc_attr($home_content['core_aria_label'] ?? 'שירותי COLT מרכזיים'); ?>">
                                <?php foreach ($origin_metrics as $metric) : ?>
                                    <span><b><?php echo esc_html($metric['value'] ?? ''); ?></b><?php echo esc_html($metric['label'] ?? ''); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($chapter['primary_label']) || !empty($chapter['secondary_label'])) : ?>
                            <div class="colt-origin__actions">
                                <?php if (!empty($chapter['primary_label'])) : ?>
                                    <a href="<?php echo esc_url($chapter['primary_url'] ?? '#'); ?>"><?php echo esc_html($chapter['primary_label']); ?></a>
                                <?php endif; ?>
                                <?php if (!empty($chapter['secondary_label'])) : ?>
                                    <a href="<?php echo esc_url($chapter['secondary_url'] ?? '#'); ?>"><?php echo esc_html($chapter['secondary_label']); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="colt-origin__label" aria-hidden="true">
                <span><?php echo esc_html($home_content['origin_scroll_label'] ?? ''); ?></span>
                <b></b>
            </div>
        </div>
    </section>

    <section class="colt-core-world" id="colt-core" data-core-world data-colt-scene="core">
        <div class="colt-core-world__pin">
            <div class="colt-core-world__garden" aria-hidden="true">
                <span class="colt-core-world__sun"></span>
                <span class="colt-core-world__river"></span>
            </div>

            <div class="colt-core-world__rings" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-core-world__bloom" aria-hidden="true">
                <?php for ($petal = 0; $petal < 18; $petal++) : ?>
                    <span></span>
                <?php endfor; ?>
            </div>

            <div class="colt-core-world__copy" data-core-copy>
                <p class="colt-kicker"><?php echo esc_html($home_content['core_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($home_content['core_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($home_content['core_text'] ?? ''); ?></p>
            </div>

            <div class="colt-core-world__services" aria-label="<?php echo esc_attr($home_content['core_aria_label'] ?? ''); ?>">
                <?php foreach ($core_services as $index => $service) : ?>
                    <a
                        class="colt-core-world__card colt-core-world__card--<?php echo esc_attr($service['tone']); ?>"
                        data-core-card
                        href="<?php echo esc_url($service['url']); ?>"
                        style="--i: <?php echo esc_attr((string) $index); ?>"
                    >
                        <span class="colt-core-world__number">0<?php echo esc_html((string) ($index + 1)); ?></span>
                        <span class="colt-core-world__visual" aria-hidden="true"><i></i><b></b></span>
                        <small><?php echo esc_html($service['eyebrow']); ?></small>
                        <strong><?php echo esc_html($service['title']); ?></strong>
                        <em><?php echo esc_html($service['text']); ?></em>
                        <span class="colt-core-world__cta"><?php echo esc_html($home_content['core_cta_label'] ?? ''); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="colt-core-world__progress" aria-hidden="true">
                <span data-core-dot="0"></span>
                <span data-core-dot="1"></span>
                <span data-core-dot="2"></span>
                <span data-core-dot="3"></span>
            </div>

            <div class="colt-core-world__aperture" aria-hidden="true"></div>
        </div>
    </section>

    <section class="colt-orbit" id="colt-services" data-orbit-world data-colt-scene="orbit">
        <div class="colt-orbit__pin">
            <div
                class="colt-orbit__three"
                data-orbit-three
                data-model-primary="<?php echo esc_url($orbit_model_urls[0]); ?>"
                data-model-secondary="<?php echo esc_url($orbit_model_urls[1]); ?>"
                data-model-tertiary="<?php echo esc_url($orbit_model_urls[2]); ?>"
                aria-hidden="true"
            ></div>

            <div class="colt-orbit__space" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-orbit__copy" data-orbit-copy>
                <p class="colt-kicker"><?php echo esc_html($home_content['orbit_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($home_content['orbit_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($home_content['orbit_text'] ?? ''); ?></p>
            </div>

            <div class="colt-orbit__system" aria-label="<?php echo esc_attr($home_content['orbit_aria_label'] ?? ''); ?>">
                <div class="colt-orbit__rings" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>

                <div class="colt-orbit__guardian-wrap" aria-hidden="true">
                    <img class="colt-orbit__guardian" src="<?php echo esc_url($orbit_guardian_url); ?>" alt="" loading="lazy">
                    <span></span>
                </div>

                <?php foreach ($support_services as $index => $service) : ?>
                    <a
                        class="colt-orbit__planet colt-orbit__planet--<?php echo esc_attr($service['tone']); ?>"
                        data-orbit-planet
                        data-hyperspace-link
                        href="<?php echo esc_url($service['url']); ?>"
                        style="--i: <?php echo esc_attr((string) $index); ?>"
                    >
                        <span class="colt-orbit__planet-body" aria-hidden="true"><i></i><b></b></span>
                        <span class="colt-orbit__planet-copy">
                            <small>0<?php echo esc_html((string) ($index + 1)); ?> / <?php echo esc_html($service['eyebrow']); ?></small>
                            <strong><?php echo esc_html($service['title']); ?></strong>
                            <em><?php echo esc_html($service['text']); ?></em>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="colt-orbit__dock" aria-hidden="true">
                <?php foreach (($home_content['orbit_dock'] ?? []) as $dock_item) : ?>
                    <span><?php echo esc_html(is_array($dock_item) ? ($dock_item['label'] ?? '') : $dock_item); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-products" data-product-world data-colt-scene="products">
            <div class="colt-products__pin">
                <div class="colt-products__sky" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>

                <div class="colt-products__intro" data-product-intro>
                    <p class="colt-kicker"><?php echo esc_html($home_content['products_kicker'] ?? ''); ?></p>
                    <h2><?php echo esc_html($home_content['products_title'] ?? ''); ?></h2>
                    <p><?php echo esc_html($home_content['products_text'] ?? ''); ?></p>
                </div>

                <div class="colt-products__categories" data-product-categories aria-label="<?php echo esc_attr($home_content['products_aria_label'] ?? ''); ?>">
                    <?php foreach ($product_categories as $index => $category) : ?>
                        <a
                            class="colt-products__category colt-products__category--<?php echo esc_attr($category['tone']); ?>"
                            href="<?php echo esc_url($category['url']); ?>"
                            style="--i: <?php echo esc_attr((string) $index); ?>"
                        >
                            <span><?php echo esc_html($category['label']); ?></span>
                            <em><?php echo esc_html($category['detail']); ?></em>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="colt-products__viewport" data-product-viewport>
                    <div class="colt-products__track" data-product-track>
                        <?php foreach ($product_showcase as $index => $product) : ?>
                            <a
                                class="colt-product-card"
                                data-product-card
                                href="<?php echo esc_url($product['url']); ?>"
                                style="--i: <?php echo esc_attr((string) $index); ?>"
                            >
                                <span class="colt-product-card__media">
                                    <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>" loading="lazy">
                                </span>
                                <span class="colt-product-card__body">
                                    <small><?php echo esc_html($home_content['product_drop_prefix'] ?? 'DROP'); ?> 0<?php echo esc_html((string) ($index + 1)); ?></small>
                                    <strong><?php echo esc_html($product['title']); ?></strong>
                                    <?php if (!empty($product['price'])) : ?>
                                        <em><?php echo wp_kses_post($product['price']); ?></em>
                                    <?php endif; ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="colt-products__ticker" aria-hidden="true">
                    <?php foreach ($product_ticker as $ticker_item) : ?>
                        <span><?php echo esc_html(is_array($ticker_item) ? ($ticker_item['label'] ?? '') : $ticker_item); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <section class="colt-finale" id="colt-contact" data-finale data-colt-scene="end">
        <div class="colt-finale__pin">
            <div class="colt-finale__garden" aria-hidden="true">
                <span class="colt-finale__sun"></span>
                <span class="colt-finale__river"></span>
                <span class="colt-finale__moon"></span>
            </div>

            <div class="colt-finale__petals" aria-hidden="true">
                <?php for ($petal = 0; $petal < 24; $petal++) : ?>
                    <span></span>
                <?php endfor; ?>
            </div>

            <div class="colt-finale__halo" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>

            <div class="colt-finale__brand" data-finale-brand>
                <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
                <p class="colt-kicker"><?php echo esc_html($home_content['finale_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($home_content['finale_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($home_content['finale_text'] ?? ''); ?></p>
            </div>

            <div class="colt-finale__contact" data-finale-contact>
                <div>
                    <p class="colt-kicker"><?php echo esc_html($home_content['finale_contact_kicker'] ?? ''); ?></p>
                    <h3><?php echo esc_html($home_content['finale_contact_title'] ?? ''); ?></h3>
                    <p><?php echo esc_html($home_content['finale_contact_text'] ?? ''); ?></p>
                </div>
                <div class="colt-finale__actions">
                    <a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($home_content['finale_contact_primary'] ?? ''); ?></a>
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>"><?php echo esc_html($home_content['finale_contact_secondary'] ?? ''); ?></a>
                </div>
            </div>

            <div class="colt-finale__socials" data-finale-socials aria-label="<?php echo esc_attr($home_content['finale_social_aria_label'] ?? ''); ?>">
                <p class="colt-kicker"><?php echo esc_html($home_content['finale_social_kicker'] ?? ''); ?></p>
                <?php foreach ($social_links as $index => $social) : ?>
                    <a
                        class="colt-finale__social colt-finale__social--<?php echo esc_attr($social['tone']); ?>"
                        href="<?php echo esc_url($social['url']); ?>"
                        style="--i: <?php echo esc_attr((string) $index); ?>"
                    >
                        <span><?php echo esc_html($social['label']); ?></span>
                        <em><?php echo esc_html($social['detail']); ?></em>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="colt-hyperspace" data-hyperspace aria-hidden="true">
        <?php for ($ray = 0; $ray < 32; $ray++) : ?>
            <span style="--angle: <?php echo esc_attr((string) ($ray * 11.25)); ?>deg; --delay: -<?php echo esc_attr((string) ($ray * 8)); ?>ms;"></span>
        <?php endfor; ?>
    </div>
</section>
