<?php
if (!defined('ABSPATH')) {
    exit;
}

$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$portfolio_url = !empty($atts['portfolio_url']) ? (string) $atts['portfolio_url'] : home_url('/projects/');
$floors = [
    [
        'number' => '01',
        'slug' => 'growth',
        'tone' => 'green',
        'background' => 'growth-ads.png',
        'title' => 'Growth Ads',
        'subtitle' => 'קומת השיווק והצמיחה',
        'text' => 'פרסום שמחבר בין תכנון, קריאייטיב, דאטה ואופטימיזציה. כל עמדה בקומה אחראית על חלק אחר במסע שמביא לקוחות ולא רק קליקים.',
        'metric' => '+82%',
        'metric_label' => 'שיפור בביצועים',
        'stations' => [
            ['key' => 'organic', 'title' => 'אורגני', 'label' => 'Organic Bot', 'text' => 'תוכן, SEO, נוכחות אורגנית ובנייה של ערוץ שלא תלוי רק בתקציב פרסום.', 'task' => 'ממפה מילים, כותב כיווני תוכן ומסמן הזדמנויות צמיחה.', 'stat' => 'Content map'],
            ['key' => 'paid', 'title' => 'ממומן', 'label' => 'Ads Bot', 'text' => 'קמפיינים, קהלים, תקציבים, בדיקות A/B ומדידה של כל שלב בפאנל.', 'task' => 'מנטר ROAS, מזהה עייפות מודעות ומציע ניסויים חדשים.', 'stat' => 'ROAS lab'],
            ['key' => 'reviews', 'title' => 'ביקורות', 'label' => 'Trust Bot', 'text' => 'איסוף, ניהול והצגה של ביקורות שמחזקות אמון ומקטינות התנגדויות.', 'task' => 'מזהה ביקורות חזקות ומכין אותן להצגה בעמודי נחיתה.', 'stat' => 'Trust layer'],
            ['key' => 'faq', 'title' => 'שאלות נפוצות', 'label' => 'FAQ Bot', 'text' => 'שאלות אמיתיות מהלקוחות הופכות לתוכן שמסיר חסמים ומשפר המרות.', 'task' => 'מחבר שאלות לתשובות מכירתיות, קצרות וברורות.', 'stat' => 'Friction down'],
        ],
    ],
    [
        'number' => '02',
        'slug' => 'ai-chief',
        'tone' => 'purple',
        'background' => 'ai-chief.png',
        'title' => 'AI Chief',
        'subtitle' => 'קומת המנהל החיצוני',
        'text' => 'קומה שמדמה מנהל AI חיצוני לעסק: אוטומציות, תהליכים, ניתוח נתונים וחיסכון בזמן בלי להעמיס על הצוות.',
        'metric' => '24/7',
        'metric_label' => 'מערכת שעובדת ברקע',
        'stations' => [
            ['key' => 'automation', 'title' => 'אוטומציות', 'label' => 'Flow Bot', 'text' => 'חיבור בין כלים, משימות חוזרות, טריגרים ועדכונים אוטומטיים.', 'task' => 'בונה זרימות עבודה ומזהה פעולות שחוזרות על עצמן.', 'stat' => 'Auto flow'],
            ['key' => 'ops', 'title' => 'תהליכים', 'label' => 'Ops Bot', 'text' => 'תיעוד, חלוקת אחריות, סטנדרטים ומעקב כדי שהעסק יעבוד נקי יותר.', 'task' => 'מסדר שלבים, אחריות ודדליינים למסלול עבודה אחד.', 'stat' => 'Process map'],
            ['key' => 'insights', 'title' => 'ניתוח נתונים', 'label' => 'Data Bot', 'text' => 'איסוף נתונים ממערכות שונות והפיכתם להחלטות שאפשר לפעול לפיהן.', 'task' => 'מזהה מגמות ומוציא נקודות פעולה קצרות.', 'stat' => 'Signal scan'],
            ['key' => 'time', 'title' => 'חיסכון בזמן', 'label' => 'Time Bot', 'text' => 'החלפת פעולות ידניות במערכות שמקצרות זמן תגובה וזמן ביצוע.', 'task' => 'מדגיש צווארי בקבוק ומציע קיצור דרך תפעולי.', 'stat' => 'Hours saved'],
        ],
    ],
    [
        'number' => '03',
        'slug' => 'self-website',
        'tone' => 'blue',
        'background' => 'self-website-ai.png',
        'title' => 'Self Website',
        'subtitle' => 'קומת האתר העצמאי',
        'text' => 'אתר שמרגיש פשוט לניהול, מהיר לטעינה וברור ללקוח. כל עמדה מטפלת בשכבה אחרת של הנכס הדיגיטלי.',
        'metric' => '<2s',
        'metric_label' => 'יעד חוויית טעינה',
        'stations' => [
            ['key' => 'ux', 'title' => 'UX', 'label' => 'UX Bot', 'text' => 'מבנה, היררכיה, ניווט וזרימת משתמש שמובילה לפעולה.', 'task' => 'מסדר מסכים ומחליט מה חשוב לראות ראשון.', 'stat' => 'Flow clean'],
            ['key' => 'design', 'title' => 'עיצוב', 'label' => 'Design Bot', 'text' => 'שפה ויזואלית, צבעים, טיפוגרפיה ורכיבים שמרגישים כמו מותג ולא תבנית.', 'task' => 'בונה מערכת רכיבים ושומר על אחידות.', 'stat' => 'UI system'],
            ['key' => 'content', 'title' => 'תוכן', 'label' => 'Copy Bot', 'text' => 'כותרות, מסרים, דפי שירות ותוכן שמסביר מהר למה לבחור בכם.', 'task' => 'מחדד מסרים ומסיר טקסט מיותר.', 'stat' => 'Copy pass'],
            ['key' => 'speed', 'title' => 'מהירות', 'label' => 'Speed Bot', 'text' => 'אופטימיזציה לנכסים, קוד, טעינה ומדדי ביצועים.', 'task' => 'בודק משקל, תמונות, קבצים וטעינה בפועל.', 'stat' => 'Perf check'],
        ],
    ],
    [
        'number' => '04',
        'slug' => 'idea-labs',
        'tone' => 'orange',
        'background' => 'idea-labs.png',
        'title' => 'Idea Labs',
        'subtitle' => 'קומת המעבדה והפיתוח',
        'text' => 'מעבדה לבניית פתרונות דיגיטליים: מערכות, אפליקציות, SaaS ותהליכים שמתחילים ברעיון ומסתיימים במוצר עובד.',
        'metric' => 'MVP',
        'metric_label' => 'מרעיון למסלול ביצוע',
        'stations' => [
            ['key' => 'web', 'title' => 'Web', 'label' => 'Web Bot', 'text' => 'אתרים, פורטלים, אזורי לקוח וכלים שמבוססים על דפדפן.', 'task' => 'משרטט מסכים ומחבר בין צורך עסקי לממשק.', 'stat' => 'Web build'],
            ['key' => 'saas', 'title' => 'SaaS', 'label' => 'SaaS Bot', 'text' => 'מוצרים חוזרים, משתמשים, הרשאות, תשלום ותהליכי שירות.', 'task' => 'מפרק רעיון למודולים, מסכים ותמחור.', 'stat' => 'SaaS map'],
            ['key' => 'app', 'title' => 'App', 'label' => 'App Bot', 'text' => 'חוויות מובייל, אפליקציות קלות וכלים פנימיים לצוותים.', 'task' => 'מתכנן חוויית שימוש קטנה, ברורה ונגישה.', 'stat' => 'App flow'],
            ['key' => 'system', 'title' => 'System', 'label' => 'System Bot', 'text' => 'מערכות תפעול, חיבורים, דאשבורדים וכלי ניהול פנימיים.', 'task' => 'מתרגם תהליך ידני למסך עבודה מסודר.', 'stat' => 'Ops system'],
        ],
    ],
    [
        'number' => '05',
        'slug' => 'plugins',
        'tone' => 'pink',
        'background' => 'astratego-plugins.png',
        'title' => 'Astratego Plugins',
        'subtitle' => 'קומת הכלים שעובדים בשבילכם',
        'text' => 'פתרונות תוספים ואינטגרציות שנבנים סביב תהליך העבודה שלכם: פחות ידני, יותר מדידה, יותר שליטה.',
        'metric' => 'Plug-in',
        'metric_label' => 'כלים תפורים לעסק',
        'stations' => [
            ['key' => 'cms', 'title' => 'ניהול אתר', 'label' => 'CMS Bot', 'text' => 'מסכי ניהול, שורטקודים, בלוקים ושדות עריכה נוחים.', 'task' => 'מכין ממשק עריכה שמחזיר שליטה לצוות.', 'stat' => 'Admin UX'],
            ['key' => 'integrations', 'title' => 'אינטגרציות', 'label' => 'API Bot', 'text' => 'חיבור CRM, סליקה, משלוחים, טפסים ומערכות צד שלישי.', 'task' => 'מחבר נתונים בלי להעתיק ידנית בין מערכות.', 'stat' => 'API link'],
            ['key' => 'commerce', 'title' => 'WooCommerce', 'label' => 'Shop Bot', 'text' => 'חנות, מוצרים, מבצעים, סטטוסים וחוויית רכישה.', 'task' => 'משפר תהליך רכישה וניהול הזמנות.', 'stat' => 'Shop tools'],
            ['key' => 'reports', 'title' => 'דוחות', 'label' => 'Report Bot', 'text' => 'מדידה, דוחות, התראות ותובנות שוטפות.', 'task' => 'מסכם נתונים לדוח שאפשר להבין מהר.', 'stat' => 'Live report'],
        ],
    ],
];

