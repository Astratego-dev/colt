<?php
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = Colt_Experience::logo_url();
$mark_url = Colt_Experience::asset_url('assets/img/colt-character.png');
$vault_entrance_url = Colt_Experience::asset_url('assets/vault/vault-entrance.jpg');
$vault_interior_url = Colt_Experience::asset_url('assets/vault/vault-interior.jpg');
$contact_url = !empty($atts['contact_url']) ? (string) $atts['contact_url'] : home_url('/contact/');
$whatsapp_url = !empty($atts['whatsapp']) ? (string) $atts['whatsapp'] : $contact_url;
$shop_url = !empty($atts['shop_url']) ? (string) $atts['shop_url'] : home_url('/shop/');
$vault_features = [
    ['title' => 'תנאי אחסון יציבים', 'text' => 'סביבה מבוקרת לפריטים רגישים, עם דגש על יציבות, ניקיון והפרדה נכונה בין פריטים.', 'meta' => 'Climate'],
    ['title' => 'תיעוד מצב הכניסה', 'text' => 'כל פריט נכנס עם תיעוד בסיסי: תמונות, מצב, הערות ושייכות, כדי לשמור על שקיפות.', 'meta' => 'Record'],
    ['title' => 'גישה לפי בקשה', 'text' => 'אפשר לבקש הוצאה, צילום, מכירה או העברה לדירוג בלי להפוך את האוסף לתיק לוגיסטי.', 'meta' => 'Access'],
    ['title' => 'שכבת ערך לאוסף', 'text' => 'שירות שמיועד לפריטים שחשוב לשמור עליהם כמו נכס, לא רק כמו מוצר על מדף.', 'meta' => 'Value'],
];
$vault_steps = [
    ['step' => '01', 'title' => 'קליטה ותיעוד', 'text' => 'בודקים יחד מה נכנס לכספת, מצלמים, מסמנים ומגדירים מטרת שמירה או מכירה עתידית.'],
    ['step' => '02', 'title' => 'אריזה והפרדה', 'text' => 'כל פריט מקבל שכבת הגנה מתאימה, הפרדה פיזית וסידור שמונע שחיקה מיותרת.'],
    ['step' => '03', 'title' => 'שמירה מבוקרת', 'text' => 'הפריטים נשמרים בסביבת תצוגה ואחסון נקייה, יציבה ומותאמת לפריטי אספנות יקרי ערך.'],
    ['step' => '04', 'title' => 'פעולה לפי צורך', 'text' => 'כשתרצה, אפשר להוציא, לשלוח לדירוג, להציע למכירה או להעביר לקונה בצורה מסודרת.'],
];
?>

<section
    class="colt-xp colt-vault-xp"
    dir="rtl"
    data-colt-xp
    data-vault-xp
    data-version="1.8.0"
    style="<?php echo esc_attr('--vault-entrance: url(' . esc_url($vault_entrance_url) . '); --vault-interior: url(' . esc_url($vault_interior_url) . ');'); ?>"
