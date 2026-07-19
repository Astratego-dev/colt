<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Colt_Experience
{
    private static $instance = null;
    private $assets_enqueued = false;
    private $live_show_assets_enqueued = false;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_shortcode('colt_home_experience', [$this, 'render_home_experience']);
        add_shortcode('colt_vault_experience', [$this, 'render_vault_experience']);
        add_shortcode('colt_mystery_box_experience', [$this, 'render_mystery_box_experience']);
        add_shortcode('colt_mystery_box_product', [$this, 'render_mystery_box_product']);
        add_shortcode('colt_service_experience', [$this, 'render_service_experience']);
        add_shortcode('colt_live_show', [$this, 'render_live_show']);
        add_shortcode('colt_product_slider', [$this, 'render_product_slider']);

        foreach (self::service_shortcode_map() as $shortcode => $service_key) {
            add_shortcode($shortcode, function ($atts = []) use ($service_key, $shortcode) {
                return $this->render_named_service_experience($service_key, $atts, $shortcode);
            });
        }

        add_action('rest_api_init', [$this, 'register_live_show_routes']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_product_crm_promo_rules']);
        add_action('woocommerce_single_product_summary', [$this, 'render_backorder_signup_form'], 31);
        add_filter('woocommerce_is_purchasable', [$this, 'maybe_disable_backorder_purchase'], 10, 2);
        add_action('admin_post_colt_backorder_signup', [$this, 'handle_backorder_signup']);
        add_action('admin_post_nopriv_colt_backorder_signup', [$this, 'handle_backorder_signup']);

        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            add_action('admin_post_colt_experience_save_content', [$this, 'handle_admin_save_content']);
            add_action('admin_post_colt_product_crm_bulk', [$this, 'handle_product_crm_bulk_action']);
            add_action('admin_post_colt_product_crm_create', [$this, 'handle_product_crm_create_product']);
            add_action('admin_post_colt_product_crm_update', [$this, 'handle_product_crm_update_product']);
            add_action('admin_post_colt_product_crm_upload_xlsx', [$this, 'handle_product_crm_upload_xlsx']);
            add_action('admin_post_colt_product_crm_import_xlsx', [$this, 'handle_product_crm_import_xlsx']);
        }
    }

    public function render_home_experience($atts = [])
    {
        $atts = shortcode_atts([
            'products' => 12,
            'contact_url' => home_url('/contact/'),
            'instagram' => '',
            'tiktok' => '',
            'whatnot' => '',
            'whatsapp' => '',
        ], $atts, 'colt_home_experience');

        $this->enqueue_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/home-experience.php';
        return (string) ob_get_clean();
    }

    public function render_vault_experience($atts = [])
    {
        $atts = shortcode_atts([
            'contact_url' => home_url('/contact/'),
            'whatsapp' => '',
            'shop_url' => home_url('/shop/'),
        ], $atts, 'colt_vault_experience');

        $this->enqueue_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/vault-experience.php';
        return (string) ob_get_clean();
    }

    public function render_mystery_box_experience($atts = [])
    {
        $atts = shortcode_atts([
            'product_url' => home_url('/shop/'),
            'contact_url' => home_url('/contact/'),
            'whatsapp' => '',
        ], $atts, 'colt_mystery_box_experience');

        $this->enqueue_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/mystery-box-experience.php';
        return (string) ob_get_clean();
    }

    public function render_mystery_box_product($atts = [])
    {
        $atts = shortcode_atts([
            'product_url' => home_url('/shop/'),
            'contact_url' => home_url('/contact/'),
            'whatsapp' => '',
        ], $atts, 'colt_mystery_box_product');

        $this->enqueue_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/mystery-box-product.php';
        return (string) ob_get_clean();
    }

    public function render_service_experience($atts = [])
    {
        $atts = shortcode_atts([
            'service' => 'singles',
            'contact_url' => home_url('/contact/'),
            'whatsapp' => '',
            'shop_url' => home_url('/shop/'),
            'primary_url' => '',
            'secondary_url' => '',
        ], $atts, 'colt_service_experience');

        return $this->render_named_service_experience((string) $atts['service'], $atts, 'colt_service_experience');
    }

    public function render_live_show($atts = [])
    {
        $atts = shortcode_atts([
            'title' => 'COLT Live Show',
            'subtitle' => 'יריד קלפים חי שבו אספנים ו-vendors נפגשים בזמן אמת.',
        ], $atts, 'colt_live_show');

        $this->enqueue_live_show_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/live-show.php';
        return (string) ob_get_clean();
    }

    public function render_named_service_experience($service_key, $atts = [], $shortcode = 'colt_service_experience')
    {
        $service_key = sanitize_key((string) $service_key);
        $service_aliases = [
            'most_wanted' => 'most-wanted',
            'personal_search' => 'personal-search',
            'search' => 'personal-search',
            'mystery_box' => 'mystery',
            'mystery-box' => 'mystery',
            'mystery_product' => 'mystery-product',
            'mystery-product' => 'mystery-product',
            'the-vault' => 'vault',
        ];
        $service_key = $service_aliases[$service_key] ?? $service_key;

        if ($service_key === 'vault') {
            return $this->render_vault_experience($atts);
        }

        if ($service_key === 'mystery') {
            return $this->render_mystery_box_experience($atts);
        }

        if ($service_key === 'mystery-product') {
            return $this->render_mystery_box_product($atts);
        }

        $atts = shortcode_atts([
            'contact_url' => home_url('/contact/'),
            'whatsapp' => '',
            'shop_url' => home_url('/shop/'),
            'primary_url' => '',
            'secondary_url' => '',
        ], $atts, $shortcode);

        $pages = self::service_pages();
        if (!isset($pages[$service_key])) {
            $service_key = 'singles';
        }

        $urls = [
            'contact' => (string) $atts['contact_url'],
            'whatsapp' => !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : (string) $atts['contact_url'],
            'shop' => (string) $atts['shop_url'],
            'home' => home_url('/'),
        ];

        $service_page = $pages[$service_key];
        $service_page['key'] = $service_key;
        $service_page['primary_url'] = !empty($atts['primary_url'])
            ? (string) $atts['primary_url']
            : ($urls[$service_page['primary_kind']] ?? $urls['contact']);
        $service_page['secondary_url'] = !empty($atts['secondary_url'])
            ? (string) $atts['secondary_url']
            : ($urls[$service_page['secondary_kind']] ?? $urls['whatsapp']);

        $this->enqueue_assets();

        ob_start();
        include COLT_EXPERIENCE_DIR . 'templates/service-experience.php';
        return (string) ob_get_clean();
    }

    public function render_product_slider($atts = [])
    {
        if (!$this->is_woocommerce_ready()) {
            return '';
        }

        $atts = shortcode_atts([
            'id' => '',
            'products' => '',
            'title' => '',
            'show_title' => '1',
        ], $atts, 'colt_product_slider');

        $slider_id = sanitize_key((string) $atts['id']);
        $sliders = $this->product_crm_sliders();
        $slider = $slider_id !== '' && isset($sliders[$slider_id]) && is_array($sliders[$slider_id]) ? $sliders[$slider_id] : [];
        $product_ids = [];

        if (!empty($slider['product_ids']) && is_array($slider['product_ids'])) {
            $product_ids = array_map('absint', $slider['product_ids']);
        }

        if (!$product_ids && !empty($atts['products'])) {
            $product_ids = array_map('absint', preg_split('/[,|\s]+/', (string) $atts['products']));
        }

        $product_ids = array_values(array_filter(array_unique($product_ids)));
        if (!$product_ids) {
            return '';
        }

        $title = trim((string) $atts['title']);
        if ($title === '' && !empty($slider['name'])) {
            $title = (string) $slider['name'];
        }

        static $style_printed = false;

        ob_start();
        if (!$style_printed) :
            $style_printed = true;
            ?>
            <style>
                .colt-product-slider { --colt-product-slider-card: clamp(220px, 24vw, 320px); color: inherit; }
                .colt-product-slider, .colt-product-slider * { box-sizing: border-box; }
                .colt-product-slider__head { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
                .colt-product-slider__title { margin: 0; font: inherit; font-size: clamp(24px, 3vw, 44px); font-weight: 900; line-height: 1.05; }
                .colt-product-slider__track { display: grid; grid-auto-flow: column; grid-auto-columns: var(--colt-product-slider-card); gap: 16px; overflow-x: auto; overscroll-behavior-inline: contain; scroll-snap-type: inline mandatory; padding: 2px 2px 16px; scrollbar-width: thin; }
                .colt-product-slider__item { scroll-snap-align: start; min-width: 0; border: 1px solid rgba(214, 194, 138, .32); border-radius: 14px; background: rgba(11, 15, 23, .78); color: #fff; overflow: hidden; }
                .colt-product-slider__media { display: block; aspect-ratio: 1 / 1; background: rgba(255,255,255,.06); overflow: hidden; }
                .colt-product-slider__image { display: block; width: 100%; height: 100%; object-fit: cover; }
                .colt-product-slider__body { display: grid; gap: 10px; padding: 14px; }
                .colt-product-slider__category { color: #9ff8e6; font-size: 12px; font-weight: 900; text-transform: uppercase; }
                .colt-product-slider__name { margin: 0; font-size: 18px; line-height: 1.2; font-weight: 900; }
                .colt-product-slider__name a, .colt-product-slider__cta { color: inherit; text-decoration: none; }
                .colt-product-slider__price { color: #f5d37b; font-weight: 900; }
                .colt-product-slider__meta, .colt-product-slider__attributes { display: grid; gap: 6px; margin: 0; padding: 0; list-style: none; color: rgba(255,255,255,.76); font-size: 13px; }
                .colt-product-slider__meta li { display: grid; gap: 2px; }
                .colt-product-slider__label { color: rgba(255,255,255,.48); font-size: 11px; font-weight: 900; text-transform: uppercase; }
                .colt-product-slider__attributes { padding-top: 8px; border-top: 1px solid rgba(255,255,255,.12); }
                .colt-product-slider__attributes div { display: grid; grid-template-columns: minmax(86px, .55fr) 1fr; gap: 8px; }
                .colt-product-slider__attributes dt, .colt-product-slider__attributes dd { margin: 0; }
                .colt-product-slider__attributes dt { color: rgba(255,255,255,.5); font-weight: 900; }
                .colt-product-slider__cta { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 9px 12px; border-radius: 999px; background: #d6b45d; color: #10151c; font-weight: 900; }
                @media (max-width: 767px) { .colt-product-slider { --colt-product-slider-card: min(78vw, 310px); } }
            </style>
            <?php
        endif;
        ?>
        <section class="colt-product-slider" dir="rtl" data-colt-product-slider="<?php echo esc_attr($slider_id ?: 'manual'); ?>">
            <?php if ($title !== '' && (string) $atts['show_title'] !== '0') : ?>
                <div class="colt-product-slider__head">
                    <h2 class="colt-product-slider__title"><?php echo esc_html($title); ?></h2>
                </div>
            <?php endif; ?>
            <div class="colt-product-slider__track" role="list">
                <?php foreach ($product_ids as $product_id) : ?>
                    <?php
                    $product = wc_get_product($product_id);
                    if (!$product || get_post_status($product_id) === 'trash') {
                        continue;
                    }

                    $image = get_the_post_thumbnail_url($product_id, 'large') ?: wc_placeholder_img_src('large');
                    $categories = $this->product_slider_term_names($product_id, 'product_cat');
                    $tags = $this->product_slider_term_names($product_id, 'product_tag');
                    $brands = $this->product_slider_brand_names($product_id);
                    $attributes = $this->product_slider_attributes($product);
                    $set_name = (string) get_post_meta($product_id, '_colt_product_set', true);
                    $language = (string) get_post_meta($product_id, '_colt_product_language', true);
                    $stock_status = $product->get_stock_status();
                    $stock_labels = $this->product_crm_stock_statuses();
                    ?>
                    <article class="colt-product-slider__item" role="listitem" data-product-id="<?php echo esc_attr($product_id); ?>">
                        <a class="colt-product-slider__media" href="<?php echo esc_url(get_permalink($product_id)); ?>" aria-label="<?php echo esc_attr(get_the_title($product_id)); ?>">
                            <img class="colt-product-slider__image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title($product_id)); ?>" loading="lazy">
                        </a>
                        <div class="colt-product-slider__body">
                            <?php if ($categories) : ?>
                                <div class="colt-product-slider__category"><?php echo esc_html(implode(' · ', array_slice($categories, 0, 2))); ?></div>
                            <?php endif; ?>
                            <h3 class="colt-product-slider__name"><a href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo esc_html(get_the_title($product_id)); ?></a></h3>
                            <?php if ($product->get_price_html()) : ?>
                                <div class="colt-product-slider__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                            <?php endif; ?>

                            <ul class="colt-product-slider__meta">
                                <?php if ($product->get_sku()) : ?>
                                    <li><span class="colt-product-slider__label">SKU</span><span><?php echo esc_html($product->get_sku()); ?></span></li>
                                <?php endif; ?>
                                <?php if ($categories) : ?>
                                    <li><span class="colt-product-slider__label">קטגוריות</span><span><?php echo esc_html(implode(', ', $categories)); ?></span></li>
                                <?php endif; ?>
                                <?php if ($brands) : ?>
                                    <li><span class="colt-product-slider__label">מותגים</span><span><?php echo esc_html(implode(', ', $brands)); ?></span></li>
                                <?php endif; ?>
                                <?php if ($tags) : ?>
                                    <li><span class="colt-product-slider__label">תגיות</span><span><?php echo esc_html(implode(', ', $tags)); ?></span></li>
                                <?php endif; ?>
                                <?php if ($set_name !== '') : ?>
                                    <li><span class="colt-product-slider__label">Set</span><span><?php echo esc_html($set_name); ?></span></li>
                                <?php endif; ?>
                                <?php if ($language !== '') : ?>
                                    <li><span class="colt-product-slider__label">שפה</span><span><?php echo esc_html($language); ?></span></li>
                                <?php endif; ?>
                                <li><span class="colt-product-slider__label">מלאי</span><span><?php echo esc_html($stock_labels[$stock_status] ?? $stock_status); ?></span></li>
                            </ul>

                            <?php if ($attributes) : ?>
                                <dl class="colt-product-slider__attributes">
                                    <?php foreach ($attributes as $attribute) : ?>
                                        <div>
                                            <dt><?php echo esc_html($attribute['label']); ?></dt>
                                            <dd><?php echo esc_html($attribute['value']); ?></dd>
                                        </div>
                                    <?php endforeach; ?>
                                </dl>
                            <?php endif; ?>
                            <a class="colt-product-slider__cta" href="<?php echo esc_url(get_permalink($product_id)); ?>">למוצר</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function enqueue_assets()
    {
        if ($this->assets_enqueued) {
            return;
        }

        wp_enqueue_style(
            'colt-rubik-font',
            'https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700;800;900&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'colt-experience',
            COLT_EXPERIENCE_URL . 'assets/css/colt-experience.css',
            ['colt-rubik-font'],
            COLT_EXPERIENCE_VERSION
        );

        wp_enqueue_script(
            'colt-lenis',
            'https://cdn.jsdelivr.net/npm/lenis@1.3.13/dist/lenis.min.js',
            [],
            '1.3.13',
            true
        );

        wp_enqueue_script(
            'colt-gsap',
            'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
            [],
            '3.13.0',
            true
        );

        wp_enqueue_script(
            'colt-scrolltrigger',
            'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
            ['colt-gsap'],
            '3.13.0',
            true
        );

        wp_enqueue_script(
            'colt-experience',
            COLT_EXPERIENCE_URL . 'assets/js/colt-experience.js',
            ['colt-lenis', 'colt-gsap', 'colt-scrolltrigger'],
            COLT_EXPERIENCE_VERSION,
            true
        );

        $this->assets_enqueued = true;
    }

    private function enqueue_live_show_assets()
    {
        if ($this->live_show_assets_enqueued) {
            return;
        }

        $this->enqueue_assets();

        wp_enqueue_style(
            'colt-live-show',
            COLT_EXPERIENCE_URL . 'assets/css/colt-live-show.css',
            ['colt-experience'],
            COLT_EXPERIENCE_VERSION
        );

        wp_enqueue_script(
            'colt-live-show',
            COLT_EXPERIENCE_URL . 'assets/js/colt-live-show.js',
            [],
            COLT_EXPERIENCE_VERSION,
            true
        );

        wp_localize_script('colt-live-show', 'COLT_LIVE_SHOW', [
            'restUrl' => esc_url_raw(rest_url('colt/v1/live-show')),
            'map' => self::live_show_map(),
            'npcs' => self::live_show_npcs(),
            'version' => COLT_EXPERIENCE_VERSION,
        ]);

        $this->live_show_assets_enqueued = true;
    }

    public static function logo_url()
    {
        $custom_logo_id = (int) get_theme_mod('custom_logo');

        if ($custom_logo_id > 0) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                return $logo_url;
            }
        }

        return COLT_EXPERIENCE_URL . 'assets/img/colt-mark.svg';
    }

    public static function asset_url($path)
    {
        return COLT_EXPERIENCE_URL . ltrim((string) $path, '/');
    }

    public static function live_show_map()
    {
        return [
            'width' => 3200,
            'height' => 1760,
            'boothWidth' => 250,
            'boothHeight' => 150,
            'sideDepth' => 430,
            'participantTtl' => 45,
        ];
    }

    public static function live_show_npcs()
    {
        return [
            ['id' => 'npc-01', 'name' => 'Mika', 'x' => 760, 'y' => 520, 'tone' => 'mint', 'line' => 'ראית את הסלאבים ליד הכניסה? יש שם דברים יפים.'],
            ['id' => 'npc-02', 'name' => 'Rex', 'x' => 1220, 'y' => 410, 'tone' => 'gold', 'line' => 'אני מחכה שמישהו יפתח בוסטר מול כולם.'],
            ['id' => 'npc-03', 'name' => 'Nova', 'x' => 1840, 'y' => 500, 'tone' => 'pink', 'line' => 'אם אתה מחפש וואן פיס, תבדוק את העמדות בצד ימין.'],
            ['id' => 'npc-04', 'name' => 'Kai', 'x' => 2380, 'y' => 390, 'tone' => 'blue', 'line' => 'טיפ קטן: תמיד שואלים על מצב הקלף לפני מחיר.'],
            ['id' => 'npc-05', 'name' => 'Juno', 'x' => 2860, 'y' => 730, 'tone' => 'violet', 'line' => 'יש פה אווירה של כנס אספנים אמיתי.'],
            ['id' => 'npc-06', 'name' => 'Ash', 'x' => 2460, 'y' => 1180, 'tone' => 'red', 'line' => 'קלף מדורג 10 תמיד מושך אנשים לשולחן.'],
            ['id' => 'npc-07', 'name' => 'Lio', 'x' => 2020, 'y' => 1370, 'tone' => 'mint', 'line' => 'אני בודק מי מביא Mystery Box הכי מעניין.'],
            ['id' => 'npc-08', 'name' => 'Skye', 'x' => 1540, 'y' => 1240, 'tone' => 'blue', 'line' => 'השולחנות הכי טובים הם אלה שמספרים סיפור.'],
            ['id' => 'npc-09', 'name' => 'Ori', 'x' => 1040, 'y' => 1370, 'tone' => 'gold', 'line' => 'יש כאן כמה chase cards שמתחבאים יפה.'],
            ['id' => 'npc-10', 'name' => 'Zed', 'x' => 510, 'y' => 1120, 'tone' => 'pink', 'line' => 'אם vendor זמין, פשוט תתקרב ותבקש שיחה.'],
            ['id' => 'npc-11', 'name' => 'Tali', 'x' => 430, 'y' => 710, 'tone' => 'violet', 'line' => 'אני אוהבת עמדות עם צבע ברור וכותרת קצרה.'],
            ['id' => 'npc-12', 'name' => 'Ben', 'x' => 1600, 'y' => 820, 'tone' => 'red', 'line' => 'המעברים פתוחים בכוונה, שלא ירגיש צפוף.'],
            ['id' => 'npc-13', 'name' => 'Nox', 'x' => 2120, 'y' => 890, 'tone' => 'blue', 'line' => 'פוקימון ווואן פיס זה שילוב שמביא קהל טוב.'],
            ['id' => 'npc-14', 'name' => 'Yam', 'x' => 1160, 'y' => 900, 'tone' => 'mint', 'line' => 'אני פה בשביל הקלפים, אבל נשאר בגלל האנשים.'],
            ['id' => 'npc-15', 'name' => 'Vera', 'x' => 2780, 'y' => 1420, 'tone' => 'gold', 'line' => 'תסתובב קצת. לפעמים העסקה הטובה נמצאת בקצה המפה.'],
        ];
    }

    public function register_live_show_routes()
    {
        $namespace = 'colt/v1';
        $routes = [
            ['/live-show/join', 'POST', 'rest_live_show_join'],
            ['/live-show/state', 'GET', 'rest_live_show_state'],
            ['/live-show/heartbeat', 'POST', 'rest_live_show_heartbeat'],
            ['/live-show/leave', 'POST', 'rest_live_show_leave'],
            ['/live-show/booth', 'POST', 'rest_live_show_booth'],
            ['/live-show/chat/request', 'POST', 'rest_live_show_chat_request'],
            ['/live-show/chat/respond', 'POST', 'rest_live_show_chat_respond'],
            ['/live-show/chat/message', 'POST', 'rest_live_show_chat_message'],
        ];

        foreach ($routes as $route) {
            register_rest_route($namespace, $route[0], [
                'methods' => $route[1],
                'callback' => [$this, $route[2]],
                'permission_callback' => '__return_true',
            ]);
        }
    }

    public function rest_live_show_join($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $map = self::live_show_map();
        $role = isset($params['role']) && $params['role'] === 'vendor' ? 'vendor' : 'collector';
        $session_id = 'p_' . strtolower(wp_generate_password(12, false, false));
        $token = wp_generate_password(32, false, false);
        $name = $this->live_show_clean_text($params['name'] ?? '', $role === 'vendor' ? 'Vendor' : 'Collector', 28);
        $colors = ['#86f7d4', '#ffd36a', '#ff6fae', '#7cc8ff', '#b796ff', '#ff8a70'];
        $color = sanitize_hex_color($params['color'] ?? '') ?: $colors[array_rand($colors)];
        $spawn = $role === 'vendor'
            ? ['x' => 260, 'y' => (int) floor($map['height'] / 2)]
            : ['x' => (int) floor($map['width'] / 2), 'y' => (int) floor($map['height'] / 2)];

        $state['sessions'][$session_id] = [
            'id' => $session_id,
            'token' => $token,
            'role' => $role,
            'name' => $name,
            'color' => $color,
            'x' => $spawn['x'],
            'y' => $spawn['y'],
            'dir' => 'down',
            'boothId' => '',
            'lastSeen' => time(),
            'joined' => time(),
        ];

        $this->live_show_save_state($state);

        return rest_ensure_response([
            'session' => $session_id,
            'token' => $token,
            'state' => $this->live_show_public_state($state, $session_id),
        ]);
    }

    public function rest_live_show_state($request)
    {
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $session_id = $this->live_show_state_key($state['sessions'], $request->get_param('session'));
        $token = (string) $request->get_param('token');

        if ($session_id && isset($state['sessions'][$session_id]) && hash_equals((string) $state['sessions'][$session_id]['token'], $token)) {
            $state['sessions'][$session_id]['lastSeen'] = time();
        }

        $this->live_show_save_state($state);

        return rest_ensure_response($this->live_show_public_state($state, $session_id));
    }

    public function rest_live_show_heartbeat($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $session = $this->live_show_verify_session($state, $params);

        if (!$session) {
            return new WP_Error('colt_live_show_session', 'Session expired.', ['status' => 401]);
        }

        $map = self::live_show_map();
        $id = $session['id'];
        $state['sessions'][$id]['x'] = $this->live_show_clamp_number($params['x'] ?? $session['x'], 60, $map['width'] - 60);
        $state['sessions'][$id]['y'] = $this->live_show_clamp_number($params['y'] ?? $session['y'], 120, $map['height'] - 80);
        $state['sessions'][$id]['dir'] = in_array(($params['dir'] ?? 'down'), ['up', 'down', 'left', 'right'], true) ? $params['dir'] : 'down';
        $state['sessions'][$id]['lastSeen'] = time();

        $this->live_show_save_state($state);

        return rest_ensure_response($this->live_show_public_state($state, $id));
    }

    public function rest_live_show_leave($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $session = $this->live_show_verify_session($state, $params);

        if ($session) {
            $state = $this->live_show_remove_session($state, $session['id']);
            $this->live_show_save_state($state);
        }

        return rest_ensure_response(['ok' => true]);
    }

    public function rest_live_show_booth($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $session = $this->live_show_verify_session($state, $params);

        if (!$session || $session['role'] !== 'vendor') {
            return new WP_Error('colt_live_show_vendor', 'Vendor session required.', ['status' => 403]);
        }

        $map = self::live_show_map();
        $booth_id = $session['boothId'] ?: 'b_' . strtolower(wp_generate_password(10, false, false));
        $x = $this->live_show_clamp_number($params['x'] ?? ($state['booths'][$booth_id]['x'] ?? 220), 0, $map['width']);
        $y = $this->live_show_clamp_number($params['y'] ?? ($state['booths'][$booth_id]['y'] ?? 220), 0, $map['height']);
        $width = (int) $map['boothWidth'];
        $height = (int) $map['boothHeight'];

        if (!$this->live_show_is_valid_booth_position($x, $y, $width, $height, $state['booths'], $booth_id)) {
            return new WP_Error('colt_live_show_booth_position', 'This booth position is unavailable.', ['status' => 409]);
        }

        $items = [];
        if (isset($params['items']) && is_array($params['items'])) {
            foreach (array_slice($params['items'], 0, 6) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $items[] = [
                    'title' => $this->live_show_clean_text($item['title'] ?? '', 'Showcase item', 40),
                    'price' => $this->live_show_clean_text($item['price'] ?? '', '', 18),
                    'note' => $this->live_show_clean_text($item['note'] ?? '', '', 72),
                ];
            }
        }

        $state['booths'][$booth_id] = [
            'id' => $booth_id,
            'owner' => $session['id'],
            'title' => $this->live_show_clean_text($params['title'] ?? '', $session['name'] . ' table', 42),
            'description' => $this->live_show_clean_text($params['description'] ?? '', 'A collector booth with fresh cards and live trades.', 180),
            'color' => sanitize_hex_color($params['color'] ?? '') ?: $session['color'],
            'x' => $x,
            'y' => $y,
            'w' => $width,
            'h' => $height,
            'items' => $items,
            'updated' => time(),
        ];
        $state['sessions'][$session['id']]['boothId'] = $booth_id;
        $state['sessions'][$session['id']]['x'] = $x;
        $state['sessions'][$session['id']]['y'] = min($map['height'] - 90, $y + $height + 72);
        $state['sessions'][$session['id']]['lastSeen'] = time();

        $this->live_show_save_state($state);

        return rest_ensure_response($this->live_show_public_state($state, $session['id']));
    }

    public function rest_live_show_chat_request($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $session = $this->live_show_verify_session($state, $params);
        $booth_id = $this->live_show_state_key($state['booths'], $params['boothId'] ?? '');

        if (!$session || !isset($state['booths'][$booth_id])) {
            return new WP_Error('colt_live_show_chat', 'Booth is unavailable.', ['status' => 404]);
        }

        $target_id = (string) $state['booths'][$booth_id]['owner'];
        if (!isset($state['sessions'][$target_id]) || $target_id === $session['id']) {
            return new WP_Error('colt_live_show_chat_target', 'Vendor is unavailable.', ['status' => 409]);
        }

        foreach ($state['chats'] as $room_id => $room) {
            $participants = $room['participants'] ?? [];
            if (in_array($session['id'], $participants, true) && in_array($target_id, $participants, true) && in_array($room['status'], ['pending', 'active'], true)) {
                return rest_ensure_response([
                    'roomId' => $room_id,
                    'state' => $this->live_show_public_state($state, $session['id']),
                ]);
            }
        }

        $room_id = 'c_' . strtolower(wp_generate_password(12, false, false));
        $state['chats'][$room_id] = [
            'id' => $room_id,
            'boothId' => $booth_id,
            'requester' => $session['id'],
            'target' => $target_id,
            'participants' => [$session['id'], $target_id],
            'status' => 'pending',
            'created' => time(),
            'updated' => time(),
            'messages' => [],
        ];

        $this->live_show_save_state($state);

        return rest_ensure_response([
            'roomId' => $room_id,
            'state' => $this->live_show_public_state($state, $session['id']),
        ]);
    }

    public function rest_live_show_chat_respond($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $session = $this->live_show_verify_session($state, $params);
        $room_id = $this->live_show_state_key($state['chats'], $params['roomId'] ?? '');

        if (!$session || !isset($state['chats'][$room_id]) || $state['chats'][$room_id]['target'] !== $session['id']) {
            return new WP_Error('colt_live_show_chat', 'Chat request is unavailable.', ['status' => 404]);
        }

        $accept = !empty($params['accept']);
        $state['chats'][$room_id]['status'] = $accept ? 'active' : 'declined';
        $state['chats'][$room_id]['updated'] = time();
        $this->live_show_save_state($state);

        return rest_ensure_response($this->live_show_public_state($state, $session['id']));
    }

    public function rest_live_show_chat_message($request)
    {
        $params = $this->live_show_request_params($request);
        $state = $this->live_show_read_state();
        $state = $this->live_show_prune_state($state);
        $session = $this->live_show_verify_session($state, $params);
        $room_id = $this->live_show_state_key($state['chats'], $params['roomId'] ?? '');
        $message = $this->live_show_clean_text($params['message'] ?? '', '', 280);

        if (!$session || $message === '' || !isset($state['chats'][$room_id])) {
            return new WP_Error('colt_live_show_chat', 'Chat is unavailable.', ['status' => 404]);
        }

        $room = $state['chats'][$room_id];
        if ($room['status'] !== 'active' || !in_array($session['id'], $room['participants'], true)) {
            return new WP_Error('colt_live_show_chat_forbidden', 'Chat is not active.', ['status' => 403]);
        }

        $state['chats'][$room_id]['messages'][] = [
            'from' => $session['id'],
            'name' => $session['name'],
            'text' => $message,
            'time' => time(),
        ];
        $state['chats'][$room_id]['messages'] = array_slice($state['chats'][$room_id]['messages'], -50);
        $state['chats'][$room_id]['updated'] = time();
        $this->live_show_save_state($state);

        return rest_ensure_response($this->live_show_public_state($state, $session['id']));
    }

    private function live_show_request_params($request)
    {
        $params = method_exists($request, 'get_json_params') ? $request->get_json_params() : [];
        return is_array($params) ? $params : [];
    }

    private function live_show_default_state()
    {
        return [
            'sessions' => [],
            'booths' => [],
            'chats' => [],
        ];
    }

    private function live_show_read_state()
    {
        $state = get_option('colt_live_show_state', []);
        if (!is_array($state)) {
            $state = [];
        }
        return array_merge($this->live_show_default_state(), $state);
    }

    private function live_show_save_state(array $state)
    {
        update_option('colt_live_show_state', $state, false);
    }

    private function live_show_prune_state(array $state)
    {
        $map = self::live_show_map();
        $now = time();
        foreach ($state['sessions'] as $session_id => $session) {
            if (($now - (int) ($session['lastSeen'] ?? 0)) > (int) $map['participantTtl']) {
                $state = $this->live_show_remove_session($state, $session_id);
            }
        }

        foreach ($state['chats'] as $room_id => $room) {
            $participants = $room['participants'] ?? [];
            $has_missing = false;
            foreach ($participants as $participant) {
                if (!isset($state['sessions'][$participant])) {
                    $has_missing = true;
                    break;
                }
            }
            if ($has_missing || ($now - (int) ($room['updated'] ?? 0)) > 900) {
                unset($state['chats'][$room_id]);
            }
        }

        return $state;
    }

    private function live_show_remove_session(array $state, $session_id)
    {
        unset($state['sessions'][$session_id]);

        foreach ($state['booths'] as $booth_id => $booth) {
            if (($booth['owner'] ?? '') === $session_id) {
                unset($state['booths'][$booth_id]);
            }
        }

        foreach ($state['chats'] as $room_id => $room) {
            if (in_array($session_id, $room['participants'] ?? [], true)) {
                unset($state['chats'][$room_id]);
            }
        }

        return $state;
    }

    private function live_show_public_state(array $state, $viewer_id = '')
    {
        $sessions = [];
        foreach ($state['sessions'] as $session) {
            $sessions[] = [
                'id' => $session['id'],
                'role' => $session['role'],
                'name' => $session['name'],
                'color' => $session['color'],
                'x' => (int) $session['x'],
                'y' => (int) $session['y'],
                'dir' => $session['dir'] ?? 'down',
                'boothId' => $session['boothId'] ?? '',
                'lastSeen' => (int) ($session['lastSeen'] ?? 0),
            ];
        }

        $chats = [];
        foreach ($state['chats'] as $room) {
            if ($viewer_id && in_array($viewer_id, $room['participants'] ?? [], true)) {
                $chats[] = $room;
            }
        }

        return [
            'map' => self::live_show_map(),
            'sessions' => array_values($sessions),
            'booths' => array_values($state['booths']),
            'chats' => array_values($chats),
            'serverTime' => time(),
        ];
    }

    private function live_show_verify_session(array $state, array $params)
    {
        $session_id = $this->live_show_state_key($state['sessions'], $params['session'] ?? '');
        $token = (string) ($params['token'] ?? '');
        if (!$session_id || !isset($state['sessions'][$session_id])) {
            return null;
        }
        if (!hash_equals((string) $state['sessions'][$session_id]['token'], $token)) {
            return null;
        }
        return $state['sessions'][$session_id];
    }

    private function live_show_state_key(array $items, $value)
    {
        $raw = (string) $value;
        if ($raw !== '' && isset($items[$raw])) {
            return $raw;
        }
        return sanitize_key($raw);
    }

    private function live_show_clean_text($value, $fallback, $max_length)
    {
        $value = sanitize_text_field((string) $value);
        $value = trim($value);
        if ($value === '') {
            $value = (string) $fallback;
        }
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max_length);
        }
        return substr($value, 0, $max_length);
    }

    private function live_show_clamp_number($value, $min, $max)
    {
        $number = is_numeric($value) ? (float) $value : (float) $min;
        return (int) max($min, min($max, $number));
    }

    private function live_show_is_valid_booth_position($x, $y, $width, $height, array $booths, $ignore_id = '')
    {
        $map = self::live_show_map();
        $half_w = $width / 2;
        $half_h = $height / 2;

        if ($x - $half_w < 20 || $x + $half_w > $map['width'] - 20 || $y - $half_h < 40 || $y + $half_h > $map['height'] - 30) {
            return false;
        }

        $side_depth = (int) $map['sideDepth'];
        $in_side_zone = $x <= $side_depth || $x >= ($map['width'] - $side_depth) || $y <= $side_depth || $y >= ($map['height'] - $side_depth);
        if (!$in_side_zone) {
            return false;
        }

        $candidate = [
            'left' => $x - $half_w - 28,
            'right' => $x + $half_w + 28,
            'top' => $y - $half_h - 28,
            'bottom' => $y + $half_h + 28,
        ];

        foreach ($booths as $booth_id => $booth) {
            if ($booth_id === $ignore_id) {
                continue;
            }
            $existing_half_w = ((int) ($booth['w'] ?? $width)) / 2;
            $existing_half_h = ((int) ($booth['h'] ?? $height)) / 2;
            $existing = [
                'left' => ((int) $booth['x']) - $existing_half_w,
                'right' => ((int) $booth['x']) + $existing_half_w,
                'top' => ((int) $booth['y']) - $existing_half_h,
                'bottom' => ((int) $booth['y']) + $existing_half_h,
            ];

            if ($candidate['left'] < $existing['right'] && $candidate['right'] > $existing['left'] && $candidate['top'] < $existing['bottom'] && $candidate['bottom'] > $existing['top']) {
                return false;
            }
        }

        return true;
    }

    public static function service_shortcode_map()
    {
        return [
            'colt_singles_experience' => 'singles',
            'colt_vault_service_experience' => 'vault',
            'colt_jewelry_experience' => 'jewelry',
            'colt_mystery_service_experience' => 'mystery',
            'colt_mystery_box_service_experience' => 'mystery',
            'colt_most_wanted_experience' => 'most-wanted',
            'colt_personal_search_experience' => 'personal-search',
            'colt_search_experience' => 'personal-search',
            'colt_grading_experience' => 'grading',
            'colt_whatnot_experience' => 'whatnot',
            'colt_portfolio_experience' => 'portfolio',
        ];
    }

    public static function content_settings()
    {
        $content = get_option('colt_experience_content', []);

        return is_array($content) ? $content : [];
    }

    private static function merge_content_values(array $defaults, array $overrides)
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
                $defaults[$key] = self::merge_content_values($defaults[$key], $value);
                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }

    public function register_admin_menu()
    {
        add_menu_page(
            'COLT Experience',
            'COLT Experience',
            'manage_options',
            'colt-experience',
            [$this, 'render_admin_content_page'],
            'dashicons-edit-page',
            58
        );

        add_submenu_page(
            'colt-experience',
            'COLT Product CRM',
            'Product CRM',
            'manage_options',
            'colt-product-crm',
            [$this, 'render_product_crm_page']
        );
    }

    public function enqueue_admin_assets($hook_suffix)
    {
        if (!in_array($hook_suffix, ['toplevel_page_colt-experience', 'colt-experience_page_colt-product-crm'], true)) {
            return;
        }

        wp_enqueue_media();
        wp_register_script('colt-experience-admin', false, ['jquery'], COLT_EXPERIENCE_VERSION, true);
        wp_enqueue_script('colt-experience-admin');
        wp_add_inline_script('colt-experience-admin', <<<'JS'
jQuery(function ($) {
    $('.colt-admin, .colt-crm').on('click', '.colt-admin__media-button', function (event) {
        event.preventDefault();

        const $button = $(this);
        const $field = $button.closest('.colt-admin__media').find('input[type="text"]').first();
        const $preview = $button.closest('.colt-admin__media').find('.colt-admin__media-preview').first();
        const frame = wp.media({
            title: 'בחירת תמונה',
            button: { text: 'שימוש בתמונה' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            const url = attachment.url || '';
            $field.val(url).trigger('change');

            if (url) {
                $preview.empty().append($('<img>', { src: url, alt: '' }));
            }
        });

        frame.open();
    });
});
JS);
    }

    public function handle_admin_save_content()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to edit COLT content.', 'colt-experience'));
        }

        check_admin_referer('colt_experience_save_content');

        $active = isset($_POST['colt_active_service']) ? sanitize_key(wp_unslash($_POST['colt_active_service'])) : 'home';
        $home_defaults = self::default_home_page();
        $service_defaults = self::default_service_pages();
        $special_defaults = self::default_special_pages();
        $is_home = $active === 'home';
        $is_special = isset($special_defaults[$active]);

        if (!$is_home && !$is_special && !isset($service_defaults[$active])) {
            $active = 'home';
            $is_home = true;
        }

        $posted = [];
        if (isset($_POST['colt_service_pages'][$active]) && is_array($_POST['colt_service_pages'][$active])) {
            $posted = wp_unslash($_POST['colt_service_pages'][$active]);
        }

        $content = self::content_settings();
        if ($is_home) {
            $content['home'] = self::sanitize_service_content($posted, $home_defaults);
        } else {
            $bucket = $is_special ? 'special' : 'services';
            $schema = $is_special ? $special_defaults[$active] : $service_defaults[$active];

            if (!isset($content[$bucket]) || !is_array($content[$bucket])) {
                $content[$bucket] = [];
            }

            $content[$bucket][$active] = self::sanitize_service_content($posted, $schema);
        }
        update_option('colt_experience_content', $content, false);

        wp_safe_redirect(add_query_arg([
            'page' => 'colt-experience',
            'colt_page' => $active,
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    private static function sanitize_service_content($posted, array $schema)
    {
        $clean = [];

        foreach ($schema as $key => $default) {
            if (!array_key_exists($key, $posted)) {
                continue;
            }

            if (is_array($default)) {
                $posted_value = is_array($posted[$key]) ? $posted[$key] : [];
                $clean[$key] = self::sanitize_service_content($posted_value, $default);
                continue;
            }

            $value = is_scalar($posted[$key]) ? (string) $posted[$key] : '';
            $key_name = (string) $key;
            $is_url_field = in_array($key_name, ['scene', 'url', 'image', 'primary_url', 'secondary_url'], true)
                || substr($key_name, -4) === '_url'
                || substr($key_name, -6) === '_image';
            $clean[$key] = $is_url_field ? esc_url_raw($value) : sanitize_textarea_field($value);
        }

        return $clean;
    }

    public function render_admin_content_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $special_defaults = self::default_special_pages();
        $pages = ['home' => self::home_page()] + self::special_pages() + self::service_pages();
        $shortcodes = self::service_admin_shortcodes();
        $active = isset($_GET['colt_page']) ? sanitize_key(wp_unslash($_GET['colt_page'])) : 'home';
        if (!isset($pages[$active])) {
            $active = 'home';
        }
        $page = $pages[$active];
        $is_home = $active === 'home';
        $is_special = isset($special_defaults[$active]);
        ?>
        <div class="wrap colt-admin" dir="rtl">
            <style>
                .colt-admin { max-width: 1240px; }
                .colt-admin h1 { font-weight: 900; }
                .colt-admin__layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 18px; align-items: start; }
                .colt-admin__nav { display: grid; gap: 7px; position: sticky; top: 42px; }
                .colt-admin__nav a { display: grid; gap: 3px; padding: 11px 12px; border: 1px solid #dcdcde; border-radius: 8px; background: #fff; text-decoration: none; }
                .colt-admin__nav a.is-active { border-color: #111; box-shadow: inset 4px 0 0 #111; }
                .colt-admin__nav strong { color: #1d2327; }
                .colt-admin__nav code { direction: ltr; text-align: left; font-size: 11px; white-space: normal; }
                .colt-admin__panel { padding: 18px; border: 1px solid #dcdcde; border-radius: 10px; background: #fff; }
                .colt-admin__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
                .colt-admin__section { margin-top: 18px; padding-top: 18px; border-top: 1px solid #dcdcde; }
                .colt-admin__repeat { display: grid; gap: 12px; }
                .colt-admin__card { padding: 14px; border: 1px solid #dcdcde; border-radius: 8px; background: #f6f7f7; }
                .colt-admin label { display: grid; gap: 6px; font-weight: 700; }
                .colt-admin input[type="text"], .colt-admin textarea, .colt-admin select { width: 100%; max-width: none; }
                .colt-admin textarea { min-height: 82px; }
                .colt-admin__wide { grid-column: 1 / -1; }
                .colt-admin__shortcode { display: inline-block; direction: ltr; margin: 4px 0 0; padding: 6px 8px; border-radius: 6px; background: #f0f0f1; }
                .colt-admin__media { display: grid; gap: 8px; }
                .colt-admin__media-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; align-items: center; }
                .colt-admin__media-preview { min-height: 64px; border: 1px dashed #c3c4c7; border-radius: 8px; background: #fff; overflow: hidden; }
                .colt-admin__media-preview:empty { display: none; }
                .colt-admin__media-preview img { display: block; width: 100%; max-height: 180px; object-fit: contain; background: #f6f7f7; }
                @media (max-width: 960px) { .colt-admin__layout, .colt-admin__grid { grid-template-columns: 1fr; } .colt-admin__nav { position: static; } }
            </style>

            <h1>COLT Experience - עריכת תוכן לפי שורטקוד</h1>
            <p>בחר עמוד שירות, ערוך את הטקסטים והמידע שמופיעים בפרונט, ושמור. אם שדה נשאר ריק הוא עדיין יישמר כריק, והקוד משמש רק כברירת מחדל ראשונית.</p>

            <?php if (isset($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p>התוכן נשמר בהצלחה.</p></div>
            <?php endif; ?>

            <div class="colt-admin__layout">
                <nav class="colt-admin__nav" aria-label="בחירת עמוד שירות">
                    <?php foreach ($pages as $key => $service_page) : ?>
                        <?php
                        $url = add_query_arg([
                            'page' => 'colt-experience',
                            'colt_page' => $key,
                        ], admin_url('admin.php'));
                        $shortcode = $shortcodes[$key] ?? '[colt_service_experience service="' . $key . '"]';
                        ?>
                        <a href="<?php echo esc_url($url); ?>" class="<?php echo $active === $key ? 'is-active' : ''; ?>">
                            <strong><?php echo esc_html($service_page['nav_label'] ?? $service_page['title'] ?? $key); ?></strong>
                            <code><?php echo esc_html($shortcode); ?></code>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <form class="colt-admin__panel" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('colt_experience_save_content'); ?>
                    <input type="hidden" name="action" value="colt_experience_save_content">
                    <input type="hidden" name="colt_active_service" value="<?php echo esc_attr($active); ?>">

                    <h2><?php echo esc_html($page['nav_label'] ?? $page['title']); ?></h2>
                    <p>שורטקוד: <code class="colt-admin__shortcode"><?php echo esc_html($shortcodes[$active] ?? '[colt_service_experience service="' . $active . '"]'); ?></code></p>

                    <?php if ($is_home) : ?>
                        <?php $this->render_home_admin_fields($active, $page); ?>
                    <?php elseif ($is_special) : ?>
                        <?php $this->render_special_admin_fields($active, $page); ?>
                    <?php else : ?>
                        <?php $this->render_service_admin_fields($active, $page); ?>
                    <?php endif; ?>

                    <?php submit_button('שמירת תוכן העמוד'); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function render_product_crm_page()
    {
        if (!$this->can_manage_product_crm()) {
            return;
        }

        ?>
        <div class="wrap colt-crm" dir="rtl">
            <style>
                .colt-crm { max-width: 1440px; color: #10151c; }
                .colt-crm h1 { font-size: 30px; font-weight: 900; margin-bottom: 6px; }
                .colt-crm h2 { margin: 0 0 12px; font-size: 19px; font-weight: 900; }
                .colt-crm h3 { margin: 0 0 10px; font-size: 15px; font-weight: 900; }
                .colt-crm p { font-size: 14px; }
                .colt-crm__hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin: 18px 0; padding: 20px; border: 1px solid #dcdcde; border-radius: 14px; background: linear-gradient(135deg, #0d1119, #172230 58%, #21150a); color: #fff; box-shadow: 0 20px 60px rgba(16, 21, 28, .14); }
                .colt-crm__hero p { max-width: 740px; margin: 4px 0 0; color: rgba(255,255,255,.72); }
                .colt-crm__hero-code { direction: ltr; padding: 10px 13px; border-radius: 999px; background: rgba(255,255,255,.12); color: #ffd36a; font-weight: 800; white-space: nowrap; }
                .colt-crm__hero-actions { display: grid; gap: 10px; justify-items: end; }
                .colt-crm__stats { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; margin: 16px 0; }
                .colt-crm__stat { padding: 14px; border: 1px solid #dcdcde; border-radius: 12px; background: #fff; }
                .colt-crm__stat strong { display: block; font-size: 24px; line-height: 1.1; color: #111827; }
                .colt-crm__stat span { display: block; margin-top: 5px; color: #646970; font-weight: 700; }
                .colt-crm__panel { margin: 16px 0; padding: 16px; border: 1px solid #dcdcde; border-radius: 14px; background: #fff; }
                .colt-crm__filters { display: grid; grid-template-columns: minmax(180px, 1.2fr) repeat(4, minmax(135px, .75fr)) auto; gap: 10px; align-items: end; }
                .colt-crm label { display: grid; gap: 6px; font-weight: 800; color: #1d2327; }
                .colt-crm input[type="text"], .colt-crm input[type="number"], .colt-crm select, .colt-crm textarea { width: 100%; max-width: none; min-height: 36px; }
                .colt-crm textarea { min-height: 90px; }
                .colt-crm select[multiple] { min-height: 110px; }
                .colt-crm__bulk { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; align-items: start; }
                .colt-crm__bulk-card { display: grid; gap: 10px; padding: 14px; border: 1px solid #dcdcde; border-radius: 12px; background: #f6f7f7; min-height: 100%; }
                .colt-crm__bulk-card--danger { border-color: #f1b8b8; background: #fff5f5; }
                .colt-crm__table-wrap { overflow-x: auto; border: 1px solid #dcdcde; border-radius: 12px; background: #fff; }
                .colt-crm table { margin: 0; border: 0; }
                .colt-crm th { font-weight: 900; }
                .colt-crm td, .colt-crm th { vertical-align: middle; }
                .colt-crm__product { display: flex; gap: 10px; align-items: center; min-width: 280px; }
                .colt-crm__thumb { width: 54px; height: 54px; border-radius: 8px; object-fit: cover; background: #f0f0f1; border: 1px solid #dcdcde; }
                .colt-crm__product strong { display: block; color: #1d2327; }
                .colt-crm__product a { text-decoration: none; }
                .colt-crm__muted { color: #646970; font-size: 12px; }
                .colt-crm__badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px; background: #f0f0f1; font-size: 12px; font-weight: 900; }
                .colt-crm__badge--in { background: #ddf7e7; color: #006b38; }
                .colt-crm__badge--out { background: #ffe3e3; color: #9b1c1c; }
                .colt-crm__badge--back { background: #fff3cd; color: #7a4c00; }
                .colt-crm__insights { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
                .colt-crm__list { display: grid; gap: 9px; margin: 0; }
                .colt-crm__list li { display: flex; justify-content: space-between; gap: 10px; margin: 0; padding: 10px; border: 1px solid #e7e8ea; border-radius: 10px; background: #fff; }
                .colt-crm__list a { text-decoration: none; font-weight: 800; }
                .colt-crm__shortcode { display: inline-flex; direction: ltr; max-width: 100%; padding: 7px 10px; border-radius: 8px; background: #10151c; color: #f5d37b; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; white-space: nowrap; overflow-x: auto; }
                .colt-crm__slider-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
                .colt-crm__slider-card { display: grid; gap: 8px; padding: 12px; border: 1px solid #e7e8ea; border-radius: 10px; background: #f8f9fb; }
                .colt-crm__footer-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-top: 14px; }
                .colt-crm__note { margin: 0; padding: 10px 12px; border-radius: 10px; background: #fff8e5; color: #6f4e00; font-weight: 700; }
                .colt-crm__pagination { margin-top: 14px; text-align: center; }
                .colt-crm__pagination .page-numbers { display: inline-block; margin: 0 2px; padding: 6px 10px; border-radius: 8px; background: #fff; border: 1px solid #dcdcde; text-decoration: none; }
                .colt-crm__pagination .current { background: #111827; color: #fff; border-color: #111827; }
                .colt-crm__modal { position: fixed; inset: 0; z-index: 100000; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(5, 8, 13, .66); }
                .colt-crm__modal.is-open { display: flex; }
                .colt-crm__modal-card { width: min(980px, 100%); max-height: calc(100vh - 48px); overflow: auto; padding: 20px; border-radius: 16px; background: #fff; box-shadow: 0 30px 100px rgba(0, 0, 0, .35); }
                .colt-crm__modal-head { display: flex; justify-content: space-between; gap: 12px; align-items: start; margin-bottom: 14px; }
                .colt-crm__create-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
                .colt-crm__wide { grid-column: 1 / -1; }
                .colt-crm__import-preview { overflow-x: auto; max-height: 260px; border: 1px solid #dcdcde; border-radius: 10px; background: #fff; }
                .colt-crm__mapping { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
                .colt-crm__mapping label { padding: 10px; border: 1px solid #e7e8ea; border-radius: 10px; background: #f8f9fb; }
                .colt-admin__media { display: grid; gap: 8px; }
                .colt-admin__media-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; align-items: center; }
                .colt-admin__media-preview { min-height: 64px; border: 1px dashed #c3c4c7; border-radius: 8px; background: #fff; overflow: hidden; }
                .colt-admin__media-preview:empty { display: none; }
                .colt-admin__media-preview img { display: block; width: 100%; max-height: 160px; object-fit: contain; background: #f6f7f7; }
                @media (max-width: 1240px) { .colt-crm__stats { grid-template-columns: repeat(3, 1fr); } .colt-crm__bulk, .colt-crm__insights { grid-template-columns: repeat(2, 1fr); } }
                @media (max-width: 782px) { .colt-crm__hero, .colt-crm__footer-actions, .colt-crm__modal-head { display: grid; } .colt-crm__hero-actions { justify-items: start; } .colt-crm__stats, .colt-crm__filters, .colt-crm__bulk, .colt-crm__insights, .colt-crm__create-grid, .colt-crm__mapping, .colt-crm__slider-list { grid-template-columns: 1fr; } }
            </style>

            <h1>COLT Product CRM</h1>
            <p>ניהול מוצרים מהיר לחנות: עריכה רוחבית, מבצעים, מלאי, מכירות ותובנות בלי להיכנס מוצר-מוצר.</p>

            <?php if (!$this->is_woocommerce_ready()) : ?>
                <div class="notice notice-error"><p>WooCommerce לא פעיל או שסוג הפוסט Product לא זמין. הפעל את WooCommerce כדי להשתמש ב־Product CRM.</p></div>
            </div>
                <?php
                return;
            endif;

            $filters = $this->product_crm_filters();
            $period_days = (int) $filters['period'];
            $period_label = $this->product_crm_period_label($period_days);
            $categories = $this->product_crm_terms('product_cat');
            $tags = $this->product_crm_terms('product_tag');
            $products_query = $this->product_crm_query($filters);
            $stats = $this->product_crm_stats($period_days);
            $top_products = $this->product_crm_sales_products(6, 'DESC', $period_days);
            $slow_products = $this->product_crm_sales_products(6, 'ASC', $period_days);
            $low_stock = $this->product_crm_low_stock_products(8);
            $customers = $this->product_crm_customer_summary(8, $period_days);
            $promotions = get_option('colt_product_crm_promotions', []);
            $promotions = is_array($promotions) ? array_slice(array_reverse($promotions), 0, 5) : [];
            $sliders = $this->product_crm_sliders();
            $import_token = isset($_GET['import_token']) ? sanitize_key(wp_unslash($_GET['import_token'])) : '';
            $import_data = $import_token !== '' ? get_transient('colt_product_crm_import_' . $import_token) : [];
            $import_data = is_array($import_data) ? $import_data : [];
            $backorder_signups = $this->product_crm_backorder_signups(1000);
            ?>

            <div class="colt-crm__hero">
                <div>
                    <h2>מרכז שליטה למוצרים ומבצעים</h2>
                    <p>בחר מוצרים מהרשימה, הפעל שינויי bulk, צור קופונים, שמור כללי 3+1, ועקוב אחרי מוצרים חזקים, מוצרים שצריך להעיר ומלאי שדורש תשומת לב. הסטטיסטיקות כרגע מתייחסות ל<?php echo esc_html($period_label); ?>.</p>
                </div>
                <div class="colt-crm__hero-actions">
                    <button type="button" class="button button-primary button-hero" data-colt-crm-open-create>הקמת מוצר חדש</button>
                    <div class="colt-crm__hero-code">WooCommerce Admin</div>
                </div>
            </div>

            <?php if (isset($_GET['crm_updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html((int) $_GET['crm_updated']); ?> מוצרים עודכנו בהצלחה.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['product_created'])) : ?>
                <div class="notice notice-success is-dismissible"><p>המוצר החדש נוצר בהצלחה.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['product_updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p>המוצר עודכן בהצלחה.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['import_created']) || isset($_GET['import_updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p>ייבוא הסתיים: <?php echo esc_html((int) ($_GET['import_created'] ?? 0)); ?> מוצרים חדשים, <?php echo esc_html((int) ($_GET['import_updated'] ?? 0)); ?> מוצרים עודכנו, <?php echo esc_html((int) ($_GET['import_skipped'] ?? 0)); ?> שורות דולגו.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['crm_deleted'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html((int) $_GET['crm_deleted']); ?> מוצרים נמחקו או הועברו לפח.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['coupon_created'])) : ?>
                <div class="notice notice-success is-dismissible"><p>הקופון נוצר ושויך לפי הבחירה.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['promo_created'])) : ?>
                <div class="notice notice-success is-dismissible"><p>כלל המבצע נשמר ב־CRM.</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['slider_created'])) : ?>
                <?php $created_slider_id = sanitize_key(wp_unslash($_GET['slider_created'])); ?>
                <div class="notice notice-success is-dismissible"><p>הסליידר נוצר. השורטקוד שלך: <code class="colt-crm__shortcode">[colt_product_slider id="<?php echo esc_html($created_slider_id); ?>"]</code></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['crm_error'])) : ?>
                <div class="notice notice-error is-dismissible"><p><?php echo esc_html($this->product_crm_error_message((string) wp_unslash($_GET['crm_error']))); ?></p></div>
            <?php endif; ?>

            <div class="colt-crm__modal" data-colt-crm-create-modal aria-hidden="true">
                <div class="colt-crm__modal-card" role="dialog" aria-modal="true" aria-labelledby="colt-create-product-title">
                    <div class="colt-crm__modal-head">
                        <div>
                            <h2 id="colt-create-product-title">הקמת מוצר חדש</h2>
                            <p class="colt-crm__muted">מוצר WooCommerce רגיל עם שם, מחיר, מלאי, קטגוריות, תגיות, תיאור ותמונה ראשית.</p>
                        </div>
                        <button type="button" class="button" data-colt-crm-close-create>סגירה</button>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('colt_product_crm_create'); ?>
                        <input type="hidden" name="action" value="colt_product_crm_create">
                        <div class="colt-crm__create-grid">
                            <label class="colt-crm__wide">
                                <span>שם מוצר</span>
                                <input type="text" name="product_title" required placeholder="לדוגמה: Ascended Heroes Booster Bundle">
                            </label>
                            <label>
                                <span>סטטוס פרסום</span>
                                <select name="product_status">
                                    <option value="draft">טיוטה</option>
                                    <option value="publish">פרסום מיידי</option>
                                </select>
                            </label>
                            <label>
                                <span>SKU / מק"ט</span>
                                <input type="text" name="product_sku" placeholder="אופציונלי">
                            </label>
                            <label>
                                <span>סטטוס מלאי</span>
                                <select name="product_stock_status">
                                    <?php foreach ($this->product_crm_stock_statuses() as $status_key => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>מחיר רגיל</span>
                                <input type="number" name="product_regular_price" min="0" step="0.01" placeholder="0.00">
                            </label>
                            <label>
                                <span>מחיר מבצע</span>
                                <input type="number" name="product_sale_price" min="0" step="0.01" placeholder="אופציונלי">
                            </label>
                            <label>
                                <span>כמות במלאי</span>
                                <input type="number" name="product_stock_quantity" min="0" step="1" placeholder="אופציונלי">
                            </label>
                            <label>
                                <span>קטגוריות</span>
                                <select name="product_categories[]" multiple>
                                    <?php foreach ($categories as $term) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>תגיות</span>
                                <input type="text" name="product_tags" placeholder="פוקימון, יפנית, בוסטר">
                            </label>
                            <label>
                                <span>Set / סדרה</span>
                                <input type="text" name="product_set" placeholder="לדוגמה: Scarlet & Violet">
                            </label>
                            <label>
                                <span>שפה</span>
                                <select name="product_language">
                                    <option value="">לא מוגדר</option>
                                    <option value="English">English</option>
                                    <option value="Japanese">Japanese</option>
                                </select>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תמונה ראשית</span>
                                <?php $this->admin_media_input('product_image_url', ''); ?>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תיאור קצר</span>
                                <textarea name="product_short_description" placeholder="תיאור קצר שמופיע ליד המחיר בעמוד המוצר."></textarea>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תיאור מלא</span>
                                <textarea name="product_description" placeholder="כל מה שחשוב לדעת על המוצר: מה יש בפנים, למי מתאים, מצב, שפה, סט וכו׳."></textarea>
                            </label>
                        </div>
                        <p class="submit">
                            <button type="submit" class="button button-primary button-hero">יצירת מוצר</button>
                        </p>
                    </form>
                </div>
            </div>

            <div class="colt-crm__modal" data-colt-crm-edit-modal aria-hidden="true">
                <div class="colt-crm__modal-card" role="dialog" aria-modal="true" aria-labelledby="colt-edit-product-title">
                    <div class="colt-crm__modal-head">
                        <div>
                            <h2 id="colt-edit-product-title">עריכת מוצר</h2>
                            <p class="colt-crm__muted">שינוי מהיר של כל הפרטים המרכזיים של המוצר בלי לצאת מה־CRM.</p>
                        </div>
                        <button type="button" class="button" data-colt-crm-close-edit>סגירה</button>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('colt_product_crm_update'); ?>
                        <input type="hidden" name="action" value="colt_product_crm_update">
                        <input type="hidden" name="product_id" value="" data-colt-edit-field="id">
                        <div class="colt-crm__create-grid">
                            <label class="colt-crm__wide">
                                <span>שם מוצר</span>
                                <input type="text" name="product_title" required data-colt-edit-field="title">
                            </label>
                            <label>
                                <span>סטטוס פרסום</span>
                                <select name="product_status" data-colt-edit-field="status">
                                    <option value="draft">טיוטה</option>
                                    <option value="publish">פורסם</option>
                                    <option value="private">פרטי</option>
                                    <option value="pending">ממתין לאישור</option>
                                </select>
                            </label>
                            <label>
                                <span>SKU / מק"ט</span>
                                <input type="text" name="product_sku" data-colt-edit-field="sku">
                            </label>
                            <label>
                                <span>סטטוס מלאי</span>
                                <select name="product_stock_status" data-colt-edit-field="stockStatus">
                                    <?php foreach ($this->product_crm_stock_statuses() as $status_key => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>מחיר רגיל</span>
                                <input type="number" name="product_regular_price" min="0" step="0.01" data-colt-edit-field="regularPrice">
                            </label>
                            <label>
                                <span>מחיר מבצע</span>
                                <input type="number" name="product_sale_price" min="0" step="0.01" data-colt-edit-field="salePrice">
                            </label>
                            <label>
                                <span>כמות במלאי</span>
                                <input type="number" name="product_stock_quantity" min="0" step="1" data-colt-edit-field="stockQuantity">
                            </label>
                            <label>
                                <span>קטגוריות</span>
                                <select name="product_categories[]" multiple data-colt-edit-field="categoryIds">
                                    <?php foreach ($categories as $term) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>תגיות</span>
                                <input type="text" name="product_tags" data-colt-edit-field="tags">
                            </label>
                            <label>
                                <span>Set / סדרה</span>
                                <input type="text" name="product_set" data-colt-edit-field="setName">
                            </label>
                            <label>
                                <span>שפה</span>
                                <select name="product_language" data-colt-edit-field="language">
                                    <option value="">לא מוגדר</option>
                                    <option value="English">English</option>
                                    <option value="Japanese">Japanese</option>
                                </select>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תמונה ראשית</span>
                                <?php $this->admin_media_input('product_image_url', ''); ?>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תיאור קצר</span>
                                <textarea name="product_short_description" data-colt-edit-field="shortDescription"></textarea>
                            </label>
                            <label class="colt-crm__wide">
                                <span>תיאור מלא</span>
                                <textarea name="product_description" data-colt-edit-field="description"></textarea>
                            </label>
                        </div>
                        <p class="submit">
                            <button type="submit" class="button button-primary button-hero">שמירת מוצר</button>
                        </p>
                    </form>
                </div>
            </div>

            <?php $this->render_product_crm_import_panel($import_token, $import_data); ?>
            <?php $this->render_product_crm_slider_panel($sliders); ?>

            <section class="colt-crm__stats" aria-label="סקירת מוצרים">
                <?php foreach ($stats as $stat) : ?>
                    <div class="colt-crm__stat">
                        <strong><?php echo esc_html($stat['value']); ?></strong>
                        <span><?php echo esc_html($stat['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </section>

            <form class="colt-crm__panel colt-crm__filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                <input type="hidden" name="page" value="colt-product-crm">
                <label>
                    <span>חיפוש מוצר</span>
                    <input type="text" name="s" value="<?php echo esc_attr($filters['search']); ?>" placeholder="שם מוצר, סט, שפה...">
                </label>
                <label>
                    <span>סטטוס מלאי</span>
                    <select name="stock">
                        <option value="">הכל</option>
                        <?php foreach ($this->product_crm_stock_statuses() as $status_key => $status_label) : ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($filters['stock'], $status_key); ?>><?php echo esc_html($status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>קטגוריה</span>
                    <select name="product_cat">
                        <option value="0">כל הקטגוריות</option>
                        <?php foreach ($categories as $term) : ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($filters['category'], (int) $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>תגית</span>
                    <select name="product_tag">
                        <option value="0">כל התגיות</option>
                        <?php foreach ($tags as $term) : ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($filters['tag'], (int) $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>טווח סטטיסטיקות</span>
                    <select name="period">
                        <?php foreach ($this->product_crm_period_options() as $days => $label) : ?>
                            <option value="<?php echo esc_attr($days); ?>" <?php selected($period_days, (int) $days); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="button button-primary">סינון</button>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('colt_product_crm_bulk'); ?>
                <input type="hidden" name="action" value="colt_product_crm_bulk">

                <section class="colt-crm__panel">
                    <h2>פעולות Bulk</h2>
                    <div class="colt-crm__bulk">
                        <div class="colt-crm__bulk-card">
                            <h3>מלאי</h3>
                            <label>
                                <span>סטטוס מלאי</span>
                                <select name="stock_status">
                                    <option value="">ללא שינוי</option>
                                    <?php foreach ($this->product_crm_stock_statuses() as $status_key => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>כמות במלאי</span>
                                <input type="number" name="stock_quantity" min="0" step="1" placeholder="ללא שינוי">
                            </label>
                        </div>

                        <div class="colt-crm__bulk-card">
                            <h3>מחיר</h3>
                            <label>
                                <span>פעולת מחיר רגיל</span>
                                <select name="price_mode">
                                    <option value="">ללא שינוי</option>
                                    <option value="set">קבע מחיר</option>
                                    <option value="percent">שינוי באחוזים</option>
                                    <option value="fixed">הוספה/הפחתה קבועה</option>
                                </select>
                            </label>
                            <label>
                                <span>ערך</span>
                                <input type="number" name="price_value" step="0.01" placeholder="לדוגמה 10 או -5">
                            </label>
                            <label>
                                <span>מחיר מבצע</span>
                                <select name="sale_price_action">
                                    <option value="">ללא שינוי</option>
                                    <option value="set">קבע מחיר מבצע</option>
                                    <option value="clear">נקה מחיר מבצע</option>
                                </select>
                            </label>
                            <input type="number" name="sale_price_value" step="0.01" placeholder="מחיר מבצע">
                        </div>

                        <div class="colt-crm__bulk-card">
                            <h3>קטגוריות ותגיות</h3>
                            <label>
                                <span>מצב קטגוריות</span>
                                <select name="category_mode">
                                    <option value="">ללא שינוי</option>
                                    <option value="add">הוסף קטגוריות</option>
                                    <option value="replace">החלף קטגוריות</option>
                                </select>
                            </label>
                            <label>
                                <span>קטגוריות</span>
                                <select name="bulk_categories[]" multiple>
                                    <?php foreach ($categories as $term) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>תגיות להוספה</span>
                                <input type="text" name="bulk_tags" placeholder="יפנית, בוסטר, PSA">
                            </label>
                        </div>

                        <div class="colt-crm__bulk-card">
                            <h3>תמונה ותצוגה</h3>
                            <label>
                                <span>רוחב תצוגה</span>
                                <input type="number" name="image_width" min="0" step="1" placeholder="px">
                            </label>
                            <label>
                                <span>גובה תצוגה</span>
                                <input type="number" name="image_height" min="0" step="1" placeholder="px">
                            </label>
                            <label>
                                <span>התאמת תמונה</span>
                                <select name="image_fit">
                                    <option value="">ללא שינוי</option>
                                    <option value="cover">Cover</option>
                                    <option value="contain">Contain</option>
                                    <option value="fill">Fill</option>
                                </select>
                            </label>
                            <p class="colt-crm__note">נשמר כ־meta לתצוגה עתידית, בלי להרוס או לחתוך את קובץ התמונה המקורי.</p>
                        </div>

                        <div class="colt-crm__bulk-card">
                            <h3>קופון / 3+1</h3>
                            <label>
                                <span><input type="checkbox" name="create_coupon" value="1"> צור קופון לבחירה</span>
                            </label>
                            <input type="text" name="coupon_code" placeholder="COLT-DROP">
                            <select name="coupon_type">
                                <option value="percent">אחוז הנחה</option>
                                <option value="fixed_product">סכום למוצר</option>
                                <option value="fixed_cart">סכום לעגלה</option>
                            </select>
                            <input type="number" name="coupon_amount" min="0" step="0.01" placeholder="גובה הנחה">
                            <label>
                                <span>קטגוריות לקופון</span>
                                <select name="coupon_categories[]" multiple>
                                    <?php foreach ($categories as $term) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span><input type="checkbox" name="create_promo_rule" value="1"> שמור כלל 3+1</span>
                            </label>
                            <input type="text" name="promo_name" placeholder="3+1 מאותה קטגוריה">
                        </div>

                        <div class="colt-crm__bulk-card">
                            <h3>סליידר מוצרים</h3>
                            <label>
                                <span>שם הסליידר</span>
                                <input type="text" name="slider_name" placeholder="לדוגמה: פוקימון חדשים">
                            </label>
                            <p class="colt-crm__note">סמן מוצרים מהרשימה ולחץ כאן כדי לקבל שורטקוד לאלמנטור. הסליידר יציג תמונה 1:1, שם, מחיר, קטגוריות, מותגים ו־attributes.</p>
                            <button class="button button-secondary" type="submit" name="create_product_slider" value="1">צור סליידר מהמוצרים שסומנו</button>
                        </div>

                        <div class="colt-crm__bulk-card colt-crm__bulk-card--danger">
                            <h3>מחיקה</h3>
                            <label>
                                <span>פעולת מחיקה</span>
                                <select name="delete_action" data-colt-crm-delete-action>
                                    <option value="">ללא מחיקה</option>
                                    <option value="trash">העבר לפח</option>
                                    <option value="delete">מחיקה לצמיתות</option>
                                </select>
                            </label>
                            <label>
                                <span><input type="checkbox" name="confirm_delete" value="1"> אני מאשר למחוק את המוצרים שסומנו</span>
                            </label>
                            <p class="colt-crm__note">מחיקה לצמיתות לא ניתנת לשחזור מתוך וורדפרס. עדיף להשתמש קודם ב"העבר לפח".</p>
                        </div>
                    </div>
                </section>

                <section class="colt-crm__panel">
                    <div class="colt-crm__footer-actions">
                        <h2>מוצרים</h2>
                        <button class="button button-primary button-hero" type="submit">הפעל פעולות על המוצרים שסומנו</button>
                    </div>
                    <p class="colt-crm__muted">נמצאו <?php echo esc_html(number_format_i18n((int) $products_query->found_posts)); ?> מוצרים לפי הסינון הנוכחי.</p>

                    <div class="colt-crm__table-wrap">
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" data-colt-crm-check-all></th>
                                    <th>מוצר</th>
                                    <th>SKU</th>
                                    <th>מחיר</th>
                                    <th>מבצע</th>
                                    <th>מלאי</th>
                                    <th>כמות</th>
                                    <th>קטגוריות</th>
                                    <th>תגיות</th>
                                    <th>מכירות</th>
                                    <th>עודכן</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products_query->posts) : ?>
                                    <?php foreach ($products_query->posts as $product_id) : ?>
                                        <?php
                                        $product = wc_get_product($product_id);
                                        if (!$product) {
                                            continue;
                                        }
                                        $image = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail') ?: wc_placeholder_img_src('woocommerce_thumbnail');
                                        $full_image = get_the_post_thumbnail_url($product_id, 'full') ?: '';
                                        $stock_status = $product->get_stock_status();
                                        $regular_price = $product->get_regular_price();
                                        $sale_price = $product->get_sale_price();
                                        $edit_payload = [
                                            'id' => $product_id,
                                            'title' => get_the_title($product_id),
                                            'status' => get_post_status($product_id),
                                            'sku' => $product->get_sku(),
                                            'stockStatus' => $stock_status,
                                            'regularPrice' => $regular_price,
                                            'salePrice' => $sale_price,
                                            'stockQuantity' => $product->get_stock_quantity(),
                                            'categoryIds' => $this->product_crm_term_ids($product_id, 'product_cat'),
                                            'tags' => $this->product_crm_term_names($product_id, 'product_tag', ''),
                                            'setName' => (string) get_post_meta($product_id, '_colt_product_set', true),
                                            'language' => (string) get_post_meta($product_id, '_colt_product_language', true),
                                            'imageUrl' => $full_image,
                                            'shortDescription' => $product->get_short_description(),
                                            'description' => $product->get_description(),
                                        ];
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product_id); ?>"></td>
                                            <td>
                                                <div class="colt-crm__product">
                                                    <img class="colt-crm__thumb" src="<?php echo esc_url($image); ?>" alt="">
                                                    <span>
                                                        <strong><a href="<?php echo esc_url(get_edit_post_link($product_id)); ?>"><?php echo esc_html(get_the_title($product_id)); ?></a></strong>
                                                        <span class="colt-crm__muted">ID <?php echo esc_html((string) $product_id); ?> · <?php echo esc_html($product->get_type()); ?></span><br>
                                                        <button type="button" class="button button-small" data-colt-crm-edit-product="<?php echo esc_attr(wp_json_encode($edit_payload)); ?>">עריכה מהירה</button>
                                                    </span>
                                                </div>
                                            </td>
                                            <td><?php echo esc_html($product->get_sku() ?: '—'); ?></td>
                                            <td><?php echo $regular_price !== '' ? wp_kses_post(wc_price((float) $regular_price)) : '—'; ?></td>
                                            <td><?php echo $sale_price !== '' ? wp_kses_post(wc_price((float) $sale_price)) : '—'; ?></td>
                                            <td><?php echo wp_kses_post($this->product_crm_stock_badge($stock_status)); ?></td>
                                            <td><?php echo esc_html($product->get_stock_quantity() !== null ? (string) $product->get_stock_quantity() : '—'); ?></td>
                                            <td><?php echo esc_html($this->product_crm_term_names($product_id, 'product_cat')); ?></td>
                                            <td><?php echo esc_html($this->product_crm_term_names($product_id, 'product_tag')); ?></td>
                                            <td><?php echo esc_html(number_format_i18n((int) get_post_meta($product_id, 'total_sales', true))); ?></td>
                                            <td><?php echo esc_html(get_the_modified_date('d.m.Y', $product_id)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><td colspan="11">לא נמצאו מוצרים לפי הסינון הנוכחי.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php $pagination = $this->product_crm_pagination($products_query, $filters); ?>
                    <?php if ($pagination) : ?>
                        <div class="colt-crm__pagination"><?php echo wp_kses_post($pagination); ?></div>
                    <?php endif; ?>
                </section>
            </form>

            <section class="colt-crm__insights">
                <div class="colt-crm__panel">
                    <h2>מוצרים מובילים</h2>
                    <?php $this->render_product_crm_product_list($top_products, 'יחידות'); ?>
                </div>
                <div class="colt-crm__panel">
                    <h2>מוצרים שצריך להעיר</h2>
                    <?php $this->render_product_crm_product_list($slow_products, 'יחידות'); ?>
                </div>
                <div class="colt-crm__panel">
                    <h2>התראות מלאי</h2>
                    <?php $this->render_product_crm_product_list($low_stock, 'במלאי'); ?>
                </div>
            </section>

            <section class="colt-crm__insights">
                <div class="colt-crm__panel">
                    <h2>לקוחות מובילים</h2>
                    <?php if ($customers) : ?>
                        <ul class="colt-crm__list">
                            <?php foreach ($customers as $customer) : ?>
                                <li>
                                    <span>
                                        <strong><?php echo esc_html($customer['name']); ?></strong><br>
                                        <span class="colt-crm__muted"><?php echo esc_html($customer['email']); ?> · <?php echo esc_html((string) $customer['orders']); ?> הזמנות</span>
                                    </span>
                                    <strong><?php echo wp_kses_post(wc_price($customer['total'])); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p>עדיין אין מספיק הזמנות להצגה.</p>
                    <?php endif; ?>
                </div>

                <div class="colt-crm__panel">
                    <h2>כללי מבצע שמורים</h2>
                    <?php if ($promotions) : ?>
                        <ul class="colt-crm__list">
                            <?php foreach ($promotions as $promotion) : ?>
                                <li>
                                    <span>
                                        <strong><?php echo esc_html($promotion['name'] ?? 'מבצע'); ?></strong><br>
                                        <span class="colt-crm__muted"><?php echo esc_html(($promotion['buy_qty'] ?? 3) . '+' . ($promotion['get_qty'] ?? 1)); ?> · <?php echo esc_html($promotion['status'] ?? 'draft'); ?></span>
                                    </span>
                                    <span class="colt-crm__badge">Rule</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p>עדיין לא נשמרו כללי מבצע. אפשר ליצור כלל 3+1 מתוך אזור ה־bulk.</p>
                    <?php endif; ?>
                    <p class="colt-crm__note">כללי 3+1 שנשמרים כאן פעילים בעגלה ומחושבים לפי המוצרים או הקטגוריות שנבחרו.</p>
                </div>

                <div class="colt-crm__panel">
                    <h2>פעולות מומלצות</h2>
                    <ul class="colt-crm__list">
                        <li><span>מוצרים ללא מכירות</span><strong>בדיקת מחיר / תמונה</strong></li>
                        <li><span>מלאי נמוך</span><strong>הזמנה / הסתרה זמנית</strong></li>
                        <li><span>קטגוריות חזקות</span><strong>קופון ממוקד</strong></li>
                        <li><span>לקוחות חוזרים</span><strong>דרופ VIP</strong></li>
                    </ul>
                </div>
            </section>

            <section class="colt-crm__panel">
                <h2>הרשמות Backorder לעדכוני מלאי</h2>
                <?php $this->render_product_crm_backorder_signup_list($backorder_signups); ?>
            </section>
        </div>
        <script>
        document.addEventListener('change', function (event) {
            if (!event.target.matches('[data-colt-crm-check-all]')) {
                return;
            }
            document.querySelectorAll('.colt-crm input[name="product_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = event.target.checked;
            });
        });
        document.addEventListener('click', function (event) {
            const openButton = event.target.closest('[data-colt-crm-open-create]');
            const closeButton = event.target.closest('[data-colt-crm-close-create]');
            const editButton = event.target.closest('[data-colt-crm-edit-product]');
            const editCloseButton = event.target.closest('[data-colt-crm-close-edit]');
            const modal = document.querySelector('[data-colt-crm-create-modal]');
            const editModal = document.querySelector('[data-colt-crm-edit-modal]');

            if (openButton && modal) {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            if (closeButton && modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            if (event.target === modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            if (editButton && editModal) {
                let payload = {};
                try {
                    payload = JSON.parse(editButton.getAttribute('data-colt-crm-edit-product') || '{}');
                } catch (error) {
                    payload = {};
                }

                editModal.querySelectorAll('[data-colt-edit-field]').forEach(function (field) {
                    const key = field.getAttribute('data-colt-edit-field');
                    const value = payload[key] ?? '';

                    if (field.tagName === 'SELECT' && field.multiple) {
                        const values = Array.isArray(value) ? value.map(String) : [];
                        Array.from(field.options).forEach(function (option) {
                            option.selected = values.includes(option.value);
                        });
                        return;
                    }

                    field.value = value === null ? '' : value;
                });

                const mediaInput = editModal.querySelector('.colt-admin__media input[type="text"]');
                const mediaPreview = editModal.querySelector('.colt-admin__media-preview');
                if (mediaInput) {
                    mediaInput.value = payload.imageUrl || '';
                }
                if (mediaPreview) {
                    mediaPreview.innerHTML = '';
                    if (payload.imageUrl) {
                        const previewImage = document.createElement('img');
                        previewImage.src = payload.imageUrl;
                        previewImage.alt = '';
                        mediaPreview.appendChild(previewImage);
                    }
                }

                editModal.classList.add('is-open');
                editModal.setAttribute('aria-hidden', 'false');
            }

            if (editCloseButton && editModal) {
                editModal.classList.remove('is-open');
                editModal.setAttribute('aria-hidden', 'true');
            }

            if (event.target === editModal) {
                editModal.classList.remove('is-open');
                editModal.setAttribute('aria-hidden', 'true');
            }
        });
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.matches('.colt-crm form')) {
                return;
            }
            const deleteAction = form.querySelector('[data-colt-crm-delete-action]');
            if (!deleteAction || !deleteAction.value) {
                return;
            }
            const confirmed = form.querySelector('input[name="confirm_delete"]:checked');
            const selected = form.querySelectorAll('input[name="product_ids[]"]:checked').length;
            if (!confirmed || selected < 1) {
                event.preventDefault();
                alert('צריך לסמן מוצרים ולאשר מחיקה לפני שממשיכים.');
                return;
            }
            if (!window.confirm('אתה בטוח שברצונך למחוק את המוצרים שסומנו?')) {
                event.preventDefault();
            }
        });
        </script>
        <?php
    }

    public function handle_product_crm_bulk_action()
    {
        if (!$this->can_manage_product_crm()) {
            wp_die(esc_html__('You do not have permission to manage products.', 'colt-experience'));
        }

        check_admin_referer('colt_product_crm_bulk');

        if (!$this->is_woocommerce_ready()) {
            $this->product_crm_redirect(['crm_error' => 'woocommerce']);
        }

        $product_ids = $this->product_crm_posted_ids('product_ids');
        if (!$product_ids) {
            $this->product_crm_redirect(['crm_error' => 'no_products']);
        }

        if (!empty($_POST['create_product_slider'])) {
            $slider_name = isset($_POST['slider_name']) ? sanitize_text_field(wp_unslash($_POST['slider_name'])) : '';
            $slider_id = $this->product_crm_create_slider($product_ids, $slider_name);
            $this->product_crm_redirect($slider_id !== '' ? ['slider_created' => $slider_id] : ['crm_error' => 'slider_create']);
        }

        $delete_action = isset($_POST['delete_action']) ? sanitize_key(wp_unslash($_POST['delete_action'])) : '';
        if (in_array($delete_action, ['trash', 'delete'], true)) {
            if (empty($_POST['confirm_delete'])) {
                $this->product_crm_redirect(['crm_error' => 'confirm_delete']);
            }

            $deleted = $this->product_crm_delete_products($product_ids, $delete_action === 'delete');
            $this->product_crm_redirect(['crm_deleted' => $deleted]);
        }

        $stock_status = isset($_POST['stock_status']) ? sanitize_key(wp_unslash($_POST['stock_status'])) : '';
        $stock_statuses = array_keys($this->product_crm_stock_statuses());
        if (!in_array($stock_status, $stock_statuses, true)) {
            $stock_status = '';
        }

        $stock_quantity_raw = isset($_POST['stock_quantity']) ? trim((string) wp_unslash($_POST['stock_quantity'])) : '';
        $stock_quantity = $stock_quantity_raw !== '' && is_numeric($stock_quantity_raw) ? max(0, (int) $stock_quantity_raw) : null;
        $price_mode = isset($_POST['price_mode']) ? sanitize_key(wp_unslash($_POST['price_mode'])) : '';
        $price_value_raw = isset($_POST['price_value']) ? trim((string) wp_unslash($_POST['price_value'])) : '';
        $price_value = $price_value_raw !== '' && is_numeric($price_value_raw) ? (float) $price_value_raw : null;
        $sale_action = isset($_POST['sale_price_action']) ? sanitize_key(wp_unslash($_POST['sale_price_action'])) : '';
        $sale_price_raw = isset($_POST['sale_price_value']) ? trim((string) wp_unslash($_POST['sale_price_value'])) : '';
        $sale_price = $sale_price_raw !== '' && is_numeric($sale_price_raw) ? max(0, (float) $sale_price_raw) : null;
        $category_mode = isset($_POST['category_mode']) ? sanitize_key(wp_unslash($_POST['category_mode'])) : '';
        $category_ids = $this->product_crm_posted_ids('bulk_categories');
        $tag_names = $this->product_crm_posted_tags();
        $image_width = isset($_POST['image_width']) ? absint(wp_unslash($_POST['image_width'])) : 0;
        $image_height = isset($_POST['image_height']) ? absint(wp_unslash($_POST['image_height'])) : 0;
        $image_fit = isset($_POST['image_fit']) ? sanitize_key(wp_unslash($_POST['image_fit'])) : '';
        if (!in_array($image_fit, ['cover', 'contain', 'fill'], true)) {
            $image_fit = '';
        }

        $updated = 0;

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                continue;
            }

            $changed = false;

            if ($stock_status !== '') {
                $product->set_stock_status($stock_status);
                if ($stock_status === 'onbackorder') {
                    if (method_exists($product, 'set_backorders')) {
                        $product->set_backorders('no');
                    }
                    if ($stock_quantity === null) {
                        $product->set_manage_stock(true);
                        $product->set_stock_quantity(0);
                    }
                }
                $changed = true;
            }

            if ($stock_quantity !== null) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock_quantity);
                $changed = true;
            }

            if ($price_mode !== '' && $price_value !== null) {
                $base_price = $product->get_regular_price() !== '' ? (float) $product->get_regular_price() : (float) $product->get_price();
                if ($price_mode === 'set') {
                    $product->set_regular_price(wc_format_decimal(max(0, $price_value)));
                    $changed = true;
                } elseif ($price_mode === 'percent' && $base_price > 0) {
                    $product->set_regular_price(wc_format_decimal(max(0, $base_price * (1 + ($price_value / 100)))));
                    $changed = true;
                } elseif ($price_mode === 'fixed' && $base_price >= 0) {
                    $product->set_regular_price(wc_format_decimal(max(0, $base_price + $price_value)));
                    $changed = true;
                }
            }

            if ($sale_action === 'clear') {
                $product->set_sale_price('');
                $changed = true;
            } elseif ($sale_action === 'set' && $sale_price !== null) {
                $product->set_sale_price(wc_format_decimal($sale_price));
                $changed = true;
            }

            if ($changed) {
                $product->save();
            }

            if ($category_mode === 'replace' && $category_ids) {
                wp_set_object_terms($product_id, $category_ids, 'product_cat', false);
                $changed = true;
            } elseif ($category_mode === 'add' && $category_ids) {
                $current_categories = wp_get_object_terms($product_id, 'product_cat', ['fields' => 'ids']);
                $current_categories = is_wp_error($current_categories) ? [] : array_map('absint', $current_categories);
                wp_set_object_terms($product_id, array_values(array_unique(array_merge($current_categories, $category_ids))), 'product_cat', false);
                $changed = true;
            }

            if ($tag_names) {
                wp_set_object_terms($product_id, $tag_names, 'product_tag', true);
                $changed = true;
            }

            if ($image_width > 0) {
                update_post_meta($product_id, '_colt_crm_image_width', $image_width);
                $changed = true;
            }
            if ($image_height > 0) {
                update_post_meta($product_id, '_colt_crm_image_height', $image_height);
                $changed = true;
            }
            if ($image_fit !== '') {
                update_post_meta($product_id, '_colt_crm_image_fit', $image_fit);
                $changed = true;
            }

            if ($stock_status === 'onbackorder') {
                update_post_meta($product_id, '_colt_backorder_signup_enabled', '1');
                $changed = true;
            } elseif ($stock_status !== '') {
                delete_post_meta($product_id, '_colt_backorder_signup_enabled');
            }

            if ($changed) {
                $updated++;
            }
        }

        $redirect_args = ['crm_updated' => $updated];
        $coupon_categories = $this->product_crm_posted_ids('coupon_categories');
        if (!empty($_POST['create_coupon'])) {
            $coupon_id = $this->product_crm_create_coupon($product_ids, $coupon_categories);
            $redirect_args[$coupon_id ? 'coupon_created' : 'crm_error'] = $coupon_id ? '1' : 'coupon';
        }

        if (!empty($_POST['create_promo_rule'])) {
            $promo_created = $this->product_crm_create_promo_rule($product_ids, $coupon_categories ?: $category_ids);
            $redirect_args[$promo_created ? 'promo_created' : 'crm_error'] = $promo_created ? '1' : 'promo';
        }

        $this->product_crm_redirect($redirect_args);
    }

    public function handle_product_crm_create_product()
    {
        if (!$this->can_manage_product_crm()) {
            wp_die(esc_html__('You do not have permission to create products.', 'colt-experience'));
        }

        check_admin_referer('colt_product_crm_create');

        if (!$this->is_woocommerce_ready() || !class_exists('WC_Product_Simple')) {
            $this->product_crm_redirect(['crm_error' => 'woocommerce']);
        }

        $title = isset($_POST['product_title']) ? sanitize_text_field(wp_unslash($_POST['product_title'])) : '';
        if ($title === '') {
            $this->product_crm_redirect(['crm_error' => 'product_title']);
        }

        $status = isset($_POST['product_status']) ? sanitize_key(wp_unslash($_POST['product_status'])) : 'draft';
        if (!in_array($status, ['draft', 'publish'], true)) {
            $status = 'draft';
        }

        $stock_status = isset($_POST['product_stock_status']) ? sanitize_key(wp_unslash($_POST['product_stock_status'])) : 'instock';
        if (!in_array($stock_status, array_keys($this->product_crm_stock_statuses()), true)) {
            $stock_status = 'instock';
        }

        $regular_price = $this->product_crm_decimal_from_post('product_regular_price');
        $sale_price = $this->product_crm_decimal_from_post('product_sale_price');
        $stock_quantity_raw = isset($_POST['product_stock_quantity']) ? trim((string) wp_unslash($_POST['product_stock_quantity'])) : '';
        $stock_quantity = $stock_quantity_raw !== '' && is_numeric($stock_quantity_raw) ? max(0, (int) $stock_quantity_raw) : null;
        $sku = isset($_POST['product_sku']) ? sanitize_text_field(wp_unslash($_POST['product_sku'])) : '';
        $short_description = isset($_POST['product_short_description']) ? wp_kses_post(wp_unslash($_POST['product_short_description'])) : '';
        $description = isset($_POST['product_description']) ? wp_kses_post(wp_unslash($_POST['product_description'])) : '';
        $image_url = isset($_POST['product_image_url']) ? esc_url_raw(wp_unslash($_POST['product_image_url'])) : '';
        $set_name = isset($_POST['product_set']) ? sanitize_text_field(wp_unslash($_POST['product_set'])) : '';
        $language = isset($_POST['product_language']) ? sanitize_text_field(wp_unslash($_POST['product_language'])) : '';
        if (!in_array($language, ['', 'English', 'Japanese'], true)) {
            $language = '';
        }

        try {
            $product = new WC_Product_Simple();
            $product->set_name($title);
            $product->set_status($status);
            $product->set_catalog_visibility('visible');
            $product->set_stock_status($stock_status);
            $product->set_description($description);
            $product->set_short_description($short_description);

            if ($sku !== '') {
                $product->set_sku($sku);
            }

            if ($regular_price !== null) {
                $product->set_regular_price(wc_format_decimal($regular_price));
            }

            if ($sale_price !== null) {
                $product->set_sale_price(wc_format_decimal($sale_price));
            }

            if ($stock_quantity !== null) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock_quantity);
            }

            if ($stock_status === 'onbackorder') {
                if (method_exists($product, 'set_backorders')) {
                    $product->set_backorders('no');
                }
                $product->set_manage_stock(true);
                $product->set_stock_quantity(0);
            }

            $image_id = $this->product_crm_attachment_id_from_url($image_url);
            if ($image_id > 0) {
                $product->set_image_id($image_id);
            }

            $product_id = (int) $product->save();
        } catch (Exception $exception) {
            $this->product_crm_redirect(['crm_error' => 'product_create']);
        }

        if ($product_id <= 0) {
            $this->product_crm_redirect(['crm_error' => 'product_create']);
        }

        $category_ids = $this->product_crm_posted_ids('product_categories');
        if ($category_ids) {
            wp_set_object_terms($product_id, $category_ids, 'product_cat', false);
        }

        $tag_names = $this->product_crm_posted_tags('product_tags');
        if ($tag_names) {
            wp_set_object_terms($product_id, $tag_names, 'product_tag', true);
        }

        if ($image_url !== '' && empty($image_id)) {
            update_post_meta($product_id, '_colt_crm_source_image_url', $image_url);
        }

        if ($set_name !== '') {
            update_post_meta($product_id, '_colt_product_set', $set_name);
        }

        if ($language !== '') {
            update_post_meta($product_id, '_colt_product_language', $language);
        }

        if ($stock_status === 'onbackorder') {
            update_post_meta($product_id, '_colt_backorder_signup_enabled', '1');
        }

        $this->product_crm_redirect(['product_created' => $product_id]);
    }

    public function handle_product_crm_update_product()
    {
        if (!$this->can_manage_product_crm()) {
            wp_die(esc_html__('You do not have permission to update products.', 'colt-experience'));
        }

        check_admin_referer('colt_product_crm_update');

        if (!$this->is_woocommerce_ready()) {
            $this->product_crm_redirect(['crm_error' => 'woocommerce']);
        }

        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $product = $product_id > 0 ? wc_get_product($product_id) : null;
        if (!$product) {
            $this->product_crm_redirect(['crm_error' => 'product_update']);
        }

        $title = isset($_POST['product_title']) ? sanitize_text_field(wp_unslash($_POST['product_title'])) : '';
        if ($title === '') {
            $this->product_crm_redirect(['crm_error' => 'product_title']);
        }

        $status = isset($_POST['product_status']) ? sanitize_key(wp_unslash($_POST['product_status'])) : 'draft';
        if (!in_array($status, ['draft', 'publish', 'private', 'pending'], true)) {
            $status = 'draft';
        }

        $stock_status = isset($_POST['product_stock_status']) ? sanitize_key(wp_unslash($_POST['product_stock_status'])) : 'instock';
        if (!in_array($stock_status, array_keys($this->product_crm_stock_statuses()), true)) {
            $stock_status = 'instock';
        }

        $regular_price = $this->product_crm_decimal_from_post('product_regular_price');
        $sale_price = $this->product_crm_decimal_from_post('product_sale_price');
        $stock_quantity_raw = isset($_POST['product_stock_quantity']) ? trim((string) wp_unslash($_POST['product_stock_quantity'])) : '';
        $stock_quantity = $stock_quantity_raw !== '' && is_numeric($stock_quantity_raw) ? max(0, (int) $stock_quantity_raw) : null;
        $sku = isset($_POST['product_sku']) ? sanitize_text_field(wp_unslash($_POST['product_sku'])) : '';
        $short_description = isset($_POST['product_short_description']) ? wp_kses_post(wp_unslash($_POST['product_short_description'])) : '';
        $description = isset($_POST['product_description']) ? wp_kses_post(wp_unslash($_POST['product_description'])) : '';
        $image_url = isset($_POST['product_image_url']) ? esc_url_raw(wp_unslash($_POST['product_image_url'])) : '';
        $set_name = isset($_POST['product_set']) ? sanitize_text_field(wp_unslash($_POST['product_set'])) : '';
        $language = isset($_POST['product_language']) ? sanitize_text_field(wp_unslash($_POST['product_language'])) : '';
        if (!in_array($language, ['', 'English', 'Japanese'], true)) {
            $language = '';
        }

        try {
            $product->set_name($title);
            $product->set_status($status);
            $product->set_stock_status($stock_status);
            $product->set_description($description);
            $product->set_short_description($short_description);

            if ($sku !== '') {
                $product->set_sku($sku);
            } else {
                $product->set_sku('');
            }

            $product->set_regular_price($regular_price !== null ? wc_format_decimal($regular_price) : '');
            $product->set_sale_price($sale_price !== null ? wc_format_decimal($sale_price) : '');

            if ($stock_quantity !== null) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock_quantity);
            }

            if ($stock_status === 'onbackorder') {
                if (method_exists($product, 'set_backorders')) {
                    $product->set_backorders('no');
                }
                $product->set_manage_stock(true);
                $product->set_stock_quantity(0);
            }

            $image_id = $this->product_crm_attachment_id_from_url($image_url);
            if ($image_id > 0) {
                $product->set_image_id($image_id);
            } elseif ($image_url === '') {
                $product->set_image_id(0);
            }

            $product->save();
        } catch (Exception $exception) {
            $this->product_crm_redirect(['crm_error' => 'product_update']);
        }

        wp_set_object_terms($product_id, $this->product_crm_posted_ids('product_categories'), 'product_cat', false);
        $tag_names = $this->product_crm_posted_tags('product_tags');
        wp_set_object_terms($product_id, $tag_names, 'product_tag', false);

        if ($image_url !== '' && empty($image_id)) {
            update_post_meta($product_id, '_colt_crm_source_image_url', $image_url);
        } else {
            delete_post_meta($product_id, '_colt_crm_source_image_url');
        }

        $set_name !== '' ? update_post_meta($product_id, '_colt_product_set', $set_name) : delete_post_meta($product_id, '_colt_product_set');
        $language !== '' ? update_post_meta($product_id, '_colt_product_language', $language) : delete_post_meta($product_id, '_colt_product_language');

        if ($stock_status === 'onbackorder') {
            update_post_meta($product_id, '_colt_backorder_signup_enabled', '1');
        } else {
            delete_post_meta($product_id, '_colt_backorder_signup_enabled');
        }

        $this->product_crm_redirect(['product_updated' => $product_id]);
    }

    public function handle_product_crm_upload_xlsx()
    {
        if (!$this->can_manage_product_crm()) {
            wp_die(esc_html__('You do not have permission to import products.', 'colt-experience'));
        }

        check_admin_referer('colt_product_crm_upload_xlsx');

        if (empty($_FILES['xlsx_file']['tmp_name']) || !is_uploaded_file($_FILES['xlsx_file']['tmp_name'])) {
            $this->product_crm_redirect(['crm_error' => 'xlsx_upload']);
        }

        $filename = isset($_FILES['xlsx_file']['name']) ? sanitize_file_name(wp_unslash($_FILES['xlsx_file']['name'])) : '';
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'xlsx') {
            $this->product_crm_redirect(['crm_error' => 'xlsx_type']);
        }

        $parsed = $this->product_crm_parse_xlsx($_FILES['xlsx_file']['tmp_name'], 700);
        if (is_wp_error($parsed)) {
            $this->product_crm_redirect(['crm_error' => $parsed->get_error_code()]);
        }

        $token = strtolower(wp_generate_password(14, false, false));
        set_transient('colt_product_crm_import_' . $token, $parsed, HOUR_IN_SECONDS);

        $this->product_crm_redirect(['import_token' => $token]);
    }

    public function handle_product_crm_import_xlsx()
    {
        if (!$this->can_manage_product_crm()) {
            wp_die(esc_html__('You do not have permission to import products.', 'colt-experience'));
        }

        check_admin_referer('colt_product_crm_import_xlsx');

        if (!$this->is_woocommerce_ready() || !class_exists('WC_Product_Simple')) {
            $this->product_crm_redirect(['crm_error' => 'woocommerce']);
        }

        $token = isset($_POST['import_token']) ? sanitize_key(wp_unslash($_POST['import_token'])) : '';
        $data = $token !== '' ? get_transient('colt_product_crm_import_' . $token) : [];
        if (!is_array($data) || empty($data['headers']) || empty($data['rows'])) {
            $this->product_crm_redirect(['crm_error' => 'xlsx_expired']);
        }

        $identifier_column = isset($_POST['identifier_column']) ? sanitize_text_field(wp_unslash($_POST['identifier_column'])) : '';
        $identifier_target = isset($_POST['identifier_target']) ? sanitize_key(wp_unslash($_POST['identifier_target'])) : 'name_exact';
        if (!in_array($identifier_target, ['name_exact', 'sku', 'product_id'], true)) {
            $identifier_target = 'name_exact';
        }

        $field_map = isset($_POST['field_map']) && is_array($_POST['field_map']) ? wp_unslash($_POST['field_map']) : [];
        $allowed_fields = array_keys($this->product_crm_import_fields());
        $create_missing = !empty($_POST['create_missing']);
        $update_existing = !empty($_POST['update_existing']);
        $new_status = isset($_POST['new_product_status']) ? sanitize_key(wp_unslash($_POST['new_product_status'])) : 'draft';
        if (!in_array($new_status, ['draft', 'publish'], true)) {
            $new_status = 'draft';
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data['rows'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $identifier = trim((string) ($row[$identifier_column] ?? ''));
            $product_id = $identifier !== '' ? $this->product_crm_find_product_by_identifier($identifier_target, $identifier) : 0;
            $values = [];

            foreach ($field_map as $column => $field) {
                $column = (string) $column;
                $field = sanitize_key((string) $field);
                if ($field === 'skip' || !in_array($field, $allowed_fields, true)) {
                    continue;
                }

                $cell_value = isset($row[$column]) ? trim((string) $row[$column]) : '';
                if ($cell_value === '') {
                    continue;
                }

                $values[$field] = $cell_value;
            }

            if (empty($values['name']) && $identifier_target === 'name_exact' && $identifier !== '') {
                $values['name'] = $identifier;
            }

            if ($product_id > 0) {
                if (!$update_existing) {
                    $skipped++;
                    continue;
                }

                $result = $this->product_crm_apply_import_values($product_id, $values, $new_status);
                if (is_wp_error($result)) {
                    $skipped++;
                    continue;
                }
                $updated++;
                continue;
            }

            if (!$create_missing || empty($values['name'])) {
                $skipped++;
                continue;
            }

            $result = $this->product_crm_apply_import_values(0, $values, $new_status);
            if (is_wp_error($result)) {
                $skipped++;
                continue;
            }
            $created++;
        }

        delete_transient('colt_product_crm_import_' . $token);

        $this->product_crm_redirect([
            'import_created' => $created,
            'import_updated' => $updated,
            'import_skipped' => $skipped,
        ]);
    }

    private function render_product_crm_import_panel($import_token, array $import_data)
    {
        $headers = isset($import_data['headers']) && is_array($import_data['headers']) ? $import_data['headers'] : [];
        $rows = isset($import_data['rows']) && is_array($import_data['rows']) ? $import_data['rows'] : [];
        ?>
        <section class="colt-crm__panel">
            <h2>ייבוא מוצרים מקובץ XLSX</h2>
            <p>מעלים קובץ, בוחרים שדה מזהה, ואז ממפים כל עמודה מהאקסל לשדה באתר. אם בוחרים זיהוי לפי שם, התאמה מדויקת בשם תיחשב אותו מוצר.</p>

            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="colt-crm__filters">
                <?php wp_nonce_field('colt_product_crm_upload_xlsx'); ?>
                <input type="hidden" name="action" value="colt_product_crm_upload_xlsx">
                <label>
                    <span>קובץ XLSX</span>
                    <input type="file" name="xlsx_file" accept=".xlsx" required>
                </label>
                <button type="submit" class="button button-primary">טעינת קובץ ומעבר למיפוי</button>
            </form>

            <?php if ($import_token !== '' && $headers && $rows) : ?>
                <hr>
                <h3>מיפוי שדות מהאקסל לאתר</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('colt_product_crm_import_xlsx'); ?>
                    <input type="hidden" name="action" value="colt_product_crm_import_xlsx">
                    <input type="hidden" name="import_token" value="<?php echo esc_attr($import_token); ?>">

                    <div class="colt-crm__filters">
                        <label>
                            <span>עמודת מזהה באקסל</span>
                            <select name="identifier_column" required>
                                <?php foreach ($headers as $header) : ?>
                                    <option value="<?php echo esc_attr($header); ?>"><?php echo esc_html($header); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>לפי איזה שדה מזהים באתר</span>
                            <select name="identifier_target">
                                <option value="name_exact">שם מוצר - התאמה מדויקת</option>
                                <option value="sku">SKU / מק"ט</option>
                                <option value="product_id">ID מוצר</option>
                            </select>
                        </label>
                        <label>
                            <span>סטטוס למוצרים חדשים</span>
                            <select name="new_product_status">
                                <option value="draft">טיוטה</option>
                                <option value="publish">פרסום מיידי</option>
                            </select>
                        </label>
                        <label>
                            <span><input type="checkbox" name="create_missing" value="1" checked> צור מוצרים חדשים אם לא נמצא מוצר קיים</span>
                        </label>
                        <label>
                            <span><input type="checkbox" name="update_existing" value="1" checked> עדכן מוצרים קיימים שנמצאו</span>
                        </label>
                    </div>

                    <div class="colt-crm__mapping">
                        <?php foreach ($headers as $header) : ?>
                            <label>
                                <span><?php echo esc_html($header); ?></span>
                                <select name="field_map[<?php echo esc_attr($header); ?>]">
                                    <?php foreach ($this->product_crm_import_fields() as $field_key => $field_label) : ?>
                                        <option value="<?php echo esc_attr($field_key); ?>" <?php selected($this->product_crm_guess_import_field($header), $field_key); ?>><?php echo esc_html($field_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <h3>תצוגה מקדימה</h3>
                    <div class="colt-crm__import-preview">
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <?php foreach ($headers as $header) : ?>
                                        <th><?php echo esc_html($header); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($rows, 0, 5) as $row) : ?>
                                    <tr>
                                        <?php foreach ($headers as $header) : ?>
                                            <td><?php echo esc_html((string) ($row[$header] ?? '')); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero">ייבוא / עדכון מוצרים</button>
                    </p>
                </form>
            <?php elseif ($import_token !== '') : ?>
                <p class="colt-crm__note">הקובץ הזמני כבר לא זמין. העלה אותו שוב כדי להמשיך במיפוי.</p>
            <?php endif; ?>
        </section>
        <?php
    }

    private function product_crm_parse_xlsx($file_path, $max_rows = 700)
    {
        if (!class_exists('ZipArchive')) {
            return new WP_Error('xlsx_zip', 'ZipArchive is not available.');
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) !== true) {
            return new WP_Error('xlsx_open', 'Could not open XLSX file.');
        }

        $shared_strings = [];
        $shared_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($shared_xml !== false) {
            $shared = simplexml_load_string($shared_xml);
            if ($shared) {
                foreach ($shared->si as $item) {
                    if (isset($item->t)) {
                        $shared_strings[] = (string) $item->t;
                        continue;
                    }

                    $text = '';
                    if (isset($item->r)) {
                        foreach ($item->r as $run) {
                            $text .= (string) $run->t;
                        }
                    }
                    $shared_strings[] = $text;
                }
            }
        }

        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet_xml === false) {
            $zip->close();
            return new WP_Error('xlsx_sheet', 'Could not find the first worksheet.');
        }

        $sheet = simplexml_load_string($sheet_xml);
        if (!$sheet || !isset($sheet->sheetData->row)) {
            $zip->close();
            return new WP_Error('xlsx_sheet', 'Could not read worksheet rows.');
        }

        $raw_rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $matches);
                $column_index = $this->product_crm_xlsx_column_index($matches[0] ?? 'A');
                $type = (string) $cell['t'];
                $value = '';

                if ($type === 's') {
                    $string_index = isset($cell->v) ? (int) $cell->v : -1;
                    $value = $shared_strings[$string_index] ?? '';
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                } elseif (isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                $cells[$column_index] = trim($value);
            }

            if (array_filter($cells, static function ($value) {
                return $value !== '';
            })) {
                ksort($cells);
                $raw_rows[] = $cells;
            }

            if (count($raw_rows) >= $max_rows + 1) {
                break;
            }
        }

        $zip->close();

        if (!$raw_rows) {
            return new WP_Error('xlsx_empty', 'The worksheet is empty.');
        }

        $header_row = array_shift($raw_rows);
        $headers = [];
        $used_headers = [];
        $max_column = max(array_keys($header_row));

        for ($column = 0; $column <= $max_column; $column++) {
            $header = trim((string) ($header_row[$column] ?? ''));
            if ($header === '') {
                $header = 'Column ' . ($column + 1);
            }

            $base_header = $header;
            $suffix = 2;
            while (isset($used_headers[$header])) {
                $header = $base_header . ' #' . $suffix;
                $suffix++;
            }
            $used_headers[$header] = true;
            $headers[$column] = $header;
        }

        $rows = [];
        foreach ($raw_rows as $raw_row) {
            $row = [];
            foreach ($headers as $column => $header) {
                $row[$header] = trim((string) ($raw_row[$column] ?? ''));
            }

            if (array_filter($row, static function ($value) {
                return $value !== '';
            })) {
                $rows[] = $row;
            }
        }

        if (!$rows) {
            return new WP_Error('xlsx_empty', 'No product rows were found.');
        }

        return [
            'headers' => array_values($headers),
            'rows' => $rows,
            'uploaded' => time(),
        ];
    }

    private function product_crm_xlsx_column_index($letters)
    {
        $letters = strtoupper((string) $letters);
        $index = 0;
        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function product_crm_import_fields()
    {
        return [
            'skip' => 'לא לייבא',
            'name' => 'שם מוצר',
            'sku' => 'SKU / מק"ט',
            'regular_price' => 'מחיר רגיל',
            'sale_price' => 'מחיר מבצע',
            'stock_quantity' => 'כמות מלאי',
            'stock_status' => 'סטטוס מלאי',
            'short_description' => 'תיאור קצר',
            'description' => 'תיאור מלא',
            'categories' => 'קטגוריות',
            'tags' => 'תגיות',
            'image_url' => 'תמונה ראשית URL',
            'set' => 'Set / סדרה',
            'language' => 'שפה',
        ];
    }

    private function product_crm_guess_import_field($header)
    {
        $normalized = strtolower(trim((string) $header));
        $normalized = str_replace([' ', '-', '_'], '', $normalized);

        $map = [
            'name' => 'name',
            'title' => 'name',
            'שם' => 'name',
            'שםמוצר' => 'name',
            'sku' => 'sku',
            'מק"ט' => 'sku',
            'מקט' => 'sku',
            'regularprice' => 'regular_price',
            'price' => 'regular_price',
            'מחיר' => 'regular_price',
            'saleprice' => 'sale_price',
            'מבצע' => 'sale_price',
            'stock' => 'stock_quantity',
            'quantity' => 'stock_quantity',
            'מלאי' => 'stock_quantity',
            'stockstatus' => 'stock_status',
            'status' => 'stock_status',
            'shortdescription' => 'short_description',
            'תיאורקצר' => 'short_description',
            'description' => 'description',
            'תיאור' => 'description',
            'categories' => 'categories',
            'category' => 'categories',
            'קטגוריות' => 'categories',
            'tags' => 'tags',
            'תגיות' => 'tags',
            'image' => 'image_url',
            'images' => 'image_url',
            'imageurl' => 'image_url',
            'תמונה' => 'image_url',
            'set' => 'set',
            'סט' => 'set',
            'language' => 'language',
            'שפה' => 'language',
        ];

        return $map[$normalized] ?? 'skip';
    }

    private function product_crm_find_product_by_identifier($target, $identifier)
    {
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return 0;
        }

        if ($target === 'product_id') {
            $product_id = absint($identifier);
            return $product_id > 0 && get_post_type($product_id) === 'product' ? $product_id : 0;
        }

        if ($target === 'sku' && function_exists('wc_get_product_id_by_sku')) {
            return absint(wc_get_product_id_by_sku($identifier));
        }

        global $wpdb;
        $product_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title = %s AND post_status != 'trash' LIMIT 1",
            $identifier
        ));

        return $product_id > 0 ? $product_id : 0;
    }

    private function product_crm_apply_import_values($product_id, array $values, $new_status)
    {
        $product = $product_id > 0 ? wc_get_product($product_id) : new WC_Product_Simple();
        if (!$product) {
            return new WP_Error('product_missing', 'Product not found.');
        }

        if ($product_id <= 0 && empty($values['name'])) {
            return new WP_Error('product_name', 'Product name is required.');
        }

        try {
            if (!empty($values['name'])) {
                $product->set_name(sanitize_text_field($values['name']));
            }

            if ($product_id <= 0) {
                $product->set_status($new_status);
                $product->set_catalog_visibility('visible');
            }

            if (!empty($values['sku'])) {
                $product->set_sku(sanitize_text_field($values['sku']));
            }

            if (isset($values['regular_price'])) {
                $product->set_regular_price(wc_format_decimal($this->product_crm_decimal_value($values['regular_price'])));
            }

            if (isset($values['sale_price'])) {
                $product->set_sale_price(wc_format_decimal($this->product_crm_decimal_value($values['sale_price'])));
            }

            if (isset($values['stock_quantity']) && is_numeric($values['stock_quantity'])) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity(max(0, (int) $values['stock_quantity']));
            }

            if (!empty($values['stock_status'])) {
                $stock_status = $this->product_crm_normalize_stock_status($values['stock_status']);
                $product->set_stock_status($stock_status);
                if ($stock_status === 'onbackorder') {
                    if (method_exists($product, 'set_backorders')) {
                        $product->set_backorders('no');
                    }
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity(0);
                }
            }

            if (isset($values['short_description'])) {
                $product->set_short_description(wp_kses_post($values['short_description']));
            }

            if (isset($values['description'])) {
                $product->set_description(wp_kses_post($values['description']));
            }

            if (!empty($values['image_url'])) {
                $image_id = $this->product_crm_attachment_id_from_url(esc_url_raw($values['image_url']));
                if ($image_id > 0) {
                    $product->set_image_id($image_id);
                }
            }

            $saved_id = (int) $product->save();
        } catch (Exception $exception) {
            return new WP_Error('product_save', $exception->getMessage());
        }

        if ($saved_id <= 0) {
            return new WP_Error('product_save', 'Product could not be saved.');
        }

        if (isset($values['categories'])) {
            wp_set_object_terms($saved_id, $this->product_crm_split_terms($values['categories']), 'product_cat', false);
        }

        if (isset($values['tags'])) {
            wp_set_object_terms($saved_id, $this->product_crm_split_terms($values['tags']), 'product_tag', false);
        }

        if (isset($values['set'])) {
            update_post_meta($saved_id, '_colt_product_set', sanitize_text_field($values['set']));
        }

        if (isset($values['language'])) {
            update_post_meta($saved_id, '_colt_product_language', sanitize_text_field($values['language']));
        }

        $stock_status = !empty($values['stock_status']) ? $this->product_crm_normalize_stock_status($values['stock_status']) : '';
        if ($stock_status === 'onbackorder') {
            update_post_meta($saved_id, '_colt_backorder_signup_enabled', '1');
        } elseif (array_key_exists('stock_status', $values)) {
            delete_post_meta($saved_id, '_colt_backorder_signup_enabled');
        }

        return $saved_id;
    }

    private function product_crm_split_terms($value)
    {
        $parts = preg_split('/[,|]+/', (string) $value);
        $parts = array_map('trim', is_array($parts) ? $parts : []);
        $parts = array_map('sanitize_text_field', $parts);

        return array_values(array_filter($parts));
    }

    private function product_crm_normalize_stock_status($value)
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace([' ', '-', '_'], '', $value);

        if (in_array($value, ['outofstock', 'אזל', 'אזלמהמלאי', 'לאבמלאי'], true)) {
            return 'outofstock';
        }

        if (in_array($value, ['backorder', 'backorders', 'onbackorder', 'preorder', 'הזמנהמוקדמת'], true)) {
            return 'onbackorder';
        }

        return 'instock';
    }

    private function can_manage_product_crm()
    {
        return current_user_can('manage_options') || current_user_can('manage_woocommerce') || current_user_can('edit_products');
    }

    private function is_woocommerce_ready()
    {
        return function_exists('wc_get_product') && function_exists('wc_get_orders') && post_type_exists('product');
    }

    private function product_crm_filters()
    {
        return [
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'stock' => isset($_GET['stock']) ? sanitize_key(wp_unslash($_GET['stock'])) : '',
            'category' => isset($_GET['product_cat']) ? absint(wp_unslash($_GET['product_cat'])) : 0,
            'tag' => isset($_GET['product_tag']) ? absint(wp_unslash($_GET['product_tag'])) : 0,
            'period' => $this->product_crm_normalize_period(isset($_GET['period']) ? absint(wp_unslash($_GET['period'])) : 30),
            'paged' => max(1, isset($_GET['paged']) ? absint(wp_unslash($_GET['paged'])) : 1),
        ];
    }

    private function product_crm_query(array $filters)
    {
        $args = [
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 30,
            'paged' => max(1, (int) $filters['paged']),
            'fields' => 'ids',
        ];

        if ($filters['search'] !== '') {
            $args['s'] = $filters['search'];
        }

        $tax_query = [];
        if ($filters['category'] > 0) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => [$filters['category']],
            ];
        }
        if ($filters['tag'] > 0) {
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => [$filters['tag']],
            ];
        }
        if ($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        if ($filters['stock'] !== '' && in_array($filters['stock'], array_keys($this->product_crm_stock_statuses()), true)) {
            $args['meta_query'] = [
                [
                    'key' => '_stock_status',
                    'value' => $filters['stock'],
                ],
            ];
        }

        return new WP_Query($args);
    }

    private function product_crm_terms($taxonomy)
    {
        if (!taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        return is_wp_error($terms) ? [] : $terms;
    }

    private function product_crm_stock_statuses()
    {
        return [
            'instock' => 'במלאי',
            'outofstock' => 'אזל מהמלאי',
            'onbackorder' => 'Backorder / טופס עדכונים',
        ];
    }

    private function product_crm_stock_badge($status)
    {
        $labels = $this->product_crm_stock_statuses();
        $class = 'colt-crm__badge';
        if ($status === 'instock') {
            $class .= ' colt-crm__badge--in';
        } elseif ($status === 'outofstock') {
            $class .= ' colt-crm__badge--out';
        } elseif ($status === 'onbackorder') {
            $class .= ' colt-crm__badge--back';
        }

        return '<span class="' . esc_attr($class) . '">' . esc_html($labels[$status] ?? $status) . '</span>';
    }

    private function product_crm_period_options()
    {
        return [
            7 => '7 ימים אחרונים',
            30 => '30 ימים אחרונים',
            90 => '90 ימים אחרונים',
            365 => 'שנה אחרונה',
            0 => 'כל הזמן',
        ];
    }

    private function product_crm_normalize_period($days)
    {
        $days = (int) $days;
        return array_key_exists($days, $this->product_crm_period_options()) ? $days : 30;
    }

    private function product_crm_period_label($days)
    {
        $options = $this->product_crm_period_options();
        return $options[$this->product_crm_normalize_period($days)] ?? $options[30];
    }

    private function product_crm_order_query_args($limit, $days)
    {
        $args = [
            'limit' => $limit,
            'status' => ['processing', 'completed', 'on-hold'],
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $days = $this->product_crm_normalize_period($days);
        if ($days > 0) {
            $args['date_created'] = '>' . (time() - ($days * DAY_IN_SECONDS));
        }

        return $args;
    }

    private function product_crm_stats($days = 30)
    {
        $counts = wp_count_posts('product');
        $total = 0;
        foreach (['publish', 'draft', 'pending', 'private'] as $status) {
            $total += isset($counts->{$status}) ? (int) $counts->{$status} : 0;
        }

        $stock_counts = [];
        foreach (array_keys($this->product_crm_stock_statuses()) as $status) {
            $stock_query = new WP_Query([
                'post_type' => 'product',
                'post_status' => ['publish', 'draft', 'pending', 'private'],
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => '_stock_status',
                        'value' => $status,
                    ],
                ],
            ]);
            $stock_counts[$status] = (int) $stock_query->found_posts;
        }

        $stock_value = 0;
        $value_query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 300,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        foreach ($value_query->posts as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                continue;
            }
            $qty = $product->get_stock_quantity();
            $qty = $qty === null ? 1 : max(0, (int) $qty);
            $stock_value += (float) $product->get_price() * $qty;
        }

        $period_orders = [];
        if (function_exists('wc_get_orders')) {
            $period_orders = wc_get_orders($this->product_crm_order_query_args(-1, $days));
        }
        $period_revenue = 0;
        $period_units = 0;

        foreach ($period_orders as $order) {
            if (!is_object($order) || !method_exists($order, 'get_total')) {
                continue;
            }
            $period_revenue += (float) $order->get_total();
            foreach ($order->get_items() as $item) {
                $period_units += (int) $item->get_quantity();
            }
        }

        return [
            ['value' => number_format_i18n($total), 'label' => 'סה"כ מוצרים'],
            ['value' => number_format_i18n($stock_counts['instock'] ?? 0), 'label' => 'במלאי'],
            ['value' => number_format_i18n($stock_counts['outofstock'] ?? 0), 'label' => 'אזל'],
            ['value' => number_format_i18n(count($period_orders)), 'label' => 'הזמנות - ' . $this->product_crm_period_label($days)],
            ['value' => wp_strip_all_tags(wc_price($period_revenue)), 'label' => 'מכירות - ' . $this->product_crm_period_label($days)],
            ['value' => number_format_i18n($period_units), 'label' => 'יחידות נמכרו - ' . $this->product_crm_period_label($days)],
            ['value' => wp_strip_all_tags(wc_price($stock_value)), 'label' => 'ערך מלאי משוער'],
        ];
    }

    private function product_crm_sales_products($limit, $order, $days = 30)
    {
        $days = $this->product_crm_normalize_period($days);

        if ($days > 0 && function_exists('wc_get_orders')) {
            $orders = wc_get_orders($this->product_crm_order_query_args(-1, $days));
            $sales = [];

            foreach ($orders as $order_item_source) {
                if (!is_object($order_item_source) || !method_exists($order_item_source, 'get_items')) {
                    continue;
                }

                foreach ($order_item_source->get_items() as $item) {
                    $product_id = (int) $item->get_product_id();
                    if ($product_id <= 0) {
                        continue;
                    }
                    if (!isset($sales[$product_id])) {
                        $sales[$product_id] = 0;
                    }
                    $sales[$product_id] += (int) $item->get_quantity();
                }
            }

            if ($order === 'ASC') {
                $candidate_query = new WP_Query([
                    'post_type' => 'product',
                    'post_status' => ['publish', 'draft', 'private'],
                    'posts_per_page' => 120,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                ]);

                foreach ($candidate_query->posts as $product_id) {
                    if (!isset($sales[$product_id])) {
                        $sales[$product_id] = 0;
                    }
                }
                asort($sales, SORT_NUMERIC);
            } else {
                arsort($sales, SORT_NUMERIC);
            }

            $rows = [];
            foreach (array_slice($sales, 0, max(1, (int) $limit), true) as $product_id => $quantity) {
                $rows[] = [
                    'id' => (int) $product_id,
                    'title' => get_the_title($product_id),
                    'value' => (int) $quantity,
                    'url' => get_edit_post_link($product_id),
                ];
            }

            return $rows;
        }

        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => max(1, (int) $limit),
            'fields' => 'ids',
            'meta_key' => 'total_sales',
            'orderby' => 'meta_value_num',
            'order' => $order === 'ASC' ? 'ASC' : 'DESC',
            'no_found_rows' => true,
        ]);

        $rows = [];
        foreach ($query->posts as $product_id) {
            $rows[] = [
                'id' => $product_id,
                'title' => get_the_title($product_id),
                'value' => (int) get_post_meta($product_id, 'total_sales', true),
                'url' => get_edit_post_link($product_id),
            ];
        }

        return $rows;
    }

    private function product_crm_low_stock_products($limit)
    {
        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => max(1, (int) $limit),
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_stock',
                    'value' => 5,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                ],
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => '_stock',
            'order' => 'ASC',
            'no_found_rows' => true,
        ]);

        $rows = [];
        foreach ($query->posts as $product_id) {
            $rows[] = [
                'id' => $product_id,
                'title' => get_the_title($product_id),
                'value' => (int) get_post_meta($product_id, '_stock', true),
                'url' => get_edit_post_link($product_id),
            ];
        }

        return $rows;
    }

    private function product_crm_customer_summary($limit, $days = 30)
    {
        if (!function_exists('wc_get_orders')) {
            return [];
        }

        $orders = wc_get_orders($this->product_crm_order_query_args(120, $days));

        $customers = [];
        foreach ($orders as $order) {
            if (!is_object($order) || !method_exists($order, 'get_billing_email')) {
                continue;
            }

            $email = strtolower(trim((string) $order->get_billing_email()));
            if ($email === '') {
                $email = 'guest-' . (string) $order->get_id();
            }

            if (!isset($customers[$email])) {
                $name = trim((string) $order->get_billing_first_name() . ' ' . (string) $order->get_billing_last_name());
                $customers[$email] = [
                    'name' => $name !== '' ? $name : 'לקוח אורח',
                    'email' => $email,
                    'orders' => 0,
                    'total' => 0,
                ];
            }

            $customers[$email]['orders']++;
            $customers[$email]['total'] += (float) $order->get_total();
        }

        usort($customers, static function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        return array_slice($customers, 0, max(1, (int) $limit));
    }

    private function render_product_crm_product_list(array $rows, $value_label)
    {
        if (!$rows) {
            echo '<p>אין נתונים להצגה עדיין.</p>';
            return;
        }
        ?>
        <ul class="colt-crm__list">
            <?php foreach ($rows as $row) : ?>
                <li>
                    <span>
                        <a href="<?php echo esc_url($row['url']); ?>"><?php echo esc_html($row['title']); ?></a><br>
                        <span class="colt-crm__muted">ID <?php echo esc_html((string) $row['id']); ?></span>
                    </span>
                    <strong><?php echo esc_html(number_format_i18n((int) $row['value']) . ' ' . $value_label); ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    private function render_product_crm_slider_panel(array $sliders)
    {
        ?>
        <section class="colt-crm__panel">
            <h2>סליידרים שנוצרו</h2>
            <p class="colt-crm__muted">כל סליידר נשמר לפי המוצרים שסימנת בזמן היצירה. את השורטקוד אפשר לשים באלמנטור או בכל עמוד WordPress.</p>
            <?php if (!$sliders) : ?>
                <p>עדיין לא נוצרו סליידרים. סמן מוצרים מהרשימה ולחץ על “צור סליידר מהמוצרים שסומנו”.</p>
            <?php else : ?>
                <div class="colt-crm__slider-list">
                    <?php foreach ($sliders as $slider_id => $slider) : ?>
                        <?php
                        $product_count = isset($slider['product_ids']) && is_array($slider['product_ids']) ? count($slider['product_ids']) : 0;
                        $shortcode = '[colt_product_slider id="' . $slider_id . '"]';
                        ?>
                        <div class="colt-crm__slider-card">
                            <strong><?php echo esc_html($slider['name'] ?? 'סליידר מוצרים'); ?></strong>
                            <span class="colt-crm__muted"><?php echo esc_html(number_format_i18n($product_count)); ?> מוצרים · נוצר <?php echo esc_html(!empty($slider['created']) ? wp_date('d.m.Y H:i', (int) $slider['created']) : ''); ?></span>
                            <code class="colt-crm__shortcode"><?php echo esc_html($shortcode); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    private function product_crm_sliders()
    {
        $sliders = get_option('colt_product_crm_sliders', []);
        if (!is_array($sliders)) {
            return [];
        }

        $normalized = [];
        foreach ($sliders as $slider_id => $slider) {
            if (!is_array($slider)) {
                continue;
            }

            $slider_id = sanitize_key((string) ($slider['id'] ?? $slider_id));
            if ($slider_id === '') {
                continue;
            }

            $product_ids = isset($slider['product_ids']) && is_array($slider['product_ids'])
                ? array_values(array_filter(array_unique(array_map('absint', $slider['product_ids']))))
                : [];

            if (!$product_ids) {
                continue;
            }

            $normalized[$slider_id] = [
                'id' => $slider_id,
                'name' => isset($slider['name']) ? sanitize_text_field((string) $slider['name']) : 'סליידר מוצרים',
                'product_ids' => $product_ids,
                'created' => isset($slider['created']) ? (int) $slider['created'] : 0,
            ];
        }

        uasort($normalized, static function ($left, $right) {
            return ((int) ($right['created'] ?? 0)) <=> ((int) ($left['created'] ?? 0));
        });

        return $normalized;
    }

    private function product_crm_create_slider(array $product_ids, $name = '')
    {
        $product_ids = array_values(array_filter(array_unique(array_map('absint', $product_ids)), static function ($product_id) {
            return $product_id > 0 && get_post_type($product_id) === 'product' && get_post_status($product_id) !== 'trash';
        }));

        if (!$product_ids) {
            return '';
        }

        $sliders = $this->product_crm_sliders();
        $slider_id = 'slider_' . gmdate('ymd_His') . '_' . wp_rand(100, 999);
        $name = trim((string) $name);
        if ($name === '') {
            $name = 'סליידר מוצרים ' . wp_date('d.m.Y H:i');
        }

        $sliders[$slider_id] = [
            'id' => $slider_id,
            'name' => sanitize_text_field($name),
            'product_ids' => $product_ids,
            'created' => time(),
        ];

        update_option('colt_product_crm_sliders', $sliders, false);

        return $slider_id;
    }

    private function product_slider_term_names($product_id, $taxonomy)
    {
        if (!taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_the_terms($product_id, $taxonomy);
        if (!$terms || is_wp_error($terms)) {
            return [];
        }

        return array_values(array_filter(array_map('sanitize_text_field', wp_list_pluck($terms, 'name'))));
    }

    private function product_slider_brand_names($product_id)
    {
        $brand_taxonomies = [];
        $common_taxonomies = ['product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand', 'pa_brands', 'pa_מותג'];

        foreach ($common_taxonomies as $taxonomy) {
            if (taxonomy_exists($taxonomy)) {
                $brand_taxonomies[] = $taxonomy;
            }
        }

        $product_taxonomies = get_object_taxonomies('product', 'objects');
        if (is_array($product_taxonomies)) {
            foreach ($product_taxonomies as $taxonomy => $object) {
                if (in_array($taxonomy, ['product_cat', 'product_tag'], true)) {
                    continue;
                }

                $label = isset($object->label) ? (string) $object->label : '';
                $haystack = strtolower($taxonomy . ' ' . $label);
                if (strpos($haystack, 'brand') !== false || strpos($haystack, 'מותג') !== false) {
                    $brand_taxonomies[] = $taxonomy;
                }
            }
        }

        $brand_names = [];
        foreach (array_unique($brand_taxonomies) as $taxonomy) {
            $brand_names = array_merge($brand_names, $this->product_slider_term_names($product_id, $taxonomy));
        }

        return array_values(array_unique($brand_names));
    }

    private function product_slider_attributes($product)
    {
        if (!$product || !is_object($product) || !method_exists($product, 'get_attributes')) {
            return [];
        }

        $rows = [];
        foreach ($product->get_attributes() as $attribute) {
            if (!is_object($attribute) || !method_exists($attribute, 'get_name')) {
                continue;
            }

            $name = (string) $attribute->get_name();
            $label = function_exists('wc_attribute_label') ? wc_attribute_label($name) : $name;
            $values = [];

            if (method_exists($attribute, 'is_taxonomy') && $attribute->is_taxonomy()) {
                $terms = function_exists('wc_get_product_terms') ? wc_get_product_terms($product->get_id(), $name, ['fields' => 'names']) : [];
                $values = is_wp_error($terms) ? [] : (array) $terms;
            } elseif (method_exists($attribute, 'get_options')) {
                $values = (array) $attribute->get_options();
            }

            $values = array_values(array_filter(array_map('sanitize_text_field', array_map('strval', $values))));
            if (!$values) {
                continue;
            }

            $rows[] = [
                'label' => sanitize_text_field((string) $label),
                'value' => implode(', ', $values),
            ];
        }

        return $rows;
    }

    private function product_crm_term_ids($product_id, $taxonomy)
    {
        $terms = get_the_terms($product_id, $taxonomy);
        if (!$terms || is_wp_error($terms)) {
            return [];
        }

        return array_values(array_map('absint', wp_list_pluck($terms, 'term_id')));
    }

    private function product_crm_term_names($product_id, $taxonomy, $fallback = '—')
    {
        $terms = get_the_terms($product_id, $taxonomy);
        if (!$terms || is_wp_error($terms)) {
            return $fallback;
        }

        return implode(', ', wp_list_pluck($terms, 'name'));
    }

    private function product_crm_pagination(WP_Query $query, array $filters)
    {
        if ((int) $query->max_num_pages < 2) {
            return '';
        }

        return paginate_links([
            'base' => add_query_arg([
                'page' => 'colt-product-crm',
                's' => $filters['search'],
                'stock' => $filters['stock'],
                'product_cat' => $filters['category'],
                'product_tag' => $filters['tag'],
                'period' => $filters['period'],
                'paged' => '%#%',
            ], admin_url('admin.php')),
            'format' => '',
            'current' => max(1, (int) $filters['paged']),
            'total' => (int) $query->max_num_pages,
            'prev_text' => '‹',
            'next_text' => '›',
        ]);
    }

    private function product_crm_posted_ids($field)
    {
        if (!isset($_POST[$field])) {
            return [];
        }

        $value = wp_unslash($_POST[$field]);
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_values(array_filter(array_unique(array_map('absint', $value))));
    }

    private function product_crm_delete_products(array $product_ids, $force_delete = false)
    {
        $deleted = 0;

        foreach ($product_ids as $product_id) {
            if (!current_user_can('delete_post', $product_id)) {
                continue;
            }

            $result = $force_delete ? wp_delete_post($product_id, true) : wp_trash_post($product_id);
            if ($result) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function product_crm_decimal_from_post($field)
    {
        $raw = isset($_POST[$field]) ? trim((string) wp_unslash($_POST[$field])) : '';
        if ($raw === '') {
            return null;
        }

        return $this->product_crm_decimal_value($raw);
    }

    private function product_crm_decimal_value($value)
    {
        $value = trim((string) $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || !is_numeric($value)) {
            return 0;
        }

        return max(0, (float) $value);
    }

    private function product_crm_attachment_id_from_url($url)
    {
        $url = (string) $url;
        if ($url === '' || !function_exists('attachment_url_to_postid')) {
            return 0;
        }

        $attachment_id = attachment_url_to_postid($url);
        if (!$attachment_id && strpos($url, '/wp-content/uploads/') === 0) {
            $attachment_id = attachment_url_to_postid(home_url($url));
        }

        return absint($attachment_id);
    }

    private function product_crm_posted_tags($field = 'bulk_tags')
    {
        $raw = isset($_POST[$field]) ? (string) wp_unslash($_POST[$field]) : '';
        if ($raw === '') {
            return [];
        }

        $tags = array_map('trim', explode(',', $raw));
        $tags = array_map('sanitize_text_field', $tags);

        return array_values(array_filter($tags));
    }

    private function product_crm_create_coupon(array $product_ids, array $category_ids)
    {
        if (!post_type_exists('shop_coupon')) {
            return 0;
        }

        $code = isset($_POST['coupon_code']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['coupon_code']))) : '';
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);
        if ($code === '') {
            $code = 'COLT-' . gmdate('ymd-His');
        }

        $type = isset($_POST['coupon_type']) ? sanitize_key(wp_unslash($_POST['coupon_type'])) : 'percent';
        if (!in_array($type, ['percent', 'fixed_product', 'fixed_cart'], true)) {
            $type = 'percent';
        }

        $amount_raw = isset($_POST['coupon_amount']) ? trim((string) wp_unslash($_POST['coupon_amount'])) : '';
        $amount = $amount_raw !== '' && is_numeric($amount_raw) ? max(0, (float) $amount_raw) : 0;
        if ($amount <= 0) {
            return 0;
        }

        $coupon_id = wp_insert_post([
            'post_title' => $code,
            'post_excerpt' => 'Created from COLT Product CRM',
            'post_status' => 'publish',
            'post_type' => 'shop_coupon',
        ], true);

        if (is_wp_error($coupon_id) || !$coupon_id) {
            return 0;
        }

        update_post_meta($coupon_id, 'discount_type', $type);
        update_post_meta($coupon_id, 'coupon_amount', wc_format_decimal($amount));
        update_post_meta($coupon_id, 'individual_use', 'no');
        update_post_meta($coupon_id, 'usage_limit', '');
        update_post_meta($coupon_id, 'usage_limit_per_user', '');
        update_post_meta($coupon_id, 'product_ids', implode(',', $product_ids));
        update_post_meta($coupon_id, 'product_categories', $category_ids);
        update_post_meta($coupon_id, '_colt_product_crm_created', time());

        return (int) $coupon_id;
    }

    private function product_crm_create_promo_rule(array $product_ids, array $category_ids)
    {
        $name = isset($_POST['promo_name']) ? sanitize_text_field(wp_unslash($_POST['promo_name'])) : '';
        if ($name === '') {
            $name = '3+1 מאותה קטגוריה';
        }

        $rules = get_option('colt_product_crm_promotions', []);
        if (!is_array($rules)) {
            $rules = [];
        }

        $rules[] = [
            'id' => 'promo_' . time() . '_' . wp_rand(1000, 9999),
            'name' => $name,
            'type' => 'buy_x_get_y',
            'buy_qty' => 3,
            'get_qty' => 1,
            'scope' => $category_ids ? 'categories' : 'products',
            'product_ids' => array_values(array_map('absint', $product_ids)),
            'category_ids' => array_values(array_map('absint', $category_ids)),
            'status' => 'active',
            'created' => time(),
        ];

        update_option('colt_product_crm_promotions', $rules, false);

        return true;
    }

    public function apply_product_crm_promo_rules($cart)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }

        if (!$cart || !method_exists($cart, 'get_cart') || !method_exists($cart, 'add_fee')) {
            return;
        }

        $rules = get_option('colt_product_crm_promotions', []);
        if (!is_array($rules) || !$rules) {
            return;
        }

        foreach ($rules as $rule) {
            if (($rule['status'] ?? '') !== 'active' || ($rule['type'] ?? '') !== 'buy_x_get_y') {
                continue;
            }

            $buy_qty = max(1, (int) ($rule['buy_qty'] ?? 3));
            $get_qty = max(1, (int) ($rule['get_qty'] ?? 1));
            $group_size = $buy_qty + $get_qty;
            $eligible_prices = [];
            $product_ids = array_map('absint', $rule['product_ids'] ?? []);
            $category_ids = array_map('absint', $rule['category_ids'] ?? []);

            foreach ($cart->get_cart() as $cart_item) {
                $product = $cart_item['data'] ?? null;
                if (!$product || !method_exists($product, 'get_id') || !method_exists($product, 'get_price')) {
                    continue;
                }

                $product_id = (int) $product->get_id();
                $parent_id = method_exists($product, 'get_parent_id') ? (int) $product->get_parent_id() : 0;
                $matches_product = $product_ids && (in_array($product_id, $product_ids, true) || ($parent_id && in_array($parent_id, $product_ids, true)));
                $matches_category = false;

                if ($category_ids) {
                    $term_product_id = $parent_id ?: $product_id;
                    $matches_category = has_term($category_ids, 'product_cat', $term_product_id);
                }

                if (!$matches_product && !$matches_category) {
                    continue;
                }

                $qty = max(0, (int) ($cart_item['quantity'] ?? 0));
                $unit_price = max(0, (float) $product->get_price());
                for ($i = 0; $i < $qty; $i++) {
                    $eligible_prices[] = $unit_price;
                }
            }

            if (count($eligible_prices) < $group_size) {
                continue;
            }

            sort($eligible_prices, SORT_NUMERIC);
            $free_units = (int) floor(count($eligible_prices) / $group_size) * $get_qty;
            $discount = array_sum(array_slice($eligible_prices, 0, $free_units));

            if ($discount > 0) {
                $cart->add_fee($rule['name'] ?? 'COLT 3+1', -1 * $discount, false);
            }
        }
    }

    public function maybe_disable_backorder_purchase($purchasable, $product)
    {
        if ($this->is_product_backorder_signup_enabled($product)) {
            return false;
        }

        return $purchasable;
    }

    public function render_backorder_signup_form()
    {
        global $product;

        if (!$this->is_product_backorder_signup_enabled($product)) {
            return;
        }

        $product_id = (int) $product->get_id();
        ?>
        <div class="colt-backorder-signup" dir="rtl" style="margin:18px 0;padding:18px;border:1px solid #d6c28a;border-radius:12px;background:#10151c;color:#fff;">
            <h3 style="margin:0 0 8px;font-size:22px;">המוצר לא במלאי כרגע</h3>
            <p style="margin:0 0 14px;color:#d8d3c4;">השאירו פרטים ונעדכן אתכם כשהמוצר חוזר או כשיש אפשרות הזמנה מתאימה.</p>
            <?php if (isset($_GET['colt_backorder_signup'])) : ?>
                <p style="padding:10px;border-radius:8px;background:#163d2f;color:#bff8db;">נרשמת בהצלחה לעדכונים על המוצר.</p>
            <?php elseif (isset($_GET['colt_backorder_error'])) : ?>
                <p style="padding:10px;border-radius:8px;background:#4b1d1d;color:#ffd5d5;">צריך להזין אימייל תקין כדי שנוכל לעדכן אותך.</p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:grid;gap:10px;">
                <?php wp_nonce_field('colt_backorder_signup_' . $product_id); ?>
                <input type="hidden" name="action" value="colt_backorder_signup">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                <input type="text" name="customer_name" placeholder="שם מלא" style="width:100%;min-height:42px;">
                <input type="email" name="customer_email" placeholder="אימייל לעדכון" required style="width:100%;min-height:42px;">
                <input type="tel" name="customer_phone" placeholder="טלפון / וואטסאפ" style="width:100%;min-height:42px;">
                <button type="submit" class="button" style="min-height:42px;background:#d6b45d;border-color:#d6b45d;color:#10151c;font-weight:800;">עדכנו אותי כשהמוצר חוזר</button>
            </form>
        </div>
        <?php
    }

    public function handle_backorder_signup()
    {
        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if ($product_id <= 0 || !wp_verify_nonce($nonce, 'colt_backorder_signup_' . $product_id)) {
            wp_die(esc_html__('Invalid signup request.', 'colt-experience'));
        }

        $email = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
        if ($email === '') {
            wp_safe_redirect(add_query_arg('colt_backorder_error', 'email', get_permalink($product_id)));
            exit;
        }

        $signups = get_option('colt_backorder_signups', []);
        if (!is_array($signups)) {
            $signups = [];
        }

        array_unshift($signups, [
            'id' => 'signup_' . time() . '_' . wp_rand(1000, 9999),
            'product_id' => $product_id,
            'product_name' => get_the_title($product_id),
            'name' => isset($_POST['customer_name']) ? sanitize_text_field(wp_unslash($_POST['customer_name'])) : '',
            'email' => $email,
            'phone' => isset($_POST['customer_phone']) ? sanitize_text_field(wp_unslash($_POST['customer_phone'])) : '',
            'created' => time(),
        ]);

        update_option('colt_backorder_signups', array_slice($signups, 0, 1000), false);

        wp_safe_redirect(add_query_arg('colt_backorder_signup', '1', get_permalink($product_id)));
        exit;
    }

    private function is_product_backorder_signup_enabled($product)
    {
        if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
            return false;
        }

        $product_id = (int) $product->get_id();
        return get_post_meta($product_id, '_colt_backorder_signup_enabled', true) === '1'
            || (method_exists($product, 'get_stock_status') && $product->get_stock_status() === 'onbackorder');
    }

    private function product_crm_backorder_signups($limit = 10)
    {
        $signups = get_option('colt_backorder_signups', []);
        if (!is_array($signups)) {
            return [];
        }

        return array_slice($signups, 0, max(1, (int) $limit));
    }

    private function render_product_crm_backorder_signup_list(array $signups)
    {
        if (!$signups) {
            echo '<p>עדיין אין הרשמות לעדכוני Backorder.</p>';
            return;
        }
        ?>
        <div class="colt-crm__table-wrap">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>מוצר</th>
                        <th>שם</th>
                        <th>אימייל</th>
                        <th>טלפון</th>
                        <th>תאריך</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($signups as $signup) : ?>
                        <tr>
                            <td>
                                <?php if (!empty($signup['product_id'])) : ?>
                                    <a href="<?php echo esc_url(get_edit_post_link((int) $signup['product_id'])); ?>"><?php echo esc_html($signup['product_name'] ?? 'מוצר'); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($signup['product_name'] ?? 'מוצר'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($signup['name'] ?? ''); ?></td>
                            <td><a href="mailto:<?php echo esc_attr($signup['email'] ?? ''); ?>"><?php echo esc_html($signup['email'] ?? ''); ?></a></td>
                            <td><?php echo esc_html($signup['phone'] ?? ''); ?></td>
                            <td><?php echo esc_html(!empty($signup['created']) ? wp_date('d.m.Y H:i', (int) $signup['created']) : ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function product_crm_redirect(array $args)
    {
        wp_safe_redirect(add_query_arg(array_merge(['page' => 'colt-product-crm'], $args), admin_url('admin.php')));
        exit;
    }

    private function product_crm_error_message($code)
    {
        $messages = [
            'woocommerce' => 'WooCommerce לא פעיל כרגע.',
            'no_products' => 'לא נבחרו מוצרים לעדכון.',
            'coupon' => 'לא ניתן ליצור קופון. ודא שיש קוד תקין וסכום הנחה גדול מאפס.',
            'promo' => 'לא ניתן לשמור את כלל המבצע.',
            'slider_create' => 'לא ניתן ליצור סליידר מהמוצרים שסומנו.',
            'confirm_delete' => 'צריך לאשר מחיקה לפני הפעלת פעולת מחיקה.',
            'product_title' => 'חובה להזין שם מוצר.',
            'product_create' => 'לא ניתן ליצור את המוצר. בדוק SKU כפול או שדות לא תקינים.',
            'product_update' => 'לא ניתן לעדכן את המוצר. בדוק SKU כפול או שדות לא תקינים.',
            'xlsx_upload' => 'לא התקבל קובץ XLSX לייבוא.',
            'xlsx_type' => 'אפשר להעלות רק קובץ XLSX.',
            'xlsx_zip' => 'השרת לא תומך בקריאת XLSX כי ZipArchive לא זמין.',
            'xlsx_open' => 'לא ניתן לפתוח את קובץ ה-XLSX.',
            'xlsx_sheet' => 'לא ניתן לקרוא את הגיליון הראשון בקובץ.',
            'xlsx_empty' => 'לא נמצאו שורות מוצרים בקובץ.',
            'xlsx_expired' => 'תצוגת הייבוא הזמנית פגה. העלה את הקובץ שוב.',
        ];

        return $messages[$code] ?? 'הפעולה לא הושלמה. נסה שוב.';
    }

    private static function service_admin_shortcodes()
    {
        return [
            'home' => '[colt_home_experience]',
            'vault' => '[colt_vault_experience]',
            'mystery' => '[colt_mystery_box_experience]',
            'mystery-product' => '[colt_mystery_box_product]',
            'singles' => '[colt_singles_experience]',
            'jewelry' => '[colt_jewelry_experience]',
            'most-wanted' => '[colt_most_wanted_experience]',
            'personal-search' => '[colt_personal_search_experience]',
            'grading' => '[colt_grading_experience]',
            'whatnot' => '[colt_whatnot_experience]',
            'portfolio' => '[colt_portfolio_experience]',
        ];
    }

    public static function default_home_page()
    {
        return [
            'nav_label' => 'עמוד הבית',
            'nav_links' => [
                ['label' => 'חוויות מרכזיות', 'url' => '#colt-core'],
                ['label' => 'שירותים', 'url' => '#colt-services'],
                ['label' => 'יצירת קשר', 'url' => '#colt-contact'],
                ['label' => 'חנות', 'url' => home_url('/shop/')],
            ],
            'origin_rail_title' => '[ COLT ORIGIN ]',
            'origin_rail' => [
                ['label' => 'כניסה לעולם', 'url' => '#colt-core', 'step' => '0'],
                ['label' => 'ערך ונדירות', 'url' => '#colt-core', 'step' => '1'],
                ['label' => 'התחלה', 'url' => home_url('/shop/'), 'step' => '2'],
            ],
            'origin_tab_label' => 'לגלות',
            'origin_scroll_label' => 'גלול כדי להתקרב',
            'origin_chapters' => [
                [
                    'kicker' => 'COLT COLLECTORS CLUB',
                    'title' => 'נכנסים לעולם שבו אוסף הופך לנכס.',
                    'text' => 'קלפים נדירים, סלאבים ופריטי אספנות מקבלים כאן במה שנבנית סביב ערך, נדירות וסיפור אישי.',
                    'primary_label' => '',
                    'primary_url' => '',
                    'secondary_label' => '',
                    'secondary_url' => '',
                ],
                [
                    'kicker' => 'CURATED VALUE',
                    'title' => 'לא רק קונים פריט. בונים אסטרטגיית אספנות.',
                    'text' => '',
                    'primary_label' => '',
                    'primary_url' => '',
                    'secondary_label' => '',
                    'secondary_url' => '',
                ],
                [
                    'kicker' => 'ENTER COLT',
                    'title' => 'מצא, שמור, דרג ובנה את הצעד הבא שלך.',
                    'text' => '',
                    'primary_label' => 'להתחיל את המסע',
                    'primary_url' => '#colt-core',
                    'secondary_label' => 'כניסה לחנות',
                    'secondary_url' => home_url('/shop/'),
                ],
            ],
            'origin_metrics' => [
                ['value' => '01', 'label' => 'סינגלים וסלאבים'],
                ['value' => '02', 'label' => 'THE VAULT'],
                ['value' => '03', 'label' => 'תכשיטי אספנים'],
                ['value' => '04', 'label' => 'Mystery Box'],
            ],
            'core_kicker' => 'MAIN EXPERIENCES',
            'core_title' => 'עולם השירותים המרכזיים של COLT.',
            'core_text' => 'ארבע כניסות שונות לאותו מסע אספנות: קנייה מדויקת, שמירת ערך, אובייקט אישי וחוויית פתיחה.',
            'core_aria_label' => 'שירותי COLT מרכזיים',
            'core_cta_label' => 'לעמוד השירות',
            'orbit_kicker' => 'COLLECTOR UNIVERSE',
            'orbit_title' => 'שירותים שמקיפים את האוסף שלך מכל כיוון.',
            'orbit_text' => 'איתור, דירוג, מכירה חיה, פריטים מבוקשים ותיק אספנות פיננסי, כולם נפתחים מתוך מערכת אחת.',
            'orbit_aria_label' => 'שירותי אספנים נוספים',
            'orbit_dock' => ['SEARCH', 'GRADE', 'SELL', 'BUILD'],
            'products_kicker' => 'LATEST DROPS',
            'products_title' => 'מהחנות אל המסלול.',
            'products_text' => 'מבחר קלפים, סלאבים, אביזרים וקטגוריות אספנות שנעות יחד כמו ויטרינה חיה.',
            'products_aria_label' => 'קטגוריות אספנות',
            'product_drop_prefix' => 'DROP',
            'product_categories' => [
                ['label' => 'Pokemon', 'detail' => 'סינגלים, סלאבים וסטים', 'url' => home_url('/shop/'), 'tone' => 'pokemon'],
                ['label' => 'One Piece', 'detail' => 'קלפים מבוקשים ופתיחות', 'url' => home_url('/shop/'), 'tone' => 'onepiece'],
                ['label' => 'Sports', 'detail' => 'NBA, NFL, MLB וכדורגל', 'url' => home_url('/shop/'), 'tone' => 'sports'],
                ['label' => 'Marvel / Disney', 'detail' => 'פריטי תרבות ואייקונים', 'url' => home_url('/shop/'), 'tone' => 'marvel'],
                ['label' => 'Accessories', 'detail' => 'שמירה, תצוגה ואחסון', 'url' => home_url('/shop/'), 'tone' => 'accessories'],
                ['label' => 'Graded Cards', 'detail' => 'PSA, BGS וסלאבים', 'url' => home_url('/shop/'), 'tone' => 'graded'],
            ],
            'fallback_products' => [
                ['title' => 'Pokemon singles & slabs', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/hunter-service.jpg'), 'price' => ''],
                ['title' => 'Graded card vault picks', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/vault-service.jpg'), 'price' => ''],
                ['title' => 'Mystery box collector drop', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/hunter-service.jpg'), 'price' => ''],
                ['title' => 'Collector accessories', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/vault-service.jpg'), 'price' => ''],
                ['title' => 'Sports cards showcase', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/hunter-service.jpg'), 'price' => ''],
                ['title' => 'Marvel / Disney collectibles', 'url' => home_url('/shop/'), 'image' => self::asset_url('assets/img/vault-service.jpg'), 'price' => ''],
            ],
            'product_ticker' => ['Pokemon', 'One Piece', 'Sports', 'Marvel', 'Disney', 'Slabs'],
            'finale_kicker' => 'COLT COLLECTORS CLUB',
            'finale_title' => 'המקום שבו אספנות מקבלת בית.',
            'finale_text' => 'אנחנו מחברים בין קלפים, פריטים נדירים, שירותי שמירה, איתור, דירוג ומכירה, כדי שכל אספן יוכל להתקדם בביטחון ובסטייל.',
            'finale_contact_kicker' => 'START A CONVERSATION',
            'finale_contact_title' => 'יש פריט למכור, קלף לחפש או אוסף לבנות?',
            'finale_contact_text' => 'שלחו לנו הודעה ונבין יחד מה הצעד הנכון: קנייה, מכירה, דירוג, כספת או בניית תיק אספנות.',
            'finale_contact_primary' => 'יצירת קשר',
            'finale_contact_secondary' => 'לחנות',
            'finale_social_aria_label' => 'עקבו אחרינו',
            'finale_social_kicker' => 'FOLLOW THE DROP',
            'social_links' => [
                ['label' => 'Instagram', 'detail' => 'דרופים, סטוריז ופריטים חדשים', 'url' => '#colt-contact', 'tone' => 'instagram'],
                ['label' => 'TikTok', 'detail' => 'פתיחות, רגעים והיילייטים', 'url' => '#colt-contact', 'tone' => 'tiktok'],
                ['label' => 'Whatnot', 'detail' => 'לייבים ומכירות בזמן אמת', 'url' => '#colt-contact', 'tone' => 'whatnot'],
                ['label' => 'WhatsApp', 'detail' => 'שאלות, איתור ושירות אישי', 'url' => '#colt-contact', 'tone' => 'whatsapp'],
            ],
            'service_cards' => [
                [
                    'title' => 'סינגלים וסלאבים',
                    'eyebrow' => 'קלפים נבחרים',
                    'text' => 'קלפים בודדים, מדורגים ופריטי showcase לאספנים שמחפשים את הדבר המדויק.',
                    'slug' => '../shop',
                    'tone' => 'singles',
                    'image' => self::asset_url('assets/img/hunter-service.jpg'),
                    'group' => 'core',
                ],
                [
                    'title' => 'THE VAULT',
                    'eyebrow' => 'כספת אספנים',
                    'text' => 'אחסון מבוטח, הרמטי ומותאם לפריטי אספנות יקרי ערך.',
                    'slug' => 'the-vault',
                    'tone' => 'vault',
                    'image' => self::asset_url('assets/img/vault-service.jpg'),
                    'group' => 'core',
                ],
                [
                    'title' => 'תכשיטי אספנים',
                    'eyebrow' => 'פריט שהופך לאובייקט',
                    'text' => 'עיצוב אישי סביב קלף, מטבע או פריט אספנות עם נוכחות יוקרתית.',
                    'slug' => '%d7%a2%d7%99%d7%a6%d7%95%d7%91-%d7%aa%d7%9b%d7%a9%d7%99%d7%98-%d7%90%d7%99%d7%a9%d7%99',
                    'tone' => 'jewelry',
                    'image' => self::asset_url('assets/img/vault-service.jpg'),
                    'group' => 'core',
                ],
                [
                    'title' => 'Mystery Box',
                    'eyebrow' => 'פתיחה עם ערך',
                    'text' => 'מארז אספנים עם קלף מדורג, סינגל וחבילת קלפים לחוויית פתיחה פרימיום.',
                    'slug' => '../shop',
                    'tone' => 'mystery',
                    'image' => self::asset_url('assets/img/hunter-service.jpg'),
                    'group' => 'core',
                ],
                [
                    'title' => 'MOST WANTED',
                    'eyebrow' => 'אנחנו מחפשים',
                    'text' => 'רשימת פריטים מבוקשים שאפשר למכור לנו ישירות.',
                    'slug' => 'most-wanted',
                    'tone' => 'wanted',
                    'image' => self::asset_url('assets/img/hunter-service.jpg'),
                    'group' => 'support',
                ],
                [
                    'title' => 'חיפוש אישי',
                    'eyebrow' => 'איתור לפי דרישה',
                    'text' => 'מפעילים קשרים, ערוצים וקהילה כדי למצוא את הפריט הנכון.',
                    'slug' => '%d7%97%d7%99%d7%a4%d7%95%d7%a9-%d7%90%d7%99%d7%a9%d7%99',
                    'tone' => 'search',
                    'image' => self::asset_url('assets/img/hunter-service.jpg'),
                    'group' => 'support',
                ],
                [
                    'title' => 'דירוג',
                    'eyebrow' => 'הכנה ושליחה',
                    'text' => 'תהליך מסודר לקלפים שצריכים להגיע לדירוג נכון.',
                    'slug' => 'cshev',
                    'tone' => 'grading',
                    'image' => self::asset_url('assets/img/vault-service.jpg'),
                    'group' => 'support',
                ],
                [
                    'title' => 'Whatnot',
                    'eyebrow' => 'מכירה חיה',
                    'text' => 'ניהול מלאי, חשיפה ולייבים לקהל אספנים מתאים.',
                    'slug' => '%d7%9e%d7%9b%d7%99%d7%a8%d7%94-%d7%91whatnot',
                    'tone' => 'whatnot',
                    'image' => self::asset_url('assets/img/hunter-service.jpg'),
                    'group' => 'support',
                ],
                [
                    'title' => 'תיק השקעות',
                    'eyebrow' => 'אספנות עם אסטרטגיה',
                    'text' => 'בניית תיק אספנות לפי תקציב, פוטנציאל וביקוש.',
                    'slug' => '%d7%91%d7%a0%d7%99%d7%99%d7%aa-%d7%aa%d7%99%d7%a7-%d7%94%d7%a9%d7%a7%d7%a2%d7%95%d7%aa',
                    'tone' => 'portfolio',
                    'image' => self::asset_url('assets/img/vault-service.jpg'),
                    'group' => 'support',
                ],
            ],
        ];
    }

    public static function default_special_pages()
    {
        return [
            'vault' => [
                'nav_label' => 'THE VAULT',
                'nav_entry_label' => 'הכספת',
                'nav_inside_label' => 'מה בפנים',
                'nav_protocol_label' => 'התהליך',
                'nav_contact_label' => 'יצירת קשר',
                'hero_scroll_label' => 'גלול לפתיחה',
                'hero_kicker' => 'COLT THE VAULT',
                'hero_title' => 'כספת אספנים לפריטים שאתה לא רוצה להשאיר ליד המקרה.',
                'hero_text' => 'שירות שמירה לפריטי אספנות יקרי ערך: קלפים מדורגים, סינגלים נדירים, פריטי חתימה ואובייקטים שהערך שלהם תלוי גם באופן שבו שומרים עליהם.',
                'hero_primary' => 'בדיקת התאמה',
                'hero_secondary' => 'לראות מה בפנים',
                'inside_kicker' => 'INSIDE THE VAULT',
                'inside_title' => 'לא מחסן. חדר שמירה שנבנה סביב ערך.',
                'inside_text' => 'המטרה היא לתת לפריטים יקרים תחושה של בית קבוע: מסודר, מתועד, נשלט, ונגיש לפעולה כשצריך למכור, לדרג או להעביר הלאה.',
                'protocol_kicker' => 'THE PROTOCOL',
                'protocol_title' => 'כל פריט נכנס למסלול ברור.',
                'protocol_text' => 'מהרגע שהפריט מגיע אלינו ועד הרגע שאתה מבקש לבצע פעולה, הכל בנוי כדי להוריד חיכוך ולשמור על השליטה אצלך.',
                'contact_kicker' => 'OPEN A VAULT CASE',
                'contact_title' => 'רוצה לבדוק אם האוסף שלך מתאים לכספת?',
                'contact_text' => 'שלח לנו מה יש לך, מה המטרה ומה רמת הרגישות של הפריטים. נחזור עם הצעה מסודרת לשמירה, תיעוד או פעולה עתידית.',
                'contact_primary' => 'פתיחת פנייה',
                'contact_secondary' => 'שיחה בוואטסאפ',
                'contact_third' => 'לראות פריטים בחנות',
                'ledger' => [
                    ['value' => '01', 'label' => 'תיעוד כניסה'],
                    ['value' => '02', 'label' => 'אחסון מבוקר'],
                    ['value' => '03', 'label' => 'גישה לפי בקשה'],
                    ['value' => '04', 'label' => 'שכבת ערך'],
                ],
                'features' => [
                    ['title' => 'תנאי אחסון יציבים', 'text' => 'סביבה מבוקרת לפריטים רגישים, עם דגש על יציבות, ניקיון והפרדה נכונה בין פריטים.', 'meta' => 'Climate'],
                    ['title' => 'תיעוד מצב הכניסה', 'text' => 'כל פריט נכנס עם תיעוד בסיסי: תמונות, מצב, הערות ושייכות, כדי לשמור על שקיפות.', 'meta' => 'Record'],
                    ['title' => 'גישה לפי בקשה', 'text' => 'אפשר לבקש הוצאה, צילום, מכירה או העברה לדירוג בלי להפוך את האוסף לתיק לוגיסטי.', 'meta' => 'Access'],
                    ['title' => 'שכבת ערך לאוסף', 'text' => 'שירות שמיועד לפריטים שחשוב לשמור עליהם כמו נכס, לא רק כמו מוצר על מדף.', 'meta' => 'Value'],
                ],
                'steps' => [
                    ['step' => '01', 'title' => 'קליטה ותיעוד', 'text' => 'בודקים יחד מה נכנס לכספת, מצלמים, מסמנים ומגדירים מטרת שמירה או מכירה עתידית.'],
                    ['step' => '02', 'title' => 'אריזה והפרדה', 'text' => 'כל פריט מקבל שכבת הגנה מתאימה, הפרדה פיזית וסידור שמונע שחיקה מיותרת.'],
                    ['step' => '03', 'title' => 'שמירה מבוקרת', 'text' => 'הפריטים נשמרים בסביבת תצוגה ואחסון נקייה, יציבה ומותאמת לפריטי אספנות יקרי ערך.'],
                    ['step' => '04', 'title' => 'פעולה לפי צורך', 'text' => 'כשתרצה, אפשר להוציא, לשלוח לדירוג, להציע למכירה או להעביר לקונה בצורה מסודרת.'],
                ],
            ],
            'mystery' => [
                'nav_label' => 'Mystery Box',
                'nav_open_label' => 'הפתיחה',
                'nav_contents_label' => 'מה בפנים',
                'nav_options_label' => 'בחירה',
                'nav_buy_label' => 'רכישה',
                'price' => '250 ש"ח',
                'hero_kicker' => 'COLT MYSTERY BOX',
                'hero_title' => 'פותחים קופסה אחת. מקבלים שלושה רגעי אספנות.',
                'hero_text' => 'בכל מיסטרי בוקס מחכה קלף מדורג, חפיסת בוסטר וקלף סינגל. בוחרים עולם, בוחרים שפה, ונותנים לפתיחה לעשות את שלה.',
                'hero_meta' => 'Pokemon או One Piece · English או Japanese',
                'reveal_kicker' => 'WHAT IS INSIDE',
                'reveal_title' => 'הפתיחה בנויה כמו דרופ קטן.',
                'reveal_text' => 'כל רכיב בקופסה מקבל תפקיד: סלאב שנותן ערך מרכזי, בוסטר שנותן מתח של פתיחה, וסינגל שמוסיף עוד שכבת אספנות.',
                'options_kicker' => 'PICK YOUR DROP',
                'options_title' => 'ארבע אפשרויות, מחיר אחד.',
                'options_text' => 'הקופסה זמינה רק בשילובים האלה: Pokemon או One Piece, ובתוך כל עולם English או Japanese. המחיר קבוע: 250 ש"ח.',
                'world_group_label' => 'עולם',
                'language_group_label' => 'שפה',
                'summary_label' => 'הבחירה שלך',
                'summary_world_default' => 'Pokemon',
                'summary_language_default' => 'English',
                'buy_kicker' => 'READY TO OPEN',
                'buy_title' => 'בחרת שילוב? עכשיו נשאר לפתוח.',
                'buy_text' => 'הקופסה מיועדת לאספנים שרוצים חוויית פתיחה מוגדרת וברורה: עולם אחד, שפה אחת, שלושה פריטים, מחיר אחד.',
                'buy_primary' => 'רכישת Mystery Box',
                'buy_secondary' => 'שאלה לפני רכישה',
                'buy_third' => 'יצירת קשר',
                'rail' => [
                    ['label' => 'sealed'],
                    ['label' => 'crack'],
                    ['label' => 'reveal'],
                    ['label' => 'choose'],
                ],
                'stream_labels' => [
                    ['label' => 'Graded'],
                    ['label' => 'Booster'],
                    ['label' => 'Single'],
                ],
                'showcase_labels' => [
                    ['label' => 'GRADED'],
                    ['label' => 'BOOSTER'],
                    ['label' => 'SINGLE'],
                ],
                'contents' => [
                    ['title' => 'קלף מדורג', 'text' => 'סלאב אחד שמייצר את רגע ה־showcase של הקופסה.', 'meta' => 'Graded'],
                    ['title' => 'חפיסת בוסטר', 'text' => 'חבילת פתיחה אחת מתוך העולם שבחרת.', 'meta' => 'Booster'],
                    ['title' => 'קלף סינגל', 'text' => 'קלף נוסף להשלמת החוויה ולבניית האוסף.', 'meta' => 'Single'],
                ],
                'worlds' => [
                    ['value' => 'pokemon', 'label' => 'Pokemon', 'text' => 'מפלצות, סטים, להיטים ונוסטלגיה'],
                    ['value' => 'one-piece', 'label' => 'One Piece', 'text' => 'פיראטים, דמויות ופתיחות באנרגיה גבוהה'],
                ],
                'languages' => [
                    ['value' => 'english', 'label' => 'English', 'text' => 'מוצרים באנגלית בלבד'],
                    ['value' => 'japanese', 'label' => 'Japanese', 'text' => 'מוצרים ביפנית בלבד'],
                ],
            ],
            'mystery-product' => [
                'nav_label' => 'Mystery Box - עמוד מוצר',
                'nav_open_label' => 'המוצר',
                'nav_contents_label' => 'מה מקבלים',
                'nav_options_label' => 'מקוריות ועולמות',
                'nav_buy_label' => 'הזמנה',
                'price' => '299 ש"ח',
                'hero_kicker' => 'COLT MYSTERY BOX',
                'hero_title' => 'מיסטרי בוקס אספנים עם שלושה פריטים בכל קופסה.',
                'hero_text' => 'קופסת Mystery רגילה, נקייה וברורה: קלף מדורג, חבילה סגורה וקלף סינגל אחד עם סיכוי לקלף חתום, ממוספר או נדיר במיוחד.',
                'hero_meta' => 'Pokemon · One Piece · Marvel · Disney · Sports',
                'hero_primary' => 'הזמנה ב-299 ש"ח',
                'hero_secondary' => 'שאלה לפני רכישה',
                'hero_box_image' => '',
                'hero_box_alt' => 'COLT Mystery Box פתוחה',
                'inside_kicker' => 'INSIDE EVERY BOX',
                'inside_title' => 'שלושה פריטים, רגע פתיחה אחד.',
                'inside_text' => 'בכל קופסה יש שילוב שנבנה לאספנים: פריט מדורג שנותן עוגן, חבילה סגורה לפתיחה, וסינגל שיכול להיות גם קלף חתימה, קלף ממוספר או פריט chase.',
                'reveal_kicker' => 'AUTHENTICITY PROMISE',
                'reveal_title' => 'מתחייבים למקוריות ולשקיפות מלאה.',
                'reveal_text' => 'כל פריט שנכנס לקופסה נבדק לפני האריזה. אנחנו לא מכניסים זיופים, רפליקות או פריטים שמקוריותם לא ברורה, ומציגים מראש מה סוגי הפריטים שאפשר לקבל.',
                'options_kicker' => 'COLLECTOR WORLDS',
                'options_title' => 'עולמות אספנות רחבים, לא קופסה של תחום אחד בלבד.',
                'options_text' => 'הקופסאות יכולות לכלול פריטים מפוקימון, וואן פיס, מארוול, דיסני וספורט, עם דגש על פריטים שמתאימים לאספנים שרוצים גם ערך וגם חוויית פתיחה.',
                'buy_kicker' => 'READY TO OPEN',
                'buy_title' => 'המחיר: 299 ש"ח לקופסה.',
                'buy_text' => 'רוצה קופסה אחת או כמה קופסאות לדרופ? אפשר להזמין ישירות, לשאול על מלאי זמין או להתייעץ אם זה מתאים לאוסף שלך.',
                'buy_primary' => 'רכישה עכשיו',
                'buy_secondary' => 'שאלה בוואטסאפ',
                'buy_third' => 'יצירת קשר',
                'ledger' => [
                    ['value' => '299', 'label' => 'ש"ח לקופסה'],
                    ['value' => '3', 'label' => 'פריטים בפנים'],
                    ['value' => '100%', 'label' => 'התחייבות מקוריות'],
                    ['value' => '5', 'label' => 'עולמות אספנות'],
                ],
                'hero_cards' => [
                    ['modifier' => 'slab', 'label' => 'GRADED', 'image' => '', 'alt' => 'קלף מדורג'],
                    ['modifier' => 'pack', 'label' => 'SEALED', 'image' => '', 'alt' => 'חבילה סגורה'],
                    ['modifier' => 'single', 'label' => 'SINGLE', 'image' => '', 'alt' => 'קלף סינגל'],
                ],
                'contents' => [
                    ['title' => 'קלף מדורג', 'text' => 'סלאב אחד שמייצר את פריט ה-showcase של הקופסה.', 'meta' => 'Graded card', 'image' => '', 'alt' => 'קלף מדורג במיסטרי בוקס'],
                    ['title' => 'חבילה סגורה', 'text' => 'בוסטר או חבילה אטומה לפתיחה מתוך אחד מעולמות האספנות.', 'meta' => 'Sealed pack', 'image' => '', 'alt' => 'חבילה סגורה במיסטרי בוקס'],
                    ['title' => 'קלף סינגל', 'text' => 'קלף נוסף שיכול להיות רגיל, חתום, ממוספר או chase לפי המלאי והדרופ.', 'meta' => 'Single card', 'image' => '', 'alt' => 'קלף סינגל במיסטרי בוקס'],
                ],
                'features' => [
                    ['title' => 'אפשרות לקלף חתום', 'text' => 'בחלק מהקופסאות עשויים להיכנס קלפי חתימה או פריטים עם ערך מיוחד.', 'meta' => 'Auto'],
                    ['title' => 'אפשרות לקלף ממוספר', 'text' => 'יש סיכוי לפריטים ממוספרים, כולל 1 מתוך כמה, בהתאם למלאי הדרופ.', 'meta' => 'Numbered'],
                    ['title' => 'מתאים לכמה תחומים', 'text' => 'פוקימון, וואן פיס, מארוול, דיסני וספורט יכולים להופיע בדרופים שונים.', 'meta' => 'Multi-world'],
                ],
                'worlds' => [
                    ['value' => 'pokemon', 'label' => 'Pokemon', 'text' => 'קלפים, חבילות, סלאבים ודמויות מבוקשות.'],
                    ['value' => 'one-piece', 'label' => 'One Piece', 'text' => 'פריטים מעולם וואן פיס, פתיחות וקלפי chase.'],
                    ['value' => 'marvel', 'label' => 'Marvel', 'text' => 'דמויות אייקוניות, קלפי אספנות ופריטי תרבות.'],
                    ['value' => 'disney', 'label' => 'Disney', 'text' => 'קלאסיקות, דמויות, סטים ופריטים עם ערך רגשי.'],
                    ['value' => 'sports', 'label' => 'Sports', 'text' => 'כדורסל, פוטבול, בייסבול וכדורגל לפי מלאי זמין.'],
                ],
                'steps' => [
                    ['step' => '01', 'title' => 'בוחרים קופסה', 'text' => 'המחיר קבוע וברור: 299 ש"ח לקופסה.'],
                    ['step' => '02', 'title' => 'אנחנו אורזים', 'text' => 'כל קופסה מקבלת שילוב של מדורג, סגור וסינגל.'],
                    ['step' => '03', 'title' => 'פותחים', 'text' => 'מקבלים חוויית פתיחה עם סיכוי לפריט חתום, ממוספר או נדיר.'],
                ],
            ],
        ];
    }

    private function render_home_admin_fields($active, array $page)
    {
        ?>
        <div class="colt-admin__grid">
            <?php $this->admin_text_field($active, 'nav_label', 'שם בתפריט הניהול', $page['nav_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'origin_rail_title', 'פתיח - כותרת סרגל צד', $page['origin_rail_title'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'origin_tab_label', 'פתיח - כפתור צד', $page['origin_tab_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'origin_scroll_label', 'פתיח - טקסט גלילה', $page['origin_scroll_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'core_kicker', 'שירותים מרכזיים - טקסט קטן', $page['core_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'core_title', 'שירותים מרכזיים - כותרת', $page['core_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'core_text', 'שירותים מרכזיים - טקסט', $page['core_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'core_aria_label', 'שירותים מרכזיים - תווית נגישות', $page['core_aria_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'core_cta_label', 'שירותים מרכזיים - כפתור בכרטיס', $page['core_cta_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'orbit_kicker', 'כוכבי שירותים - טקסט קטן', $page['orbit_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'orbit_title', 'כוכבי שירותים - כותרת', $page['orbit_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'orbit_text', 'כוכבי שירותים - טקסט', $page['orbit_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'orbit_aria_label', 'כוכבי שירותים - תווית נגישות', $page['orbit_aria_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'products_kicker', 'מוצרים - טקסט קטן', $page['products_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'products_title', 'מוצרים - כותרת', $page['products_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'products_text', 'מוצרים - טקסט', $page['products_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'products_aria_label', 'מוצרים - תווית נגישות', $page['products_aria_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'product_drop_prefix', 'מוצרים - קידומת כרטיס', $page['product_drop_prefix'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_kicker', 'סיום - טקסט קטן', $page['finale_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_title', 'סיום - כותרת', $page['finale_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'finale_text', 'סיום - טקסט', $page['finale_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'finale_contact_kicker', 'יצירת קשר - טקסט קטן', $page['finale_contact_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_contact_title', 'יצירת קשר - כותרת', $page['finale_contact_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'finale_contact_text', 'יצירת קשר - טקסט', $page['finale_contact_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'finale_contact_primary', 'יצירת קשר - כפתור ראשי', $page['finale_contact_primary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_contact_secondary', 'יצירת קשר - כפתור חנות', $page['finale_contact_secondary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_social_aria_label', 'רשתות - תווית נגישות', $page['finale_social_aria_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'finale_social_kicker', 'רשתות - טקסט קטן', $page['finale_social_kicker'] ?? ''); ?>
        </div>

        <?php $this->admin_repeatable_fields($active, 'nav_links', 'ניווט עליון', $page['nav_links'] ?? [], ['label' => 'תווית', 'url' => 'קישור']); ?>
        <?php $this->admin_repeatable_fields($active, 'origin_rail', 'פתיח - סרגל צד', $page['origin_rail'] ?? [], ['label' => 'תווית', 'url' => 'קישור', 'step' => 'מספר שלב']); ?>
        <?php $this->admin_repeatable_fields($active, 'origin_chapters', 'פתיח - פרקי סיפור', $page['origin_chapters'] ?? [], ['kicker' => 'טקסט קטן', 'title' => 'כותרת', 'text' => 'טקסט', 'primary_label' => 'כפתור ראשי', 'primary_url' => 'קישור ראשי', 'secondary_label' => 'כפתור משני', 'secondary_url' => 'קישור משני']); ?>
        <?php $this->admin_repeatable_fields($active, 'origin_metrics', 'פתיח - מדדי ערך', $page['origin_metrics'] ?? [], ['value' => 'מספר/קוד', 'label' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'service_cards', 'כרטיסי שירות בעמוד הבית', $page['service_cards'] ?? [], ['group' => 'קבוצה: core/support', 'tone' => 'טון עיצוב', 'slug' => 'Slug או נתיב', 'title' => 'כותרת', 'eyebrow' => 'טקסט קטן', 'text' => 'טקסט', 'image' => 'תמונה']); ?>
        <?php $this->admin_repeatable_fields($active, 'orbit_dock', 'כוכבי שירותים - תוויות תחתונות', $page['orbit_dock'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'product_categories', 'קטגוריות מוצרים', $page['product_categories'] ?? [], ['label' => 'שם', 'detail' => 'פירוט', 'url' => 'קישור', 'tone' => 'טון עיצוב']); ?>
        <?php $this->admin_repeatable_fields($active, 'fallback_products', 'מוצרים לדוגמה כשאין מוצרי WooCommerce', $page['fallback_products'] ?? [], ['title' => 'שם מוצר', 'url' => 'קישור', 'image' => 'תמונה', 'price' => 'מחיר']); ?>
        <?php $this->admin_repeatable_fields($active, 'product_ticker', 'מוצרים - טיקר תחתון', $page['product_ticker'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'social_links', 'רשתות חברתיות', $page['social_links'] ?? [], ['label' => 'שם', 'detail' => 'פירוט', 'url' => 'קישור', 'tone' => 'טון עיצוב']); ?>
        <?php
    }

    private function render_service_admin_fields($active, array $page)
    {
        ?>
        <div class="colt-admin__grid">
            <?php $this->admin_text_field($active, 'nav_label', 'שם בתפריט/ניהול', $page['nav_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'eyebrow', 'טקסט קטן מעל הכותרת', $page['eyebrow'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'title', 'כותרת ראשית', $page['title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'lead', 'פסקת פתיחה', $page['lead'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'intro_title', 'כותרת מקטע הסבר', $page['intro_title'] ?? ''); ?>
            <?php $this->admin_textarea_field($active, 'intro_text', 'טקסט מקטע הסבר', $page['intro_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'primary_label', 'כפתור ראשי', $page['primary_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'secondary_label', 'כפתור משני', $page['secondary_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'scene', 'כתובת תמונת רקע', $page['scene'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'layout', 'סוג פריסה', $page['layout'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'motion', 'סוג תנועה', $page['motion'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_start_label', 'ניווט - התחלה', $page['nav_start_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_brief_label', 'ניווט - מה מקבלים', $page['nav_brief_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_process_label', 'ניווט - תהליך', $page['nav_process_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_contact_label', 'ניווט - פנייה', $page['nav_contact_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'brief_kicker', 'מקטע כרטיסים - טקסט קטן', $page['brief_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'process_kicker', 'תהליך - טקסט קטן', $page['process_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'process_title', 'תהליך - כותרת', $page['process_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'process_text', 'תהליך - טקסט פתיחה', $page['process_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'details_kicker', 'פרטים - טקסט קטן', $page['details_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'details_title', 'פרטים - כותרת', $page['details_title'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'fit_kicker', 'התאמה - טקסט קטן', $page['fit_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'fit_title', 'התאמה - כותרת', $page['fit_title'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_kicker', 'יצירת קשר - טקסט קטן', $page['contact_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_title', 'יצירת קשר - כותרת', $page['contact_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'contact_text', 'יצירת קשר - טקסט', $page['contact_text'] ?? '', true); ?>
        </div>

        <?php $this->admin_repeatable_fields($active, 'artifact_labels', 'תוויות אלמנטים צפים', $page['artifact_labels'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'metrics', 'מדדים בהירו', $page['metrics'] ?? [], ['value' => 'מספר/קוד', 'label' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'features', 'כרטיסי ערך', $page['features'] ?? [], ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'process', 'שלבי תהליך', $page['process'] ?? [], ['step' => 'מספר שלב', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'details', 'רשימת פרטים קטנים', $page['details'] ?? [], ['label' => 'פרט']); ?>
        <?php $this->admin_repeatable_fields($active, 'fit', 'למי זה מתאים', $page['fit'] ?? [], ['label' => 'התאמה']); ?>

        <div class="colt-admin__section">
            <?php $this->admin_textarea_field($active, 'note', 'הערה/טון העמוד', $page['note'] ?? '', true); ?>
        </div>
        <?php
    }

    private function render_special_admin_fields($active, array $page)
    {
        ?>
        <div class="colt-admin__grid">
            <?php $this->admin_text_field($active, 'nav_label', 'שם העמוד', $page['nav_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_entry_label', 'ניווט כספת - הכספת', $page['nav_entry_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_inside_label', 'ניווט כספת - מה בפנים', $page['nav_inside_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_protocol_label', 'ניווט כספת - התהליך', $page['nav_protocol_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_contact_label', 'ניווט כספת - יצירת קשר', $page['nav_contact_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_open_label', 'ניווט Mystery - הפתיחה', $page['nav_open_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_contents_label', 'ניווט Mystery - מה בפנים', $page['nav_contents_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_options_label', 'ניווט Mystery - בחירה', $page['nav_options_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'nav_buy_label', 'ניווט Mystery - רכישה', $page['nav_buy_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'hero_scroll_label', 'פתיח - טקסט גלילה', $page['hero_scroll_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'price', 'מחיר / תווית מחיר', $page['price'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'hero_kicker', 'פתיח - טקסט קטן', $page['hero_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'hero_title', 'פתיח - כותרת', $page['hero_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'hero_text', 'פתיח - טקסט', $page['hero_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'hero_meta', 'פתיח - מידע קטן', $page['hero_meta'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'hero_primary', 'פתיח - כפתור ראשי', $page['hero_primary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'hero_secondary', 'פתיח - כפתור משני', $page['hero_secondary'] ?? ''); ?>
            <?php if ($active === 'mystery-product') : ?>
                <?php $this->admin_image_field($active, 'hero_box_image', 'פתיח - תמונת קופסה מרכזית', $page['hero_box_image'] ?? '', true); ?>
                <?php $this->admin_text_field($active, 'hero_box_alt', 'פתיח - תיאור תמונת קופסה', $page['hero_box_alt'] ?? ''); ?>
            <?php endif; ?>
            <?php $this->admin_text_field($active, 'inside_kicker', 'מקטע פנימי - טקסט קטן', $page['inside_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'inside_title', 'מקטע פנימי - כותרת', $page['inside_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'inside_text', 'מקטע פנימי - טקסט', $page['inside_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'reveal_kicker', 'Reveal - טקסט קטן', $page['reveal_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'reveal_title', 'Reveal - כותרת', $page['reveal_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'reveal_text', 'Reveal - טקסט', $page['reveal_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'options_kicker', 'בחירה - טקסט קטן', $page['options_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'options_title', 'בחירה - כותרת', $page['options_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'options_text', 'בחירה - טקסט', $page['options_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'world_group_label', 'בחירה - תווית עולם', $page['world_group_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'language_group_label', 'בחירה - תווית שפה', $page['language_group_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'summary_label', 'תווית סיכום', $page['summary_label'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'summary_world_default', 'סיכום - עולם ברירת מחדל', $page['summary_world_default'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'summary_language_default', 'סיכום - שפה ברירת מחדל', $page['summary_language_default'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'protocol_kicker', 'תהליך - טקסט קטן', $page['protocol_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'protocol_title', 'תהליך - כותרת', $page['protocol_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'protocol_text', 'תהליך - טקסט', $page['protocol_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'contact_kicker', 'יצירת קשר - טקסט קטן', $page['contact_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_title', 'יצירת קשר - כותרת', $page['contact_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'contact_text', 'יצירת קשר - טקסט', $page['contact_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'buy_kicker', 'רכישה - טקסט קטן', $page['buy_kicker'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'buy_title', 'רכישה - כותרת', $page['buy_title'] ?? '', true); ?>
            <?php $this->admin_textarea_field($active, 'buy_text', 'רכישה - טקסט', $page['buy_text'] ?? '', true); ?>
            <?php $this->admin_text_field($active, 'buy_primary', 'רכישה - כפתור ראשי', $page['buy_primary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'buy_secondary', 'רכישה - כפתור משני', $page['buy_secondary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'buy_third', 'רכישה - כפתור שלישי', $page['buy_third'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_primary', 'קשר - כפתור ראשי', $page['contact_primary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_secondary', 'קשר - כפתור משני', $page['contact_secondary'] ?? ''); ?>
            <?php $this->admin_text_field($active, 'contact_third', 'קשר - כפתור שלישי', $page['contact_third'] ?? ''); ?>
        </div>

        <?php if ($active === 'mystery-product') : ?>
            <?php $this->admin_repeatable_fields($active, 'hero_cards', 'פתיח - תמונות הקלפים המרחפים סביב הקופסה', $page['hero_cards'] ?? [], ['modifier' => 'מיקום קבוע: slab / pack / single', 'label' => 'טקסט קטן על הקלף', 'image' => 'תמונת הקלף המרחף', 'alt' => 'תיאור תמונה']); ?>
        <?php endif; ?>

        <?php $this->admin_repeatable_fields($active, 'ledger', 'מדדי פתיח / Ledger', $page['ledger'] ?? [], ['value' => 'מספר/קוד', 'label' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'features', 'כרטיסי מידע', $page['features'] ?? [], ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'steps', 'שלבי תהליך', $page['steps'] ?? [], ['step' => 'מספר שלב', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'rail', 'סרגל פתיחה', $page['rail'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'stream_labels', 'Mystery - תוויות אלמנטים בפתיחה', $page['stream_labels'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'showcase_labels', 'Mystery - תוויות Showcase', $page['showcase_labels'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'contents', 'תכולת Mystery Box', $page['contents'] ?? [], $active === 'mystery-product' ? ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט', 'image' => 'תמונה', 'alt' => 'תיאור תמונה'] : ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'worlds', 'עולמות Mystery Box', $page['worlds'] ?? [], ['value' => 'ערך מערכת', 'label' => 'שם', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'languages', 'שפות Mystery Box', $page['languages'] ?? [], ['value' => 'ערך מערכת', 'label' => 'שם', 'text' => 'טקסט']); ?>
        <?php
    }

    private function admin_text_field($service_key, $field, $label, $value, $wide = false)
    {
        ?>
        <label class="<?php echo $wide ? 'colt-admin__wide' : ''; ?>">
            <span><?php echo esc_html($label); ?></span>
            <input type="text" name="colt_service_pages[<?php echo esc_attr($service_key); ?>][<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr((string) $value); ?>">
        </label>
        <?php
    }

    private function admin_image_field($service_key, $field, $label, $value, $wide = false)
    {
        $value = (string) $value;
        ?>
        <label class="<?php echo $wide ? 'colt-admin__wide' : ''; ?>">
            <span><?php echo esc_html($label); ?></span>
            <?php $this->admin_media_input('colt_service_pages[' . $service_key . '][' . $field . ']', $value); ?>
        </label>
        <?php
    }

    private function admin_media_input($field_name, $field_value)
    {
        $field_value = (string) $field_value;
        ?>
        <span class="colt-admin__media">
            <span class="colt-admin__media-row">
                <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($field_value); ?>" dir="ltr">
                <button type="button" class="button colt-admin__media-button">בחירת תמונה</button>
            </span>
            <span class="colt-admin__media-preview"><?php if ($field_value !== '') : ?><img src="<?php echo esc_url($field_value); ?>" alt=""><?php endif; ?></span>
        </span>
        <?php
    }

    private function admin_textarea_field($service_key, $field, $label, $value, $wide = false)
    {
        ?>
        <label class="<?php echo $wide ? 'colt-admin__wide' : ''; ?>">
            <span><?php echo esc_html($label); ?></span>
            <textarea name="colt_service_pages[<?php echo esc_attr($service_key); ?>][<?php echo esc_attr($field); ?>]"><?php echo esc_textarea((string) $value); ?></textarea>
        </label>
        <?php
    }

    private function admin_repeatable_fields($service_key, $field, $title, array $items, array $columns)
    {
        ?>
        <section class="colt-admin__section">
            <h3><?php echo esc_html($title); ?></h3>
            <div class="colt-admin__repeat">
                <?php foreach ($items as $index => $item) : ?>
                    <div class="colt-admin__card">
                        <h4><?php echo esc_html($title . ' #' . ((int) $index + 1)); ?></h4>
                        <div class="colt-admin__grid">
                            <?php foreach ($columns as $column_key => $column_label) : ?>
                                <?php
                                $is_scalar_item = !is_array($item);
                                $field_name = $is_scalar_item
                                    ? 'colt_service_pages[' . $service_key . '][' . $field . '][' . $index . ']'
                                    : 'colt_service_pages[' . $service_key . '][' . $field . '][' . $index . '][' . $column_key . ']';
                                $field_value = $is_scalar_item ? $item : ($item[$column_key] ?? '');
                                $is_textarea = in_array($column_key, ['text', 'detail'], true);
                                $is_image = $column_key === 'image' || substr((string) $column_key, -6) === '_image';
                                $is_wide = in_array($column_key, ['text', 'detail', 'url', 'image'], true) || $is_image;
                                ?>
                                <label class="<?php echo $is_wide ? 'colt-admin__wide' : ''; ?>">
                                    <span><?php echo esc_html($column_label); ?></span>
                                    <?php if ($is_image) : ?>
                                        <?php $this->admin_media_input($field_name, $field_value); ?>
                                    <?php elseif ($is_textarea) : ?>
                                        <textarea name="<?php echo esc_attr($field_name); ?>"><?php echo esc_textarea((string) $field_value); ?></textarea>
                                    <?php else : ?>
                                        <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr((string) $field_value); ?>">
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    public static function default_service_pages()
    {
        $pages = [
            'singles' => [
                'nav_label' => 'סינגלים וסלאבים',
                'eyebrow' => 'COLT SINGLES',
                'title' => 'קלפים שנבחרים אחד אחד, לא עוד מדף גנרי.',
                'lead' => 'עמוד לפריטים הבודדים והמדורגים: קלפים עם סיפור, מצב ברור, תמונות נקיות והצגה שמרגישה כמו גלריית אספנות ולא כמו רשימת מוצרים.',
                'intro_title' => 'האוסף מתחיל בפריט הנכון.',
                'intro_text' => 'אנחנו בונים חוויית קנייה סביב מה שאספן באמת צריך לראות: מצב הקלף, נראות, רמת נדירות, התאמה לאוסף, וסיבה ברורה למה הפריט הזה ראוי להיות אצלך.',
                'scene' => self::asset_url('assets/service-scenes/singles-gallery.png'),
                'layout' => 'gallery',
                'motion' => 'cascade',
                'artifact_labels' => ['Slab wall', 'Gold rail', 'Collector scan'],
                'primary_label' => 'לראות פריטים בחנות',
                'secondary_label' => 'לבקש קלף ספציפי',
                'primary_kind' => 'shop',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'סינגלים נבחרים'],
                    ['value' => '02', 'label' => 'סלאבים מדורגים'],
                    ['value' => '03', 'label' => 'תצוגת מצב ותמונות'],
                    ['value' => '04', 'label' => 'התאמה לאוסף'],
                ],
                'features' => [
                    ['meta' => 'CURATION', 'title' => 'בחירה לפי ערך אספני', 'text' => 'לא כל קלף מעניין אותנו באותה מידה. אנחנו מדגישים פריטים עם ביקוש, נראות, מצב או סיפור שמצדיקים במה.'],
                    ['meta' => 'CONDITION', 'title' => 'שקיפות מצב', 'text' => 'העמוד מתוכנן להציג מצב, דרגת דירוג, הערות חשובות ותמונות שמורידות ספק לפני רכישה.'],
                    ['meta' => 'DISPLAY', 'title' => 'תצוגת פרימיום', 'text' => 'קלף טוב צריך להרגיש כמו אובייקט. השפה הוויזואלית משלבת זהב, עומק, דמות מותג ותחושת vault.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'אוצרות', 'text' => 'מכניסים לעמוד רק פריטים שמתאימים לקו של COLT: מעניינים, נקיים, נדירים או שימושיים לבניית אוסף.'],
                    ['step' => '02', 'title' => 'צילום ותיאור', 'text' => 'מבליטים מצב, פינות, מרכזיות, דירוג, שפה, סט וכל פרט שעוזר לקבל החלטה.'],
                    ['step' => '03', 'title' => 'שיוך לאוסף', 'text' => 'מייצרים חלוקה לפי סוגי אספנים: נוסטלגיה, השקעה, סטים, דמויות, ספורט או פריטי showcase.'],
                    ['step' => '04', 'title' => 'רכישה או פנייה', 'text' => 'הלקוח יכול לקנות ישירות או לבקש התאמה אישית אם הוא מחפש כיוון דומה.'],
                ],
                'details' => [
                    'קלפים בודדים מפוקימון, וואן פיס, ספורט, מארוול ודיסני.',
                    'סלאבים מדורגים עם מקום ברור לציון החברה, ציון, מצב ותמונות.',
                    'אזורי המלצה לפריטים משלימים: מגן, תצוגה, כספת או דירוג.',
                    'אפשרות לבנות סביב הפריט המשך מסע לעמוד מוצר או לפנייה אישית.',
                ],
                'fit' => [
                    'אספנים שמחפשים פריט מדויק ולא סל מוצרים אקראי.',
                    'לקוחות שרוצים להבין מה הם קונים לפני שהם משלמים.',
                    'מי שבונה אוסף לפי דמות, סט, ענף ספורט, שפה או תקציב.',
                ],
                'note' => 'העמוד צריך להרגיש כמו גלריית פריטים חדה: פחות רעש, יותר ביטחון, יותר כבוד לקלף.',
            ],
            'jewelry' => [
                'nav_label' => 'תכשיטי אספנים',
                'eyebrow' => 'COLLECTOR JEWELRY',
                'title' => 'פריט אספנות שהופך לאובייקט לביש.',
                'lead' => 'שירות לתכשיטים ומחזיקים סביב קלפים, סלאבים או פריטי אספנות יקרים: לא עוד אביזר, אלא דרך להציג את הפריט שלך כמו סמל אישי.',
                'intro_title' => 'כשקלף מפסיק להיות רק קלף.',
                'intro_text' => 'העמוד בנוי סביב תהליך שמתחיל בפריט שלך, ממשיך בבחירת חומר, צורה ונראות, ומסתיים באובייקט שמרגיש יוקרתי, מוגן ומאוד אישי.',
                'scene' => self::asset_url('assets/service-scenes/jewelry-atelier.png'),
                'layout' => 'atelier',
                'motion' => 'orbit',
                'artifact_labels' => ['Gold mount', 'Stone glint', 'Card pendant'],
                'primary_label' => 'להתחיל עיצוב אישי',
                'secondary_label' => 'לשלוח פריט לבדיקה',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'פריט בסיס'],
                    ['value' => '02', 'label' => 'קונספט עיצובי'],
                    ['value' => '03', 'label' => 'חומרים וגימור'],
                    ['value' => '04', 'label' => 'תוצאה אישית'],
                ],
                'features' => [
                    ['meta' => 'OBJECT', 'title' => 'מחזיק לקלף יוקרתי', 'text' => 'מסגרת או מחזיק שמציגים קלף יקר בצורה בולטת, עם איזון בין נראות להגנה.'],
                    ['meta' => 'MATERIAL', 'title' => 'שפה של זהב ושחור', 'text' => 'הכיוון העיצובי נשען על זהב, שחור, זכוכית, מתכת ותחושת premium שמתאימה למותג.'],
                    ['meta' => 'PERSONAL', 'title' => 'סיפור אישי', 'text' => 'אפשר לבנות סביב דמות, סט, שנה, ענף ספורט, אירוע או פריט שנושא ערך רגשי.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'בחירת פריט', 'text' => 'מבינים מה הפריט, מה הגודל שלו, מה רמת הרגישות ומה הסיפור שרוצים לשדר.'],
                    ['step' => '02', 'title' => 'סקיצה וכיוון', 'text' => 'בוחרים אם הולכים על תכשיט, מחזיק, מסגרת או אובייקט תצוגה.'],
                    ['step' => '03', 'title' => 'חומר וגימור', 'text' => 'מתאימים צבע, גימור, שכבת הגנה, תחושת משקל ופרטים קטנים.'],
                    ['step' => '04', 'title' => 'אישור והפקה', 'text' => 'רק אחרי שהכיוון ברור ממשיכים להפקה או להצעת מחיר מסודרת.'],
                ],
                'details' => [
                    'מתאים לקלפים יקרים, סלאבים, קלפי חתימה ופריטים עם ערך רגשי.',
                    'אפשרות לעמוד שמציג inspiration, חומרים, תהליך והזמנה אישית.',
                    'הדגש הוא על יוקרה ולא על גימיק: הפריט צריך להרגיש רציני.',
                    'קריאות לפעולה ממוקדות: בדיקת התאמה, שיחה, שליחת תמונה של הפריט.',
                ],
                'fit' => [
                    'אספנים שרוצים להפוך קלף לפריט הצהרה.',
                    'מתנות אספנות יוקרתיות.',
                    'לקוחות עם פריט יקר שרוצים תצוגה או נשיאה מיוחדת.',
                ],
                'note' => 'החוויה צריכה להרגיש כמו atelier קטן לאספנים: אישי, שחור, זהב, מוקפד.',
            ],
            'most-wanted' => [
                'nav_label' => 'Most Wanted',
                'eyebrow' => 'MOST WANTED',
                'title' => 'יש לך פריט שאנחנו מחפשים? זה המקום להתחיל.',
                'lead' => 'עמוד למוכרים שרוצים להציע לנו קלפים, סלאבים, אוספים או פריטים מבוקשים בלי להסתבך עם פרסום, משא ומתן ולוגיסטיקה.',
                'intro_title' => 'מכירה ישירה, מסודרת וברורה.',
                'intro_text' => 'המטרה היא להפוך את הפנייה לתהליך נוח: מה יש לך, באיזה מצב, כמה פריטים, ומה הדרך הכי נכונה לבדוק הצעה.',
                'scene' => self::asset_url('assets/service-scenes/most-wanted-room.png'),
                'layout' => 'wanted',
                'motion' => 'target',
                'artifact_labels' => ['Wanted ring', 'Offer lock', 'Case file'],
                'primary_label' => 'להציע פריט',
                'secondary_label' => 'לשלוח בוואטסאפ',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'בדיקת פריט'],
                    ['value' => '02', 'label' => 'אימות מצב'],
                    ['value' => '03', 'label' => 'הצעה מסודרת'],
                    ['value' => '04', 'label' => 'סגירה מהירה'],
                ],
                'features' => [
                    ['meta' => 'WANTED', 'title' => 'רשימת ביקוש', 'text' => 'העמוד יכול להציג קטגוריות שאנחנו מחפשים: סלאבים, קלפים נדירים, אוספים, ספורט וסטים ספציפיים.'],
                    ['meta' => 'PROOF', 'title' => 'תמונות ותיעוד', 'text' => 'הלקוח מקבל הנחיה ברורה איזה תמונות לשלוח כדי לחסוך זמן ולהעלות סיכוי להצעה טובה.'],
                    ['meta' => 'DEAL', 'title' => 'הצעה בלי רעש', 'text' => 'אם הפריט מתאים, ממשיכים להצעה מסודרת, תיאום העברה וסגירה נקייה.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'שליחת פרטים', 'text' => 'שם הפריט, תמונות, מצב, כמות, שפה, דירוג וכל פרט ידוע.'],
                    ['step' => '02', 'title' => 'בדיקה ראשונית', 'text' => 'בודקים התאמה לביקוש, מצב, מחיר שוק ורמת עניין.'],
                    ['step' => '03', 'title' => 'שיחה או הצעה', 'text' => 'אם זה מתאים, ממשיכים להצעה, שאלות השלמה או תיאום צפייה בפריט.'],
                    ['step' => '04', 'title' => 'סגירה והעברה', 'text' => 'מסכמים דרך העברה, תשלום ותיעוד כך שהמוכר יודע בדיוק מה קורה.'],
                ],
                'details' => [
                    'מתאים למוכרים פרטיים עם פריט אחד וגם לאוספים גדולים.',
                    'אפשר להדגיש שאנחנו לא קונים כל דבר, אלא מחפשים התאמה לקו האתר.',
                    'טופס קצר או וואטסאפ עם הנחיה לצילום קדמי, אחורי, ציון ופגמים.',
                    'חוויית עמוד שמשרה אמון: פחות לחץ, יותר מקצועיות.',
                ],
                'fit' => [
                    'מוכר שרוצה עסקה מהירה בלי לפתוח חנות.',
                    'אספן שמצמצם אוסף.',
                    'מי שיש לו פריט נדיר ולא בטוח למי לפנות.',
                ],
                'note' => 'העמוד צריך לגרום למוכר להרגיש שיש מולו מערכת רצינית, לא קונה מזדמן.',
            ],
            'personal-search' => [
                'nav_label' => 'חיפוש אישי',
                'eyebrow' => 'PERSONAL HUNT',
                'title' => 'אנחנו מחפשים את הקלף שאתה לא מצליח למצוא.',
                'lead' => 'שירות איתור אישי לפריטים קשים: קלף ספציפי, סלאב, שפה, סט, שנה, שחקן, דמות או תקציב. אתה מגדיר מטרה, אנחנו מפעילים את הרשת.',
                'intro_title' => 'ציד אספנות עם יעד ברור.',
                'intro_text' => 'במקום שתבזבז שעות בקבוצות, מכירות ולייבים, העמוד מציג תהליך מסודר: מה מחפשים, כמה זה חשוב, מה התקציב ומה נחשב הצלחה.',
                'scene' => self::asset_url('assets/service-scenes/personal-search-hunt.png'),
                'layout' => 'hunt',
                'motion' => 'map',
                'artifact_labels' => ['Target node', 'Market path', 'Signal lock'],
                'primary_label' => 'לפתוח חיפוש',
                'secondary_label' => 'לשלוח תמונת יעד',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'מטרה מוגדרת'],
                    ['value' => '02', 'label' => 'תקציב וטווח'],
                    ['value' => '03', 'label' => 'איתור מקורות'],
                    ['value' => '04', 'label' => 'הצעה לאישור'],
                ],
                'features' => [
                    ['meta' => 'TARGET', 'title' => 'מגדירים יעד', 'text' => 'שם קלף, סט, דמות, שחקן, מספר, שפה, דירוג רצוי וטווח מחיר.'],
                    ['meta' => 'NETWORK', 'title' => 'חיפוש דרך מקורות', 'text' => 'בודקים ערוצים, ספקים, קהילות, לייבים ואוספים פרטיים כדי למצוא התאמה.'],
                    ['meta' => 'FILTER', 'title' => 'מסננים רעש', 'text' => 'לא כל מציאה היא עסקה טובה. בודקים מצב, אמינות, מחיר ולוגיסטיקה לפני שמביאים לך אפשרות.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'Brief קצר', 'text' => 'אתה שולח מה מחפשים, למה זה חשוב, ומה הגבולות: תקציב, שפה, מצב, דירוג וזמן.'],
                    ['step' => '02', 'title' => 'בדיקת שוק', 'text' => 'ממפים זמינות, טווחי מחיר, חלופות והאם כדאי להמתין או לפעול מהר.'],
                    ['step' => '03', 'title' => 'איתור והצעות', 'text' => 'כשיש פריט רלוונטי, מציגים לך תמונות, תנאים, מחיר וסיכון.'],
                    ['step' => '04', 'title' => 'סגירה', 'text' => 'אם מאשרים, מתקדמים לרכישה, תיאום או ליווי עד שהפריט אצלך.'],
                ],
                'details' => [
                    'מתאים לפריטים נדירים, שפות ספציפיות, קלפים מדורגים ויעדים אישיים.',
                    'אפשר להציג דוגמאות של חיפוש לפי תקציב, סט, דמות או ציון דירוג.',
                    'טופס הפנייה צריך להיות קצר אבל חכם: יעד, תקציב, חובה/גמיש, דדליין.',
                    'העמוד נותן תחושה של משימה: COLT יוצא לציד בשבילך.',
                ],
                'fit' => [
                    'אספן עם קלף יעד ברור.',
                    'לקוח שרוצה לחסוך זמן וטעויות.',
                    'מי שמחפש פריט מתנה או השלמה לאוסף.',
                ],
                'note' => 'הדמות של COLT כאן צריכה להרגיש כמו hunter: עומד בחושך עם קלפים כמו מטרות סביבו.',
            ],
            'grading' => [
                'nav_label' => 'דירוג',
                'eyebrow' => 'GRADING SUBMISSION',
                'title' => 'מהקלף ביד ועד הסלאב, בלי לאבד שליטה בדרך.',
                'lead' => 'שירות הכנה ושליחה לדירוג: בדיקה ראשונית, סינון, תיעוד, אריזה, שליחה ומעקב. המטרה היא להפוך תהליך מלחיץ למסלול ברור.',
                'intro_title' => 'דירוג מתחיל לפני השליחה.',
                'intro_text' => 'עמוד השירות מסביר מה בודקים, איך מחליטים אם כדאי לשלוח, מה הסיכונים ומה הלקוח מקבל לאורך הדרך.',
                'scene' => self::asset_url('assets/service-scenes/grading-lab.png'),
                'layout' => 'lab',
                'motion' => 'scan',
                'artifact_labels' => ['Surface scan', 'Centering grid', 'Slab prep'],
                'primary_label' => 'בדיקת קלפים לדירוג',
                'secondary_label' => 'לשלוח תמונות',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'בדיקה ראשונית'],
                    ['value' => '02', 'label' => 'סינון לשליחה'],
                    ['value' => '03', 'label' => 'תיעוד ואריזה'],
                    ['value' => '04', 'label' => 'מעקב ועדכון'],
                ],
                'features' => [
                    ['meta' => 'PRECHECK', 'title' => 'בדיקת כדאיות', 'text' => 'בודקים מצב, פינות, מרכזיות, שריטות, ערך משוער ורמת סיכון לפני שממליצים לשלוח.'],
                    ['meta' => 'PACKING', 'title' => 'אריזה נכונה', 'text' => 'מכינים את הקלפים לשליחה בצורה מסודרת, עם תיעוד וסדר שמקטין בלבול.'],
                    ['meta' => 'TRACKING', 'title' => 'מעקב תהליך', 'text' => 'הלקוח יודע באיזה שלב הפריט נמצא ומה הצעד הבא עד החזרה.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'צילום ובדיקה', 'text' => 'מקבלים תמונות או בודקים פיזית, ומסמנים קלפים ששווה לשקול לשליחה.'],
                    ['step' => '02', 'title' => 'החלטת שליחה', 'text' => 'מסבירים מה יכול להשפיע על הציון ומה העלות מול פוטנציאל הערך.'],
                    ['step' => '03', 'title' => 'תיעוד ואריזה', 'text' => 'מכינים רשימה, תמונות, אריזה ושיוך כדי שהפריטים לא יאבדו בהליך.'],
                    ['step' => '04', 'title' => 'חזרה וסגירה', 'text' => 'כשהסלאבים חוזרים, אפשר לאסוף, לשמור בכספת או להציע למכירה.'],
                ],
                'details' => [
                    'העמוד צריך להבהיר שאין הבטחה לציון, אלא תהליך מסודר להקטנת טעויות.',
                    'אפשר לחבר בסוף למסלול כספת או מכירה אחרי קבלת הסלאב.',
                    'מומלץ להציג checklist קצר: פינות, פני שטח, מרכזיות, גב, אותנטיות.',
                    'קריאה לפעולה צריכה לבקש תמונות טובות ולא טקסט ארוך.',
                ],
                'fit' => [
                    'קלפים שיכולים לקבל ערך נוסף מסלאב.',
                    'אספן שלא רוצה להתעסק בלוגיסטיקה.',
                    'מי שרוצה להבין אם שווה לשלוח לפני שהוא משלם.',
                ],
                'note' => 'הטון צריך להיות מדויק ואחראי: מקצועי, לא מבטיח ציון, כן בונה אמון.',
            ],
            'whatnot' => [
                'nav_label' => 'Whatnot',
                'eyebrow' => 'LIVE SELLING',
                'title' => 'מכירה חיה לאספנים, בלי לבנות הכל מאפס.',
                'lead' => 'שירות ללייבים ומכירה ב־Whatnot: סידור מלאי, בניית דרופ, תמחור, הצגה, קצב מכירה ותפעול חכם מול קהל אספנים.',
                'intro_title' => 'לייב טוב הוא לא רק מצלמה פתוחה.',
                'intro_text' => 'העמוד מציג את כל מה שקורה מאחורי מכירה חיה טובה: בחירת פריטים, סדר פתיחה, בניית מתח, אמון ותהליך אחרי המכירה.',
                'scene' => self::asset_url('assets/service-scenes/whatnot-stage.png'),
                'layout' => 'stage',
                'motion' => 'spotlight',
                'artifact_labels' => ['Live beam', 'Drop queue', 'Final bid'],
                'primary_label' => 'לתכנן לייב מכירה',
                'secondary_label' => 'להציע מלאי ללייב',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'מיון מלאי'],
                    ['value' => '02', 'label' => 'בניית דרופ'],
                    ['value' => '03', 'label' => 'ניהול לייב'],
                    ['value' => '04', 'label' => 'סגירת הזמנות'],
                ],
                'features' => [
                    ['meta' => 'DROP', 'title' => 'בניית סדר מכירה', 'text' => 'לא זורקים הכל לשידור. בונים קצב: פתיחה, פריטים חזקים, הפתעות וסגירה.'],
                    ['meta' => 'AUDIENCE', 'title' => 'התאמה לקהל', 'text' => 'Pokemon, One Piece, ספורט או מארוול דורשים שפה, קצב וסטים שונים.'],
                    ['meta' => 'TRUST', 'title' => 'שקיפות בזמן אמת', 'text' => 'מצב, מחיר, משלוח ותיאור צריכים להיות ברורים כדי שקהל יחזור.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'בדיקת מלאי', 'text' => 'מבינים מה יש, מה מתאים ללייב ומה עדיף למכור בחנות או לשמור.'],
                    ['step' => '02', 'title' => 'דרופ ותמחור', 'text' => 'בונים קבוצות מכירה, נקודות פתיחה, פריטים מובילים ומוצרים משלימים.'],
                    ['step' => '03', 'title' => 'שידור', 'text' => 'מנהלים קצב, הצגה, שאלות, מומנטום ותחושת אירוע.'],
                    ['step' => '04', 'title' => 'אחרי המכירה', 'text' => 'מסדרים הזמנות, משלוחים, מעקב ותובנות ללייב הבא.'],
                ],
                'details' => [
                    'מתאים למי שיש מלאי אבל אין לו קהל או תהליך מכירה חיה.',
                    'אפשר לשלב Mystery Box, סינגלים, סלאבים ואביזרים בדרופ אחד.',
                    'העמוד צריך לשדר אנרגיה של במה, לא רק שירות תפעולי.',
                    'קריאות לפעולה: תכנון לייב, בדיקת מלאי, שיתוף פעולה.',
                ],
                'fit' => [
                    'מוכרים עם מלאי אספנות.',
                    'אספנים שרוצים נזילות בלי לנהל הכל לבד.',
                    'מותגים או נותני שירות שרוצים אירוע מכירה בתחום.',
                ],
                'note' => 'העמוד צריך להרגיש כמו backstage של מכירה חיה: חושך, זהב, זרקורים, קלפים באוויר.',
            ],
            'portfolio' => [
                'nav_label' => 'תיק השקעות',
                'eyebrow' => 'COLLECTOR PORTFOLIO',
                'title' => 'לבנות אוסף עם אסטרטגיה, לא רק עם אינטואיציה.',
                'lead' => 'שירות לבניית תיק אספנות לפי תקציב, טעם אישי, רמת סיכון, ביקוש ופוטנציאל. התוכן בעמוד נשאר אחראי: אין הבטחות תשואה, יש חשיבה מסודרת.',
                'intro_title' => 'אוסף יכול להיות גם מערכת החלטות.',
                'intro_text' => 'העמוד מציג איך הופכים תקציב ורצון לכיוון פעולה: חלוקת קטגוריות, בחירת עוגנים, מעקב, שמירה, דירוג ומכירה עתידית.',
                'scene' => self::asset_url('assets/service-scenes/portfolio-strategy.png'),
                'layout' => 'strategy',
                'motion' => 'strategy',
                'artifact_labels' => ['Asset map', 'Risk line', 'Vault route'],
                'primary_label' => 'לבנות תיק אספנות',
                'secondary_label' => 'שיחת התאמה',
                'primary_kind' => 'contact',
                'secondary_kind' => 'whatsapp',
                'metrics' => [
                    ['value' => '01', 'label' => 'תקציב'],
                    ['value' => '02', 'label' => 'קטגוריות'],
                    ['value' => '03', 'label' => 'סיכון וביקוש'],
                    ['value' => '04', 'label' => 'מעקב ושמירה'],
                ],
                'features' => [
                    ['meta' => 'STRATEGY', 'title' => 'חלוקת תיק', 'text' => 'מגדירים איזון בין פריטים יציבים, פריטי צמיחה, פריטים רגשיים ופריטי showcase.'],
                    ['meta' => 'RISK', 'title' => 'חשיבה על סיכון', 'text' => 'לא כל הייפ הוא השקעה. העמוד מדבר על ביקוש, נזילות, מצב, אותנטיות ושמירה.'],
                    ['meta' => 'OPS', 'title' => 'מסלול פעולה', 'text' => 'רכישה, דירוג, כספת, מכירה או החזקה לטווח ארוך הופכים לחלק מתוכנית אחת.'],
                ],
                'process' => [
                    ['step' => '01', 'title' => 'פרופיל אספן', 'text' => 'מבינים תקציב, תחומי עניין, טווח זמן, סיכון רצוי ומה כבר קיים אצלך.'],
                    ['step' => '02', 'title' => 'מפת קטגוריות', 'text' => 'מחלקים בין Pokemon, One Piece, ספורט, מארוול, דיסני, סלאבים וסינגלים.'],
                    ['step' => '03', 'title' => 'בחירת עוגנים', 'text' => 'מזהים פריטים מרכזיים, פריטים משלימים ופעולות שמעלות איכות תיק.'],
                    ['step' => '04', 'title' => 'מעקב ושמירה', 'text' => 'מתכננים איפה לשמור, מה לדרג, מתי למכור ומה לבדוק מחדש.'],
                ],
                'details' => [
                    'העמוד חייב לכלול הבהרה: אספנות אינה הבטחת תשואה או ייעוץ פיננסי.',
                    'אפשר להציג מודל תיק: עוגנים, הזדמנויות, רגש, נזילות.',
                    'חיבור טבעי לשירותי דירוג, כספת, חיפוש אישי וסינגלים.',
                    'החוויה צריכה להרגיש כמו חדר אסטרטגיה חשוך עם מפות, קלפים וזהב.',
                ],
                'fit' => [
                    'לקוח עם תקציב ורצון לבנות אוסף חכם.',
                    'אספן שרוצה סדר ועדיפות בין רכישות.',
                    'מי שרואה באספנות שילוב של תשוקה, ערך ותכנון.',
                ],
                'note' => 'לא מבטיחים עלייה בערך. כן מציגים תהליך חשיבה מסודר ואחראי.',
            ],
        ];

        $shared = [
            'nav_start_label' => 'התחלה',
            'nav_brief_label' => 'מה מקבלים',
            'nav_process_label' => 'תהליך',
            'nav_contact_label' => 'פנייה',
            'brief_kicker' => 'SERVICE DESIGN',
            'process_kicker' => 'THE FLOW',
            'process_title' => 'מהרגע הראשון ועד הפעולה הבאה.',
            'process_text' => 'כל שירות מקבל מסלול ברור, כדי שהלקוח יבין מה שולחים, מה מקבלים, מה קורה אחרי הפנייה ואיפה הערך האמיתי של COLT נכנס לתמונה.',
            'details_kicker' => 'DETAILS',
            'details_title' => 'הדברים הקטנים שמייצרים אמון.',
            'fit_kicker' => 'BEST FIT',
            'fit_title' => 'למי זה מתאים?',
            'contact_kicker' => 'START WITH COLT',
            'contact_title' => '',
            'contact_text' => 'שלח לנו כמה פרטים, תמונות או כיוון כללי. משם נבנה לך את הצעד הבא בצורה מסודרת, נקייה ועם תשומת לב לפרטים הקטנים.',
        ];

        foreach ($pages as $key => $page) {
            $pages[$key] = $page + $shared;
            if ($pages[$key]['contact_title'] === '') {
                $pages[$key]['contact_title'] = $pages[$key]['primary_label'] ?? '';
            }
        }

        return $pages;
    }

    public static function home_page()
    {
        $content = self::content_settings();
        $overrides = isset($content['home']) && is_array($content['home']) ? $content['home'] : [];

        return self::merge_content_values(self::default_home_page(), $overrides);
    }

    public static function service_pages()
    {
        $content = self::content_settings();
        $overrides = isset($content['services']) && is_array($content['services']) ? $content['services'] : [];

        return self::merge_content_values(self::default_service_pages(), $overrides);
    }

    public static function special_pages()
    {
        $content = self::content_settings();
        $overrides = isset($content['special']) && is_array($content['special']) ? $content['special'] : [];

        return self::merge_content_values(self::default_special_pages(), $overrides);
    }

    public static function special_page($key)
    {
        $pages = self::special_pages();

        return $pages[$key] ?? [];
    }

    public static function services()
    {
        $home = self::home_page();
        $defaults = isset($home['service_cards']) && is_array($home['service_cards'])
            ? $home['service_cards']
            : [];

        return array_map(static function (array $service): array {
            if (empty($service['slug'])) {
                $service['url'] = home_url('/');
                return $service;
            }

            if (strpos($service['slug'], '../') === 0) {
                $service['url'] = home_url('/' . ltrim(substr($service['slug'], 3), '/') . '/');
                return $service;
            }

            $path = '/services/' . $service['slug'] . '/';
            $service['url'] = home_url($path);
            return $service;
        }, $defaults);
    }

    public static function featured_products($limit = 8)
    {
        if (!class_exists('WooCommerce')) {
            return [];
        }

        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => max(1, min(12, $limit)),
            'no_found_rows' => true,
        ]);

        $products = [];

        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());

            if (!$product) {
                continue;
            }

            $products[] = [
                'title' => get_the_title(),
                'url' => get_permalink(),
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail') ?: wc_placeholder_img_src('woocommerce_thumbnail'),
                'price' => $product->get_price_html(),
            ];
        }

        wp_reset_postdata();

        return $products;
    }
}