$station_layouts = [
    [
        'x' => '28%',
        'y' => '63%',
        'z' => '-260px',
        'scale' => '.54',
        'angle' => '-32deg',
        'hover_turn' => '10deg',
        'focus_turn' => '18deg',
        'bot_gaze' => '-42deg',
        'terminal_x' => '58%',
        'terminal_turn' => '24deg',
        'surface_turn' => '-5deg',
        'walk_x' => '42px',
        'walk_y' => '16px',
        'delay' => '-.6s',
    ],
    [
        'x' => '48%',
        'y' => '76%',
        'z' => '70px',
        'scale' => '.72',
        'angle' => '18deg',
        'hover_turn' => '-8deg',
        'focus_turn' => '-15deg',
        'bot_gaze' => '24deg',
        'terminal_x' => '10%',
        'terminal_turn' => '-26deg',
        'surface_turn' => '4deg',
        'walk_x' => '58px',
        'walk_y' => '24px',
        'delay' => '-1.4s',
    ],
    [
        'x' => '72%',
        'y' => '66%',
        'z' => '-190px',
        'scale' => '.58',
        'angle' => '34deg',
        'hover_turn' => '-10deg',
        'focus_turn' => '-18deg',
        'bot_gaze' => '36deg',
        'terminal_x' => '7%',
        'terminal_turn' => '-32deg',
        'surface_turn' => '6deg',
        'walk_x' => '38px',
        'walk_y' => '18px',
        'delay' => '-2.1s',
    ],
    [
        'x' => '37%',
        'y' => '86%',
        'z' => '290px',
        'scale' => '.92',
        'angle' => '-12deg',
        'hover_turn' => '7deg',
        'focus_turn' => '12deg',
        'bot_gaze' => '-18deg',
        'terminal_x' => '54%',
        'terminal_turn' => '22deg',
        'surface_turn' => '-3deg',
        'walk_x' => '64px',
        'walk_y' => '28px',
        'delay' => '-3s',
    ],
];

