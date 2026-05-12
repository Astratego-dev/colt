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
            'colt-experience',
            COLT_EXPERIENCE_URL . 'assets/css/colt-experience.css',
            [],
            COLT_EXPERIENCE_VERSION
        );

        wp_enqueue_script(
            'colt-experience',
            COLT_EXPERIENCE_URL . 'assets/js/colt-experience.js',
            [],
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

    public static function services()
    {
        $defaults = [
            [
                'title' => 'THE VAULT',
                'eyebrow' => 'כספת אספנים',
                'text' => 'אחסון מבוטח, הרמטי ומותאם לפריטי אספנות יקרי ערך.',
                'slug' => 'the-vault',
                'tone' => 'vault',
            ],
            [
                'title' => 'MOST WANTED',
                'eyebrow' => 'אנחנו מחפשים',
                'text' => 'רשימת פריטים מבוקשים שאפשר למכור לנו ישירות.',
                'slug' => 'most-wanted',
                'tone' => 'wanted',
            ],
            [
                'title' => 'חיפוש אישי',
                'eyebrow' => 'איתור לפי דרישה',
                'text' => 'מפעילים קשרים, ערוצים וקהילה כדי למצוא את הפריט הנכון.',
                'slug' => '%d7%97%d7%99%d7%a4%d7%95%d7%a9-%d7%90%d7%99%d7%a9%d7%99',
                'tone' => 'search',
            ],
            [
                'title' => 'דירוג',
                'eyebrow' => 'הכנה ושליחה',
                'text' => 'תהליך מסודר לקלפים שצריכים להגיע לדירוג נכון.',
                'slug' => 'cshev',
                'tone' => 'grading',
            ],
            [
                'title' => 'Whatnot',
                'eyebrow' => 'מכירה חיה',
                'text' => 'ניהול מלאי, חשיפה ולייבים לקהל אספנים מתאים.',
                'slug' => '%d7%9e%d7%9b%d7%99%d7%a8%d7%94-%d7%91whatnot',
                'tone' => 'whatnot',
            ],
            [
                'title' => 'תיק השקעות',
                'eyebrow' => 'אספנות עם אסטרטגיה',
                'text' => 'בניית תיק אספנות לפי תקציב, פוטנציאל וביקוש.',
                'slug' => '%d7%91%d7%a0%d7%99%d7%99%d7%aa-%d7%aa%d7%99%d7%a7-%d7%94%d7%a9%d7%a7%d7%a2%d7%95%d7%aa',
                'tone' => 'portfolio',
            ],
        ];

        return array_map(static function (array $service): array {
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
