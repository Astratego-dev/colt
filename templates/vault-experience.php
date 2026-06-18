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
$vault_content = Colt_Experience::special_page('vault');
$vault_features = $vault_content['features'] ?? [];
$vault_steps = $vault_content['steps'] ?? [];
$vault_ledger = $vault_content['ledger'] ?? [];
?>

<section
    class="colt-xp colt-vault-xp"
    dir="rtl"
    data-colt-xp
    data-vault-xp
    data-version="2.0.5"
    style="<?php echo esc_attr('--vault-entrance: url(' . esc_url($vault_entrance_url) . '); --vault-interior: url(' . esc_url($vault_interior_url) . ');'); ?>"
>
    <canvas class="colt-xp__canvas" data-colt-canvas aria-hidden="true"></canvas>
    <div class="colt-xp__noise" aria-hidden="true"></div>

    <nav class="colt-nav colt-vault-nav" aria-label="<?php echo esc_attr($vault_content['nav_label'] ?? 'COLT Vault'); ?>">
        <a class="colt-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="COLT">
            <img src="<?php echo esc_url($logo_url); ?>" alt="COLT" loading="eager">
        </a>
        <div class="colt-nav__links">
            <a href="#vault-entry"><?php echo esc_html($vault_content['nav_entry_label'] ?? 'הכספת'); ?></a>
            <a href="#vault-inside"><?php echo esc_html($vault_content['nav_inside_label'] ?? 'מה בפנים'); ?></a>
            <a href="#vault-protocol"><?php echo esc_html($vault_content['nav_protocol_label'] ?? 'התהליך'); ?></a>
            <a href="#vault-contact"><?php echo esc_html($vault_content['nav_contact_label'] ?? 'יצירת קשר'); ?></a>
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
                <p class="colt-kicker"><?php echo esc_html($vault_content['hero_kicker'] ?? ''); ?></p>
                <h1><?php echo esc_html($vault_content['hero_title'] ?? ''); ?></h1>
                <p><?php echo esc_html($vault_content['hero_text'] ?? ''); ?></p>
                <div class="colt-vault-hero__actions">
                    <a href="#vault-contact"><?php echo esc_html($vault_content['hero_primary'] ?? ''); ?></a>
                    <a href="#vault-inside"><?php echo esc_html($vault_content['hero_secondary'] ?? ''); ?></a>
                </div>
            </div>

            <div class="colt-vault-hero__ledger" data-vault-ledger aria-label="מאפייני כספת">
                <?php foreach ($vault_ledger as $item) : ?>
                    <span><b><?php echo esc_html($item['value'] ?? ''); ?></b><?php echo esc_html($item['label'] ?? ''); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="colt-vault-hero__scroll" aria-hidden="true">
                <span><?php echo esc_html($vault_content['hero_scroll_label'] ?? 'גלול לפתיחה'); ?></span>
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
                <p class="colt-kicker"><?php echo esc_html($vault_content['inside_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($vault_content['inside_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($vault_content['inside_text'] ?? ''); ?></p>
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
                <p class="colt-kicker"><?php echo esc_html($vault_content['protocol_kicker'] ?? ''); ?></p>
                <h2><?php echo esc_html($vault_content['protocol_title'] ?? ''); ?></h2>
                <p><?php echo esc_html($vault_content['protocol_text'] ?? ''); ?></p>
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
                    <p class="colt-kicker"><?php echo esc_html($vault_content['contact_kicker'] ?? ''); ?></p>
                    <h2><?php echo esc_html($vault_content['contact_title'] ?? ''); ?></h2>
                    <p><?php echo esc_html($vault_content['contact_text'] ?? ''); ?></p>
                </div>
            </div>

            <div class="colt-vault-contact__panel" data-vault-contact-panel>
                <a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($vault_content['contact_primary'] ?? ''); ?></a>
                <a href="<?php echo esc_url($whatsapp_url); ?>"><?php echo esc_html($vault_content['contact_secondary'] ?? ''); ?></a>
                <a href="<?php echo esc_url($shop_url); ?>"><?php echo esc_html($vault_content['contact_third'] ?? ''); ?></a>
            </div>
        </div>
    </section>
</section>