>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-vault-nav" aria-label="COLT Vault">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#vault-entry">הכספת</a>
            <a href="#vault-inside">מה בפנים</a>
            <a href="#vault-protocol">התהליך</a>
            <a href="#vault-contact">יצירת קשר</a>
        </div>
    </nav>

    <section class="colt-vault-hero" id="vault-entry" data-vault-hero>
        <div class="colt-vault-hero__pin">
            <div class="colt-vault-hero__backdrop" data-vault-hero-bg aria-hidden="true"></div>
            <div class="colt-vault-hero__door" data-vault-door aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="colt-vault-hero__scan" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>

            <div class="colt-vault-hero__copy" data-vault-hero-copy>
                <p class="colt-kicker">COLT THE VAULT</p>
                <h1>כספת אספנים לפריטים שאתה לא רוצה להשאיר ליד המקרה.</h1>
                <p>שירות שמירה לפריטי אספנות יקרי ערך: קלפים מדורגים, סינגלים נדירים, פריטי חתימה ואובייקטים שהערך שלהם תלוי גם באופן שבו שומרים עליהם.</p>
                <div class="colt-vault-hero__actions">
                    <a href="#vault-contact">בדיקת התאמה</a>
                    <a href="#vault-inside">לראות מה בפנים</a>
                </div>
            </div>

            <div class="colt-vault-hero__ledger" data-vault-ledger aria-label="מאפייני כספת">
                <span><b>01</b>תיעוד כניסה</span>
                <span><b>02</b>אחסון מבוקר</span>
                <span><b>03</b>גישה לפי בקשה</span>
                <span><b>04</b>שכבת ערך</span>
            </div>

            <div class="colt-vault-hero__scroll" aria-hidden="true">
                <span>גלול לפתיחה</span>
                <b></b>
            </div>
        </div>
    </section>

    <section class="colt-vault-inside" id="vault-inside" data-vault-inside>
        <div class="colt-vault-inside__pin">
            <div class="colt-vault-inside__backdrop" data-vault-inside-bg aria-hidden="true"></div>
            <div class="colt-vault-inside__glass" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="colt-vault-inside__copy" data-vault-inside-copy>
                <p class="colt-kicker">INSIDE THE VAULT</p>
                <h2>לא מחסן. חדר שמירה שנבנה סביב ערך.</h2>
                <p>המטרה היא לתת לפריטים יקרים תחושה של בית קבוע: מסודר, מתועד, נשלט, ונגיש לפעולה כשצריך למכור, לדרג או להעביר הלאה.</p>
            </div>

            <div class="colt-vault-inside__features" aria-label="מה הכספת כוללת">
                <?php foreach ($vault_features as $index => $feature) : ?>
                    <article class="colt-vault-feature" data-vault-feature style="--i: <?php echo esc_attr((string) $index); ?>">
                        <small><?php echo esc_html($feature['meta']); ?></small>
                        <strong><?php echo esc_html($feature['title']); ?></strong>
                        <p><?php echo esc_html($feature['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="colt-vault-inside__slabs" aria-hidden="true">
                <span><i></i></span>
                <span><i></i></span>
                <span><i></i></span>
                <span><i></i></span>
                <span><i></i></span>
            </div>
        </div>
    </section>

    <section class="colt-vault-protocol" id="vault-protocol" data-vault-protocol>
        <div class="colt-vault-protocol__pin">
            <div class="colt-vault-protocol__rings" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="colt-vault-protocol__copy" data-vault-protocol-copy>
                <p class="colt-kicker">THE PROTOCOL</p>
                <h2>כל פריט נכנס למסלול ברור.</h2>
                <p>מהרגע שהפריט מגיע אלינו ועד הרגע שאתה מבקש לבצע פעולה, הכל בנוי כדי להוריד חיכוך ולשמור על השליטה אצלך.</p>
            </div>
            <div class="colt-vault-protocol__steps" aria-label="תהליך הכנסה לכספת">
                <?php foreach ($vault_steps as $index => $item) : ?>
                    <article class="colt-vault-step" data-vault-step style="--i: <?php echo esc_attr((string) $index); ?>">
                        <small><?php echo esc_html($item['step']); ?></small>
                        <strong><?php echo esc_html($item['title']); ?></strong>
                        <p><?php echo esc_html($item['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="colt-vault-contact" id="vault-contact" data-vault-contact>
        <div class="colt-vault-contact__shell">
            <div class="colt-vault-contact__brand" data-vault-contact-brand>
                <img src="<?php echo esc_url($mark_url); ?>" alt="" loading="lazy">
                <div>
                    <p class="colt-kicker">OPEN A VAULT CASE</p>
                    <h2>רוצה לבדוק אם האוסף שלך מתאים לכספת?</h2>
                    <p>שלח לנו מה יש לך, מה המטרה ומה רמת הרגישות של הפריטים. נחזור עם הצעה מסודרת לשמירה, תיעוד או פעולה עתידית.</p>
                </div>
            </div>

            <div class="colt-vault-contact__panel" data-vault-contact-panel>
                <a href="<?php echo esc_url($contact_url); ?>">פתיחת פנייה</a>
                <a href="<?php echo esc_url($whatsapp_url); ?>">שיחה בוואטסאפ</a>
                <a href="<?php echo esc_url($shop_url); ?>">לראות פריטים בחנות</a>
            </div>
        </div>
    </section>
</section>
