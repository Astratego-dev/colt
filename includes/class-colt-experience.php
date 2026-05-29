<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Colt_Experience
{
    private static $instance = null;
    private $assets_enqueued = false;

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
        add_shortcode('colt_service_experience', [$this, 'render_service_experience']);

        foreach (self::service_shortcode_map() as $shortcode => $service_key) {
            add_shortcode($shortcode, function ($atts = []) use ($service_key, $shortcode) {
                return $this->render_named_service_experience($service_key, $atts, $shortcode);
            });
        }

        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu']);
            add_action('admin_post_colt_experience_save_content', [$this, 'handle_admin_save_content']);
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

    public function render_named_service_experience($service_key, $atts = [], $shortcode = 'colt_service_experience')
    {
        $service_key = sanitize_key((string) $service_key);
        $service_aliases = [
            'most_wanted' => 'most-wanted',
            'personal_search' => 'personal-search',
            'search' => 'personal-search',
            'mystery_box' => 'mystery',
            'mystery-box' => 'mystery',
            'the-vault' => 'vault',
        ];
        $service_key = $service_aliases[$service_key] ?? $service_key;

        if ($service_key === 'vault') {
            return $this->render_vault_experience($atts);
        }

        if ($service_key === 'mystery') {
            return $this->render_mystery_box_experience($atts);
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
            $is_url_field = in_array($key_name, ['scene', 'url', 'image', 'primary_url', 'secondary_url'], true) || substr($key_name, -4) === '_url';
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

    private static function service_admin_shortcodes()
    {
        return [
            'home' => '[colt_home_experience]',
            'vault' => '[colt_vault_experience]',
            'mystery' => '[colt_mystery_box_experience]',
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

        <?php $this->admin_repeatable_fields($active, 'ledger', 'מדדי פתיח / Ledger', $page['ledger'] ?? [], ['value' => 'מספר/קוד', 'label' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'features', 'כרטיסי מידע', $page['features'] ?? [], ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'steps', 'שלבי תהליך', $page['steps'] ?? [], ['step' => 'מספר שלב', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
        <?php $this->admin_repeatable_fields($active, 'rail', 'סרגל פתיחה', $page['rail'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'stream_labels', 'Mystery - תוויות אלמנטים בפתיחה', $page['stream_labels'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'showcase_labels', 'Mystery - תוויות Showcase', $page['showcase_labels'] ?? [], ['label' => 'תווית']); ?>
        <?php $this->admin_repeatable_fields($active, 'contents', 'תכולת Mystery Box', $page['contents'] ?? [], ['meta' => 'Meta', 'title' => 'כותרת', 'text' => 'טקסט']); ?>
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
                                $is_wide = in_array($column_key, ['text', 'detail', 'url', 'image'], true);
                                ?>
                                <label class="<?php echo $is_wide ? 'colt-admin__wide' : ''; ?>">
                                    <span><?php echo esc_html($column_label); ?></span>
                                    <?php if ($is_textarea) : ?>
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
