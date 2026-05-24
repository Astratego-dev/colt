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

    public static function service_pages()
    {
        return [
            'singles' => [
                'nav_label' => 'סינגלים וסלאבים',
                'eyebrow' => 'COLT SINGLES',
                'title' => 'קלפים שנבחרים אחד אחד, לא עוד מדף גנרי.',
                'lead' => 'עמוד לפריטים הבודדים והמדורגים: קלפים עם סיפור, מצב ברור, תמונות נקיות והצגה שמרגישה כמו גלריית אספנות ולא כמו רשימת מוצרים.',
                'intro_title' => 'האוסף מתחיל בפריט הנכון.',
                'intro_text' => 'אנחנו בונים חוויית קנייה סביב מה שאספן באמת צריך לראות: מצב הקלף, נראות, רמת נדירות, התאמה לאוסף, וסיבה ברורה למה הפריט הזה ראוי להיות אצלך.',
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
