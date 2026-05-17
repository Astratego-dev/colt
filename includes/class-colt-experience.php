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
    }

    public function render_home_experience($atts = [])
    {
        $atts = shortcode_atts([
            'products' => 8,
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

    public static function services()
    {
        $defaults = [
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
        ];

        return array_map(static function (array $service): array {
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