$bot_model_url = '';
$bot_model_candidates = [
    'fun-robot.glb' => [],
    'fun robot.glb' => [],
    'fun-robot.gltf' => ['fun-robot.bin', 'fun robot.bin'],
    'fun robot.gltf' => ['fun robot.bin', 'fun%20robot.bin'],
];

foreach ($bot_model_candidates as $model_filename => $required_files) {
    if (!file_exists(COLT_EXPERIENCE_DIR . 'assets/models/astratego-bot/' . $model_filename)) {
        continue;
    }

    if (empty($required_files)) {
        $bot_model_url = COLT_EXPERIENCE_URL . 'assets/models/astratego-bot/' . rawurlencode($model_filename);
        break;
    }

    foreach ($required_files as $required_file) {
        if (file_exists(COLT_EXPERIENCE_DIR . 'assets/models/astratego-bot/' . $required_file)) {
            $bot_model_url = COLT_EXPERIENCE_URL . 'assets/models/astratego-bot/' . rawurlencode($model_filename);
            break 2;
        }
    }
}
?>

<section class="astratego-tower" dir="rtl" data-colt-xp data-astratego-tower data-version="2.0.6">
    <canvas class="colt-xp__canvas stratego-tower__canvas" data-colt-canvas aria-hidden="true"></canvas>

    <header class="astratego-tower__nav">
        <a class="astratego-tower__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Astratego">
            <span>A</span>
            <strong>ASTRATEGO</strong>
        </a>
        <nav aria-label="Astratego floors">
            <?php foreach ($floors as $floor) : ?>
                <a href="#astratego-floor-<?php echo esc_attr($floor['slug']); ?>"><?php echo esc_html($floor['title']); ?></a>
            <?php endforeach; ?>
        </nav>
        <a class="astratego-tower__contact" href="<?php echo esc_url($contact_url); ?>">דברו איתנו</a>
    </header>

    <section class="astratego-tower__intro">
        <p>ASTRATEGO HQ</p>
        <h1>בניין דיגיטלי שבו כל קומה היא מחלקה שעובדת בשביל העסק.</h1>
        <span>גלילה בין קומות, לחיצה על עמדות, ופוקוס חי על הבוט שאחראי למשימה.</span>
    </section>

    <div class="astratego-tower__shaft" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="astratego-elevator-transition" data-elevator-transition aria-hidden="true">
        <span class="astratego-elevator-transition__door astratego-elevator-transition__door--left"></span>
        <span class="astratego-elevator-transition__door astratego-elevator-transition__door--right"></span>
        <div class="astratego-elevator-transition__indicator">
            <span data-elevator-number>01</span>
            <strong data-elevator-title>Growth Ads</strong>
            <i></i>
        </div>
        <div class="astratego-elevator-transition__scan"></div>
    </div>

    <?php foreach ($floors as $floor_index => $floor) : ?>
        <section
            class="astratego-floor stratego-floor--<?php echo esc_attr($floor['tone']); ?>"
            id="astratego-floor-<?php echo esc_attr($floor['slug']); ?>"
            data-astratego-floor
            data-floor-index="<?php echo esc_attr((string) $floor_index); ?>"
        >
            <div class="astratego-floor__shell">
                <aside class="astratego-floor__copy">
                    <small><?php echo esc_html($floor['number']); ?></small>
                    <h2><?php echo esc_html($floor['title']); ?></h2>
                    <p><?php echo esc_html($floor['text']); ?></p>
                    <a href="<?php echo esc_url($portfolio_url); ?>">לפרטים נוספים</a>
                </aside>

                <?php $floor_background_url = COLT_EXPERIENCE_URL . 'assets/images/astratego-floors/' . $floor['background']; ?>
                <div
                    class="astratego-floor__workspace"
                    data-floor-workspace
                    style="--floor-bg: url('<?php echo esc_url($floor_background_url); ?>');"
                >
                    <div class="astratego-floor__ceiling" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </div>

                    <div class="astratego-floor__depth" data-floor-stage>
                        <div class="astratego-room" aria-hidden="true">
                            <span class="astratego-room__wall stratego-room__wall--left"></span>
                            <span class="astratego-room__wall stratego-room__wall--right"></span>
                            <span class="astratego-room__floor"></span>
                            <span class="astratego-room__ceiling"></span>
                            <span class="astratego-room__column stratego-room__column--left"></span>
                            <span class="astratego-room__column stratego-room__column--right"></span>
                            <span class="astratego-room__lamp stratego-room__lamp--one"></span>
                            <span class="astratego-room__lamp stratego-room__lamp--two"></span>
                            <span class="astratego-room__lamp stratego-room__lamp--three"></span>
                        </div>

                        <div class="astratego-floor__backwall" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>

                        <div class="astratego-room__headline">
                            <small><?php echo esc_html($floor['number']); ?></small>
                            <h3><?php echo esc_html($floor['title']); ?></h3>
                            <p><?php echo esc_html($floor['text']); ?></p>
                            <a href="<?php echo esc_url($portfolio_url); ?>">לפרטים נוספים</a>
                        </div>

                        <div class="astratego-room__elevator" aria-hidden="true">
                            <b><?php echo esc_html($floor['number']); ?></b>
                            <i></i>
                        </div>

                        <div class="astratego-floor__screen" data-floor-screen>
                            <div>
                                <small data-floor-station-label><?php echo esc_html($floor['stations'][0]['label']); ?></small>
                                <h3 data-floor-station-title><?php echo esc_html($floor['stations'][0]['title']); ?></h3>
                                <p data-floor-station-text><?php echo esc_html($floor['stations'][0]['text']); ?></p>
                                <div class="astratego-floor__screen-task">
                                    <strong data-floor-task-title><?php echo esc_html($floor['stations'][0]['stat']); ?></strong>
                                    <span data-floor-task-text><?php echo esc_html($floor['stations'][0]['task']); ?></span>
                                </div>
                            </div>
                            <div class="astratego-floor__screen-grid" aria-hidden="true">
                                <span></span><span></span><span></span><span></span>
                            </div>
                        </div>

                        <div class="astratego-floor__stations" aria-label="<?php echo esc_attr($floor['title']); ?>">
                            <?php foreach ($floor['stations'] as $station_index => $station) : ?>
                                <?php $station_layout = $station_layouts[$station_index] ?? $station_layouts[0]; ?>
                                <button
                                    class="astratego-station"
                                    type="button"
                                    data-astratego-station
                                    data-station-title="<?php echo esc_attr($station['title']); ?>"
                                    data-station-label="<?php echo esc_attr($station['label']); ?>"
                                    data-station-text="<?php echo esc_attr($station['text']); ?>"
                                    data-station-task="<?php echo esc_attr($station['task']); ?>"
                                    data-station-stat="<?php echo esc_attr($station['stat']); ?>"
                                    style="
                                        --station: <?php echo esc_attr((string) $station_index); ?>;
                                        --station-x: <?php echo esc_attr($station_layout['x']); ?>;
                                        --station-y: <?php echo esc_attr($station_layout['y']); ?>;
                                        --station-z: <?php echo esc_attr($station_layout['z']); ?>;
                                        --station-scale: <?php echo esc_attr($station_layout['scale']); ?>;
                                        --station-angle: <?php echo esc_attr($station_layout['angle']); ?>;
                                        --station-hover-turn: <?php echo esc_attr($station_layout['hover_turn']); ?>;
                                        --station-focus-turn: <?php echo esc_attr($station_layout['focus_turn']); ?>;
                                        --bot-gaze: <?php echo esc_attr($station_layout['bot_gaze']); ?>;
                                        --terminal-x: <?php echo esc_attr($station_layout['terminal_x']); ?>;
                                        --terminal-turn: <?php echo esc_attr($station_layout['terminal_turn']); ?>;
                                        --surface-turn: <?php echo esc_attr($station_layout['surface_turn']); ?>;
                                        --walk-x: <?php echo esc_attr($station_layout['walk_x']); ?>;
                                        --walk-y: <?php echo esc_attr($station_layout['walk_y']); ?>;
                                        --station-delay: <?php echo esc_attr($station_layout['delay']); ?>;
                                    "
                                >
                                    <span class="astratego-station__hit" aria-hidden="true"></span>
                                    <span class="astratego-station__surface" aria-hidden="true">
                                        <i></i><b></b><em></em>
                                    </span>
                                    <span class="astratego-bot" aria-hidden="true">
                                        <?php if ($bot_model_url) : ?>
                                            <model-viewer
                                                class="astratego-bot__model"
                                                src="<?php echo esc_url($bot_model_url); ?>"
                                                autoplay
                                                interaction-prompt="none"
                                                disable-zoom
                                                camera-orbit="0deg 78deg 5m"
                                                exposure="1.05"
                                                shadow-intensity=".38"
                                            ></model-viewer>
                                        <?php else : ?>
                                            <i></i><b></b><em></em>
                                        <?php endif; ?>
                                    </span>
                                    <span class="astratego-station__terminal" aria-hidden="true">
                                        <i></i><b></b>
                                    </span>
                                    <strong><?php echo esc_html($station['title']); ?></strong>
                                    <small><?php echo esc_html($station['label']); ?></small>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="astratego-floor__focus" data-floor-focus>
                            <span class="astratego-bot stratego-bot--hero" aria-hidden="true">
                                <?php if ($bot_model_url) : ?>
                                    <model-viewer
                                        class="astratego-bot__model"
                                        src="<?php echo esc_url($bot_model_url); ?>"
                                        autoplay
                                        interaction-prompt="none"
                                        disable-zoom
                                        camera-orbit="0deg 78deg 5m"
                                        exposure="1.05"
                                        shadow-intensity=".38"
                                    ></model-viewer>
                                <?php else : ?>
                                    <i></i><b></b><em></em>
                                <?php endif; ?>
                            </span>
                            <div>
                                <small>WORKSTATION FOCUS</small>
                                <strong data-floor-task-title><?php echo esc_html($floor['stations'][0]['stat']); ?></strong>
                                <p data-floor-task-text><?php echo esc_html($floor['stations'][0]['task']); ?></p>
                            </div>
                        </div>

                        <div class="astratego-floor__metric">
                            <strong><?php echo esc_html($floor['metric']); ?></strong>
                            <span><?php echo esc_html($floor['metric_label']); ?></span>
                        </div>

                        <div class="astratego-floor__decor" aria-hidden="true">
                            <span class="plant"></span>
                            <span class="desk"></span>
                            <span class="stairs"></span>
                            <span class="elevator"><?php echo esc_html($floor['number']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <footer class="astratego-tower__footer">
        <div>
            <p>ASTRATEGO METHOD</p>
            <h2>כל קומה יכולה להתחבר בהמשך לנתונים אמיתיים: ביקורות, ביצועים, מסכים, דוחות ותהליכים חיים.</h2>
        </div>
        <a href="<?php echo esc_url($contact_url); ?>">להתחיל פרויקט</a>
    </footer>
</section>
