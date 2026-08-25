<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Colt_Ebay_Manager
{
    private const SETTINGS_OPTION = 'colt_ebay_settings';
    private const TOKEN_OPTION = 'colt_ebay_tokens';
    private const POLICIES_OPTION = 'colt_ebay_policies';
    private const ORDERS_OPTION = 'colt_ebay_orders';
    private const LOGS_OPTION = 'colt_ebay_logs';
    private const NOTIFICATIONS_OPTION = 'colt_ebay_notifications';
    private const QUEUE_OPTION = 'colt_ebay_queue';
    private const CRON_HOOK = 'colt_ebay_sync_price_quantity';
    private const QUEUE_CRON_HOOK = 'colt_ebay_process_queue';

    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_menu'], 20);
            add_action('admin_post_colt_ebay_save_settings', [$this, 'handle_save_settings']);
            add_action('admin_post_colt_ebay_connect', [$this, 'handle_connect']);
            add_action('admin_post_colt_ebay_oauth_callback', [$this, 'handle_oauth_callback']);
            add_action('admin_post_colt_ebay_sync_policies', [$this, 'handle_sync_policies']);
            add_action('admin_post_colt_ebay_export_products', [$this, 'handle_export_products']);
            add_action('admin_post_colt_ebay_fetch_orders', [$this, 'handle_fetch_orders']);
            add_action('admin_post_colt_ebay_update_fulfillment', [$this, 'handle_update_fulfillment']);
            add_action('admin_post_colt_ebay_clear_logs', [$this, 'handle_clear_logs']);
            add_action('admin_post_colt_ebay_process_queue', [$this, 'handle_process_queue']);
            add_action('admin_post_colt_ebay_clear_queue', [$this, 'handle_clear_queue']);
        }

        add_action('init', [$this, 'maybe_schedule_sync']);
        add_action(self::CRON_HOOK, [$this, 'cron_sync_price_quantity']);
        add_action(self::QUEUE_CRON_HOOK, [$this, 'process_queue']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_admin_menu()
    {
        add_submenu_page(
            'colt-experience',
            'COLT eBay',
            'eBay',
            'manage_options',
            'colt-ebay',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page()
    {
        if (!$this->can_manage()) {
            return;
        }

        $settings = $this->settings();
        $tokens = $this->tokens();
        $policies = $this->policies();
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'listings';
        if (!in_array($tab, ['listings', 'settings', 'orders', 'notifications', 'queue', 'logs'], true)) {
            $tab = 'listings';
        }
        ?>
        <div class="wrap colt-ebay" dir="rtl">
            <style>
                .colt-ebay { max-width: 1480px; color: #111827; }
                .colt-ebay h1 { font-size: 30px; font-weight: 900; margin-bottom: 6px; }
                .colt-ebay h2 { margin: 0 0 12px; font-size: 19px; font-weight: 900; }
                .colt-ebay h3 { margin: 0 0 10px; font-size: 15px; font-weight: 900; }
                .colt-ebay p { font-size: 14px; }
                .colt-ebay__hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin: 18px 0; padding: 20px; border: 1px solid #dcdcde; border-radius: 14px; background: linear-gradient(135deg, #0d1119, #182534 58%, #24170b); color: #fff; box-shadow: 0 20px 60px rgba(16, 21, 28, .14); }
                .colt-ebay__hero p { max-width: 780px; margin: 4px 0 0; color: rgba(255,255,255,.72); }
                .colt-ebay__status { display: grid; gap: 8px; justify-items: end; }
                .colt-ebay__pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f5d37b; font-weight: 900; white-space: nowrap; }
                .colt-ebay__tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0; }
                .colt-ebay__tabs a { padding: 9px 13px; border: 1px solid #dcdcde; border-radius: 999px; background: #fff; text-decoration: none; font-weight: 900; }
                .colt-ebay__tabs a.is-active { background: #111827; color: #fff; border-color: #111827; }
                .colt-ebay__panel { margin: 16px 0; padding: 16px; border: 1px solid #dcdcde; border-radius: 14px; background: #fff; }
                .colt-ebay__grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
                .colt-ebay__grid--two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .colt-ebay label { display: grid; gap: 6px; font-weight: 800; color: #1d2327; }
                .colt-ebay input[type="text"], .colt-ebay input[type="password"], .colt-ebay input[type="number"], .colt-ebay select, .colt-ebay textarea { width: 100%; max-width: none; min-height: 36px; }
                .colt-ebay textarea { min-height: 120px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; direction: ltr; }
                .colt-ebay__wide { grid-column: 1 / -1; }
                .colt-ebay__note { margin: 0; padding: 10px 12px; border-radius: 10px; background: #fff8e5; color: #6f4e00; font-weight: 700; }
                .colt-ebay__table-wrap { overflow-x: auto; border: 1px solid #dcdcde; border-radius: 12px; background: #fff; }
                .colt-ebay table { margin: 0; border: 0; }
                .colt-ebay th { font-weight: 900; }
                .colt-ebay td, .colt-ebay th { vertical-align: middle; }
                .colt-ebay__product { display: flex; gap: 10px; align-items: center; min-width: 290px; }
                .colt-ebay__thumb { width: 54px; height: 54px; border-radius: 8px; object-fit: cover; background: #f0f0f1; border: 1px solid #dcdcde; }
                .colt-ebay__muted { color: #646970; font-size: 12px; }
                .colt-ebay__toolbar { display: grid; grid-template-columns: minmax(180px, 1fr) repeat(3, minmax(120px, .6fr)) auto; gap: 10px; align-items: end; }
                .colt-ebay__actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-top: 14px; }
                .colt-ebay__badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px; background: #f0f0f1; font-size: 12px; font-weight: 900; }
                .colt-ebay__badge--ok { background: #ddf7e7; color: #006b38; }
                .colt-ebay__badge--warn { background: #fff3cd; color: #7a4c00; }
                .colt-ebay__badge--bad { background: #ffe3e3; color: #9b1c1c; }
                .colt-ebay__shortcode { display: inline-flex; direction: ltr; max-width: 100%; padding: 7px 10px; border-radius: 8px; background: #10151c; color: #f5d37b; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; white-space: nowrap; overflow-x: auto; }
                .colt-ebay__pagination { margin-top: 14px; text-align: center; }
                .colt-ebay__pagination .page-numbers { display: inline-block; margin: 0 2px; padding: 6px 10px; border-radius: 8px; background: #fff; border: 1px solid #dcdcde; text-decoration: none; }
                .colt-ebay__pagination .current { background: #111827; color: #fff; border-color: #111827; }
                @media (max-width: 1100px) { .colt-ebay__grid, .colt-ebay__grid--two, .colt-ebay__toolbar { grid-template-columns: 1fr; } }
                @media (max-width: 782px) { .colt-ebay__hero, .colt-ebay__actions { display: grid; justify-items: start; } .colt-ebay__status { justify-items: start; } }
            </style>

            <h1>COLT eBay</h1>
            <p>ניהול חיבור לאיביי, העלאת מוצרים, פוליסות עסקיות, הזמנות ולוגים מתוך התוסף.</p>

            <?php $this->render_notices(); ?>

            <div class="colt-ebay__hero">
                <div>
                    <h2>eBay Connector</h2>
                    <p>בחר מוצרים מהחנות, צור Inventory Item, Offer ופרסום Live דרך Sell APIs, ושמור לוגים מפורטים לכל פעולה כדי לפתור שגיאות מהר.</p>
                </div>
                <div class="colt-ebay__status">
                    <?php echo wp_kses_post($this->token_badge($tokens)); ?>
                    <span class="colt-ebay__pill"><?php echo esc_html($settings['environment'] === 'sandbox' ? 'Sandbox' : 'Production'); ?> · <?php echo esc_html($settings['marketplace_id']); ?></span>
                </div>
            </div>

            <nav class="colt-ebay__tabs">
                <?php foreach ($this->tabs() as $tab_key => $label) : ?>
                    <a class="<?php echo $tab === $tab_key ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['page' => 'colt-ebay', 'tab' => $tab_key], admin_url('admin.php'))); ?>"><?php echo esc_html($label); ?></a>
                <?php endforeach; ?>
            </nav>

            <?php
            if ($tab === 'settings') {
                $this->render_settings_tab($settings, $tokens, $policies);
            } elseif ($tab === 'orders') {
                $this->render_orders_tab();
            } elseif ($tab === 'notifications') {
                $this->render_notifications_tab($settings);
            } elseif ($tab === 'queue') {
                $this->render_queue_tab();
            } elseif ($tab === 'logs') {
                $this->render_logs_tab();
            } else {
                $this->render_listings_tab($settings);
            }
            ?>
        </div>
        <script>
        document.addEventListener('change', function (event) {
            if (!event.target.matches('[data-colt-ebay-check-all]')) {
                return;
            }

            document.querySelectorAll('.colt-ebay input[name="product_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = event.target.checked;
            });
        });
        </script>
        <?php
    }

    public function handle_save_settings()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to manage eBay settings.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_save_settings');

        $current = $this->settings();
        $settings = [
            'environment' => isset($_POST['environment']) && $_POST['environment'] === 'sandbox' ? 'sandbox' : 'production',
            'app_id' => isset($_POST['app_id']) ? sanitize_text_field(wp_unslash($_POST['app_id'])) : '',
            'dev_id' => isset($_POST['dev_id']) ? sanitize_text_field(wp_unslash($_POST['dev_id'])) : '',
            'cert_id' => isset($_POST['cert_id']) && (string) wp_unslash($_POST['cert_id']) !== '' ? sanitize_text_field(wp_unslash($_POST['cert_id'])) : (string) ($current['cert_id'] ?? ''),
            'redirect_uri' => isset($_POST['redirect_uri']) ? sanitize_text_field(wp_unslash($_POST['redirect_uri'])) : '',
            'scopes' => isset($_POST['scopes']) ? sanitize_textarea_field(wp_unslash($_POST['scopes'])) : '',
            'marketplace_id' => isset($_POST['marketplace_id']) ? strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) wp_unslash($_POST['marketplace_id']))) : 'EBAY_US',
            'currency' => isset($_POST['currency']) ? strtoupper(sanitize_key(wp_unslash($_POST['currency']))) : 'USD',
            'exchange_rate' => $this->decimal_from_post('exchange_rate', 1),
            'price_markup_percent' => $this->decimal_from_post('price_markup_percent', 0),
            'price_markup_fixed' => $this->decimal_from_post('price_markup_fixed', 0),
            'merchant_location_key' => isset($_POST['merchant_location_key']) ? sanitize_text_field(wp_unslash($_POST['merchant_location_key'])) : '',
            'category_id' => isset($_POST['category_id']) ? sanitize_text_field(wp_unslash($_POST['category_id'])) : '',
            'condition' => isset($_POST['condition']) ? sanitize_text_field(wp_unslash($_POST['condition'])) : 'NEW',
            'payment_policy_id' => isset($_POST['payment_policy_id']) ? sanitize_text_field(wp_unslash($_POST['payment_policy_id'])) : '',
            'fulfillment_policy_id' => isset($_POST['fulfillment_policy_id']) ? sanitize_text_field(wp_unslash($_POST['fulfillment_policy_id'])) : '',
            'return_policy_id' => isset($_POST['return_policy_id']) ? sanitize_text_field(wp_unslash($_POST['return_policy_id'])) : '',
            'description_template' => isset($_POST['description_template']) ? wp_kses_post(wp_unslash($_POST['description_template'])) : '',
            'verification_token' => isset($_POST['verification_token']) ? sanitize_text_field(wp_unslash($_POST['verification_token'])) : '',
            'auto_sync_enabled' => !empty($_POST['auto_sync_enabled']) ? '1' : '0',
        ];

        update_option(self::SETTINGS_OPTION, array_merge($this->default_settings(), $settings), false);
        $this->maybe_schedule_sync();
        $this->redirect(['tab' => 'settings', 'saved' => '1']);
    }

    public function handle_connect()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to connect eBay.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_connect');

        $settings = $this->settings();
        if ($settings['app_id'] === '' || $settings['redirect_uri'] === '') {
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'missing_credentials']);
        }

        $state = get_current_user_id() . '|' . wp_create_nonce('colt_ebay_oauth_' . get_current_user_id());
        $url = add_query_arg([
            'client_id' => $settings['app_id'],
            'redirect_uri' => $settings['redirect_uri'],
            'response_type' => 'code',
            'scope' => $settings['scopes'],
            'state' => $state,
        ], $this->auth_url());

        wp_redirect($url);
        exit;
    }

    public function handle_oauth_callback()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to connect eBay.', 'colt-experience'));
        }

        $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
        [$user_id, $nonce] = array_pad(explode('|', $state, 2), 2, '');
        if ((int) $user_id !== get_current_user_id() || !wp_verify_nonce($nonce, 'colt_ebay_oauth_' . get_current_user_id())) {
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'oauth_state']);
        }

        if (!empty($_GET['error'])) {
            $this->add_log('error', 'eBay OAuth returned an error.', ['error' => sanitize_text_field(wp_unslash($_GET['error']))]);
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'oauth']);
        }

        $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
        if ($code === '') {
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'oauth_code']);
        }

        $result = $this->exchange_authorization_code($code);
        if (is_wp_error($result)) {
            $this->add_log('error', 'Could not exchange eBay authorization code.', ['error' => $result->get_error_message()]);
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'oauth_exchange']);
        }

        $this->redirect(['tab' => 'settings', 'oauth_connected' => '1']);
    }

    public function handle_sync_policies()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to sync eBay policies.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_sync_policies');

        $result = $this->sync_business_policies();
        if (is_wp_error($result)) {
            $this->add_log('error', 'Could not sync eBay business policies.', ['error' => $result->get_error_message()]);
            $this->redirect(['tab' => 'settings', 'ebay_error' => 'policies']);
        }

        $this->redirect(['tab' => 'settings', 'policies_synced' => '1']);
    }

    public function handle_export_products()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to export products.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_export_products');

        $product_ids = $this->posted_ids('product_ids');
        if (!$product_ids) {
            $this->redirect(['tab' => 'listings', 'ebay_error' => 'no_products']);
        }

        $mode = isset($_POST['export_mode']) && $_POST['export_mode'] === 'publish' ? 'publish' : 'draft';
        $category_id = isset($_POST['category_id']) ? sanitize_text_field(wp_unslash($_POST['category_id'])) : '';
        $queued = $this->enqueue_export_products($product_ids, $mode, [
            'category_id' => $category_id,
        ]);

        $queue_result = $this->process_queue(3);

        $this->redirect([
            'tab' => 'listings',
            'ebay_queued' => $queued,
            'ebay_exported' => $queue_result['completed'],
            'ebay_failed' => $queue_result['failed'],
        ]);
    }

    public function handle_fetch_orders()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to fetch eBay orders.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_fetch_orders');

        $response = $this->api_request('GET', '/sell/fulfillment/v1/order?limit=50');
        if (is_wp_error($response)) {
            $this->add_log('error', 'Could not fetch eBay orders.', ['error' => $response->get_error_message()]);
            $this->redirect(['tab' => 'orders', 'ebay_error' => 'orders']);
        }

        update_option(self::ORDERS_OPTION, [
            'fetched_at' => time(),
            'orders' => is_array($response['orders'] ?? null) ? $response['orders'] : [],
            'raw' => $response,
        ], false);

        $this->redirect(['tab' => 'orders', 'orders_synced' => '1']);
    }

    public function handle_update_fulfillment()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to update eBay fulfillment.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_update_fulfillment');

        $order_id = isset($_POST['order_id']) ? sanitize_text_field(wp_unslash($_POST['order_id'])) : '';
        $tracking = isset($_POST['tracking_number']) ? sanitize_text_field(wp_unslash($_POST['tracking_number'])) : '';
        $carrier = isset($_POST['carrier']) ? sanitize_text_field(wp_unslash($_POST['carrier'])) : 'Israel Post';
        $line_items = $this->posted_line_items();

        if ($order_id === '' || $tracking === '' || !$line_items) {
            $this->redirect(['tab' => 'orders', 'ebay_error' => 'fulfillment_fields']);
        }

        $payload = [
            'lineItems' => $line_items,
            'shippedDate' => gmdate('Y-m-d\TH:i:s\Z'),
            'shippingCarrierCode' => $carrier,
            'trackingNumber' => $tracking,
        ];

        $result = $this->api_request('POST', '/sell/fulfillment/v1/order/' . rawurlencode($order_id) . '/shipping_fulfillment', $payload);
        if (is_wp_error($result)) {
            $this->add_log('error', 'Could not update eBay shipping fulfillment.', [
                'order_id' => $order_id,
                'error' => $result->get_error_message(),
            ]);
            $this->redirect(['tab' => 'orders', 'ebay_error' => 'fulfillment']);
        }

        $this->add_log('info', 'eBay order fulfillment updated.', [
            'order_id' => $order_id,
            'carrier' => $carrier,
            'tracking' => $tracking,
        ]);

        $this->redirect(['tab' => 'orders', 'fulfillment_updated' => '1']);
    }

    public function handle_clear_logs()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to clear logs.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_clear_logs');
        update_option(self::LOGS_OPTION, [], false);
        $this->redirect(['tab' => 'logs', 'logs_cleared' => '1']);
    }

    public function handle_process_queue()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to process eBay queue.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_process_queue');

        $result = $this->process_queue(10);
        $this->redirect([
            'tab' => 'queue',
            'queue_processed' => '1',
            'queue_done' => $result['completed'],
            'queue_failed' => $result['failed'],
        ]);
    }

    public function handle_clear_queue()
    {
        if (!$this->can_manage()) {
            wp_die(esc_html__('You do not have permission to clear eBay queue.', 'colt-experience'));
        }

        check_admin_referer('colt_ebay_clear_queue');

        $queue = array_values(array_filter($this->queue(), static function ($task) {
            return is_array($task) && !in_array((string) ($task['status'] ?? ''), ['complete', 'failed'], true);
        }));

        update_option(self::QUEUE_OPTION, $queue, false);
        $this->redirect(['tab' => 'queue', 'queue_cleared' => '1']);
    }

    public function maybe_schedule_sync()
    {
        $settings = $this->settings();
        $scheduled = wp_next_scheduled(self::CRON_HOOK);

        if ($settings['auto_sync_enabled'] === '1' && !$scheduled) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', self::CRON_HOOK);
            return;
        }

        if ($settings['auto_sync_enabled'] !== '1' && $scheduled) {
            wp_unschedule_event($scheduled, self::CRON_HOOK);
        }
    }

    public function process_queue($limit = 5)
    {
        $limit = max(1, (int) $limit);
        $queue = $this->queue();
        $processed = 0;
        $completed = 0;
        $failed = 0;

        foreach ($queue as $index => $task) {
            if ($processed >= $limit) {
                break;
            }

            if (!is_array($task) || (string) ($task['status'] ?? 'pending') !== 'pending') {
                continue;
            }

            $processed++;
            $task['attempts'] = max(0, (int) ($task['attempts'] ?? 0)) + 1;
            $task['started_at'] = time();
            $task['status'] = 'running';
            $queue[$index] = $task;
            update_option(self::QUEUE_OPTION, $queue, false);

            $result = $this->process_queue_task($task);
            if (is_wp_error($result)) {
                $task['status'] = $task['attempts'] >= 3 ? 'failed' : 'pending';
                $task['last_error'] = $result->get_error_message();
                $task['updated_at'] = time();
                $queue[$index] = $task;
                $failed++;

                $this->add_log('error', 'eBay queue task failed.', [
                    'task_id' => (string) ($task['id'] ?? ''),
                    'type' => (string) ($task['type'] ?? ''),
                    'attempts' => (int) $task['attempts'],
                    'error' => $result->get_error_message(),
                ]);
                continue;
            }

            $task['status'] = 'complete';
            $task['completed_at'] = time();
            $task['last_error'] = '';
            $queue[$index] = $task;
            $completed++;
        }

        $queue = $this->trim_queue($queue);
        update_option(self::QUEUE_OPTION, $queue, false);

        if ($this->queue_has_pending($queue)) {
            $this->schedule_queue_processing();
        }

        if ($processed > 0) {
            $this->add_log('info', 'eBay queue processed.', [
                'processed' => $processed,
                'completed' => $completed,
                'failed' => $failed,
                'pending' => $this->queue_count_by_status($queue, 'pending'),
            ]);
        }

        return [
            'processed' => $processed,
            'completed' => $completed,
            'failed' => $failed,
        ];
    }

    public function cron_sync_price_quantity()
    {
        $settings = $this->settings();
        if ($settings['auto_sync_enabled'] !== '1') {
            return;
        }

        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 25,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_colt_ebay_offer_id',
                    'compare' => 'EXISTS',
                ],
            ],
        ]);

        $requests = [];
        foreach ($query->posts as $product_id) {
            $product = function_exists('wc_get_product') ? wc_get_product((int) $product_id) : null;
            if (!$product) {
                continue;
            }

            $sku = (string) get_post_meta((int) $product_id, '_colt_ebay_sku', true);
            if ($sku === '') {
                $sku = $this->product_sku($product);
            }

            $requests[] = [
                'sku' => $sku,
                'price' => [
                    'value' => $this->ebay_price($product, $settings),
                    'currency' => $settings['currency'],
                ],
                'shipToLocationAvailability' => [
                    'quantity' => $this->product_quantity($product),
                ],
            ];
        }

        if (!$requests) {
            return;
        }

        $result = $this->api_request('POST', '/sell/inventory/v1/bulk_update_price_quantity', [
            'requests' => $requests,
        ]);

        if (is_wp_error($result)) {
            $this->add_log('error', 'eBay bulk price/quantity sync failed.', ['error' => $result->get_error_message()]);
            return;
        }

        $this->add_log('info', 'eBay bulk price/quantity sync completed.', ['count' => count($requests)]);
    }

    public function register_rest_routes()
    {
        register_rest_route('colt-experience/v1', '/ebay-notifications', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'handle_notification_challenge'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'handle_notification_post'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    public function handle_notification_challenge(WP_REST_Request $request)
    {
        $settings = $this->settings();
        $challenge_code = (string) $request->get_param('challenge_code');
        if ($challenge_code === '' || $settings['verification_token'] === '') {
            return new WP_Error('missing_challenge', 'Missing challenge code or verification token.', ['status' => 400]);
        }

        $challenge_response = hash('sha256', $challenge_code . $settings['verification_token'] . $this->notification_endpoint_url());
        return rest_ensure_response(['challengeResponse' => $challenge_response]);
    }

    public function handle_notification_post(WP_REST_Request $request)
    {
        $notifications = get_option(self::NOTIFICATIONS_OPTION, []);
        $notifications = is_array($notifications) ? $notifications : [];
        array_unshift($notifications, [
            'time' => time(),
            'signature' => (string) $request->get_header('x-ebay-signature'),
            'topic' => (string) $request->get_header('x-ebay-notification-topic'),
            'payload' => $request->get_json_params() ?: json_decode($request->get_body(), true),
        ]);

        update_option(self::NOTIFICATIONS_OPTION, array_slice($notifications, 0, 50), false);
        $this->add_log('info', 'eBay notification received.', [
            'topic' => (string) $request->get_header('x-ebay-notification-topic'),
            'has_signature' => $request->get_header('x-ebay-signature') ? 'yes' : 'no',
        ]);

        return rest_ensure_response(['ok' => true]);
    }

    private function render_settings_tab(array $settings, array $tokens, array $policies)
    {
        $connect_url = wp_nonce_url(admin_url('admin-post.php?action=colt_ebay_connect'), 'colt_ebay_connect');
        $policy_url = wp_nonce_url(admin_url('admin-post.php?action=colt_ebay_sync_policies'), 'colt_ebay_sync_policies');
        ?>
        <section class="colt-ebay__panel">
            <h2>הגדרות ואימות</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('colt_ebay_save_settings'); ?>
                <input type="hidden" name="action" value="colt_ebay_save_settings">
                <div class="colt-ebay__grid">
                    <label>
                        <span>סביבה</span>
                        <select name="environment">
                            <option value="production" <?php selected($settings['environment'], 'production'); ?>>Production</option>
                            <option value="sandbox" <?php selected($settings['environment'], 'sandbox'); ?>>Sandbox</option>
                        </select>
                    </label>
                    <label>
                        <span>Marketplace</span>
                        <input type="text" name="marketplace_id" value="<?php echo esc_attr($settings['marketplace_id']); ?>" placeholder="EBAY_US">
                    </label>
                    <label>
                        <span>Currency</span>
                        <input type="text" name="currency" value="<?php echo esc_attr($settings['currency']); ?>" placeholder="USD">
                    </label>
                    <label>
                        <span>App ID / Client ID</span>
                        <input type="text" name="app_id" value="<?php echo esc_attr($settings['app_id']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>Dev ID</span>
                        <input type="text" name="dev_id" value="<?php echo esc_attr($settings['dev_id']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>Cert ID / Client Secret</span>
                        <input type="password" name="cert_id" value="" placeholder="<?php echo esc_attr($settings['cert_id'] !== '' ? 'שמור קיים' : 'חובה להזין'); ?>" dir="ltr">
                    </label>
                    <label class="colt-ebay__wide">
                        <span>Redirect URI / RuName</span>
                        <input type="text" name="redirect_uri" value="<?php echo esc_attr($settings['redirect_uri']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>Exchange Rate</span>
                        <input type="number" name="exchange_rate" value="<?php echo esc_attr($settings['exchange_rate']); ?>" min="0" step="0.0001">
                    </label>
                    <label>
                        <span>תוספת אחוז למחיר</span>
                        <input type="number" name="price_markup_percent" value="<?php echo esc_attr($settings['price_markup_percent']); ?>" min="0" step="0.01">
                    </label>
                    <label>
                        <span>תוספת קבועה למחיר</span>
                        <input type="number" name="price_markup_fixed" value="<?php echo esc_attr($settings['price_markup_fixed']); ?>" min="0" step="0.01">
                    </label>
                    <label>
                        <span>Merchant Location Key</span>
                        <input type="text" name="merchant_location_key" value="<?php echo esc_attr($settings['merchant_location_key']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>eBay Category ID ברירת מחדל</span>
                        <input type="text" name="category_id" value="<?php echo esc_attr($settings['category_id']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>Condition</span>
                        <input type="text" name="condition" value="<?php echo esc_attr($settings['condition']); ?>" placeholder="NEW" dir="ltr">
                    </label>
                    <?php $this->render_policy_select('payment_policy_id', 'Payment Policy', $settings['payment_policy_id'], $policies['paymentPolicies'] ?? []); ?>
                    <?php $this->render_policy_select('fulfillment_policy_id', 'Shipping / Fulfillment Policy', $settings['fulfillment_policy_id'], $policies['fulfillmentPolicies'] ?? []); ?>
                    <?php $this->render_policy_select('return_policy_id', 'Return Policy', $settings['return_policy_id'], $policies['returnPolicies'] ?? []); ?>
                    <label class="colt-ebay__wide">
                        <span>OAuth Scopes</span>
                        <textarea name="scopes"><?php echo esc_textarea($settings['scopes']); ?></textarea>
                    </label>
                    <label class="colt-ebay__wide">
                        <span>תבנית תיאור ל־eBay</span>
                        <textarea name="description_template"><?php echo esc_textarea($settings['description_template']); ?></textarea>
                    </label>
                    <label>
                        <span>Webhook Verification Token</span>
                        <input type="text" name="verification_token" value="<?php echo esc_attr($settings['verification_token']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>סנכרון רקע</span>
                        <span><input type="checkbox" name="auto_sync_enabled" value="1" <?php checked($settings['auto_sync_enabled'], '1'); ?>> עדכון מחיר ומלאי אוטומטי פעם בשעה</span>
                    </label>
                </div>
                <p class="colt-ebay__note">Callback להגדרת Accept URL באיביי: <code dir="ltr"><?php echo esc_html(admin_url('admin-post.php?action=colt_ebay_oauth_callback')); ?></code></p>
                <p class="colt-ebay__note">Webhook endpoint: <code dir="ltr"><?php echo esc_html($this->notification_endpoint_url()); ?></code></p>
                <?php submit_button('שמירת הגדרות eBay'); ?>
            </form>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($connect_url); ?>">חיבור OAuth לאיביי</a>
                <a class="button" href="<?php echo esc_url($policy_url); ?>">משיכת Business Policies</a>
            </p>
            <p><?php echo wp_kses_post($this->token_badge($tokens)); ?></p>
        </section>
        <?php
    }

    private function render_listings_tab(array $settings)
    {
        if (!$this->is_woocommerce_ready()) {
            echo '<div class="notice notice-warning"><p>WooCommerce לא פעיל כרגע.</p></div>';
            return;
        }

        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $stock = isset($_GET['stock']) ? sanitize_key(wp_unslash($_GET['stock'])) : '';
        $paged = max(1, isset($_GET['paged']) ? absint(wp_unslash($_GET['paged'])) : 1);
        $products_query = $this->products_query($search, $stock, $paged);
        ?>
        <section class="colt-ebay__panel">
            <h2>בחירת מוצרים ושליחה לאיביי</h2>
            <form class="colt-ebay__toolbar" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                <input type="hidden" name="page" value="colt-ebay">
                <input type="hidden" name="tab" value="listings">
                <label>
                    <span>חיפוש מוצר</span>
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="שם מוצר או SKU">
                </label>
                <label>
                    <span>סטטוס מלאי</span>
                    <select name="stock">
                        <option value="">הכל</option>
                        <option value="instock" <?php selected($stock, 'instock'); ?>>במלאי</option>
                        <option value="outofstock" <?php selected($stock, 'outofstock'); ?>>אזל מהמלאי</option>
                        <option value="onbackorder" <?php selected($stock, 'onbackorder'); ?>>Backorder</option>
                    </select>
                </label>
                <button class="button button-primary">סינון</button>
                <a class="button" href="<?php echo esc_url(add_query_arg(['page' => 'colt-ebay', 'tab' => 'listings'], admin_url('admin.php'))); ?>">ניקוי</a>
            </form>
        </section>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('colt_ebay_export_products'); ?>
            <input type="hidden" name="action" value="colt_ebay_export_products">
            <section class="colt-ebay__panel">
                <div class="colt-ebay__grid colt-ebay__grid--two">
                    <label>
                        <span>eBay Category ID להעלאה הזו</span>
                        <input type="text" name="category_id" value="<?php echo esc_attr($settings['category_id']); ?>" dir="ltr">
                    </label>
                    <label>
                        <span>מצב פעולה</span>
                        <select name="export_mode">
                            <option value="draft">יצירת Inventory + Offer בלבד</option>
                            <option value="publish">יצירה ופרסום בלייב</option>
                        </select>
                    </label>
                </div>
                <p class="colt-ebay__note">כדי לפרסם בלייב חייבים Token תקין, Location Key, Category ID ופוליסות Payment / Shipping / Return. תמונות חייבות להיות URL ציבורי שהשרת של איביי יכול לגשת אליו. מוצרים שנבחרו נכנסים לתור כדי להימנע מ־timeout ו־Rate Limits.</p>
                <div class="colt-ebay__actions">
                    <strong>נמצאו <?php echo esc_html(number_format_i18n((int) $products_query->found_posts)); ?> מוצרים</strong>
                    <button class="button button-primary button-hero">הוספה לתור eBay</button>
                </div>
                <div class="colt-ebay__table-wrap">
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" data-colt-ebay-check-all></th>
                                <th>מוצר</th>
                                <th>SKU</th>
                                <th>מחיר eBay</th>
                                <th>מלאי</th>
                                <th>סטטוס eBay</th>
                                <th>Offer / Listing</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($products_query->posts) : ?>
                                <?php foreach ($products_query->posts as $product_id) : ?>
                                    <?php
                                    $product = wc_get_product((int) $product_id);
                                    if (!$product) {
                                        continue;
                                    }
                                    $image = get_the_post_thumbnail_url((int) $product_id, 'woocommerce_thumbnail') ?: wc_placeholder_img_src('woocommerce_thumbnail');
                                    $offer_id = (string) get_post_meta((int) $product_id, '_colt_ebay_offer_id', true);
                                    $listing_id = (string) get_post_meta((int) $product_id, '_colt_ebay_listing_id', true);
                                    $status = (string) get_post_meta((int) $product_id, '_colt_ebay_status', true);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product_id); ?>"></td>
                                        <td>
                                            <div class="colt-ebay__product">
                                                <img class="colt-ebay__thumb" src="<?php echo esc_url($image); ?>" alt="">
                                                <span>
                                                    <strong><?php echo esc_html(get_the_title((int) $product_id)); ?></strong>
                                                    <span class="colt-ebay__muted">ID <?php echo esc_html((string) $product_id); ?> · <?php echo esc_html($product->get_type()); ?></span>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?php echo esc_html($product->get_sku() ?: 'COLT-' . (int) $product_id); ?></td>
                                        <td><?php echo esc_html($this->ebay_price($product, $settings) . ' ' . $settings['currency']); ?></td>
                                        <td><?php echo esc_html((string) $this->product_quantity($product)); ?></td>
                                        <td><?php echo wp_kses_post($this->listing_status_badge($status)); ?></td>
                                        <td>
                                            <?php if ($offer_id !== '') : ?><div class="colt-ebay__muted">Offer: <?php echo esc_html($offer_id); ?></div><?php endif; ?>
                                            <?php if ($listing_id !== '') : ?><a href="<?php echo esc_url($this->listing_url($listing_id)); ?>" target="_blank" rel="noopener">פתיחה באיביי</a><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="7">לא נמצאו מוצרים.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php $pagination = $this->pagination($products_query, ['tab' => 'listings', 's' => $search, 'stock' => $stock]); ?>
                <?php if ($pagination) : ?><div class="colt-ebay__pagination"><?php echo wp_kses_post($pagination); ?></div><?php endif; ?>
            </section>
        </form>
        <?php
    }

    private function render_orders_tab()
    {
        $orders_data = get_option(self::ORDERS_OPTION, []);
        $orders = is_array($orders_data['orders'] ?? null) ? $orders_data['orders'] : [];
        ?>
        <section class="colt-ebay__panel">
            <div class="colt-ebay__actions">
                <div>
                    <h2>הזמנות איביי</h2>
                    <p class="colt-ebay__muted">משיכת הזמנות דרך Fulfillment API ועדכון מספרי מעקב.</p>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('colt_ebay_fetch_orders'); ?>
                    <input type="hidden" name="action" value="colt_ebay_fetch_orders">
                    <button class="button button-primary">משיכת הזמנות מאיביי</button>
                </form>
            </div>
            <?php if (!empty($orders_data['fetched_at'])) : ?>
                <p class="colt-ebay__muted">עודכן לאחרונה: <?php echo esc_html(wp_date('d.m.Y H:i', (int) $orders_data['fetched_at'])); ?></p>
            <?php endif; ?>
            <div class="colt-ebay__table-wrap">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>קונה</th>
                            <th>כתובת</th>
                            <th>פריטים</th>
                            <th>סטטוס</th>
                            <th>עדכון משלוח</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders) : ?>
                            <?php foreach ($orders as $order) : ?>
                                <?php
                                $order_id = (string) ($order['orderId'] ?? '');
                                $line_items = is_array($order['lineItems'] ?? null) ? $order['lineItems'] : [];
                                $ship_to = $order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo'] ?? [];
                                $address = $ship_to['contactAddress'] ?? [];
                                ?>
                                <tr>
                                    <td><code><?php echo esc_html($order_id); ?></code></td>
                                    <td><?php echo esc_html($order['buyer']['username'] ?? ($ship_to['fullName'] ?? '')); ?></td>
                                    <td><?php echo esc_html($this->format_address($address)); ?></td>
                                    <td>
                                        <?php foreach ($line_items as $item) : ?>
                                            <div><?php echo esc_html(($item['title'] ?? 'Item') . ' × ' . ($item['quantity'] ?? 1)); ?></div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?php echo esc_html(($order['orderPaymentStatus'] ?? '') . ' / ' . ($order['orderFulfillmentStatus'] ?? '')); ?></td>
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:grid;gap:8px;min-width:220px;">
                                            <?php wp_nonce_field('colt_ebay_update_fulfillment'); ?>
                                            <input type="hidden" name="action" value="colt_ebay_update_fulfillment">
                                            <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                                            <?php foreach ($line_items as $item) : ?>
                                                <?php if (!empty($item['lineItemId'])) : ?>
                                                    <input type="hidden" name="line_items[<?php echo esc_attr($item['lineItemId']); ?>]" value="<?php echo esc_attr(max(1, (int) ($item['quantity'] ?? 1))); ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <input type="text" name="tracking_number" placeholder="Tracking Number" dir="ltr">
                                            <input type="text" name="carrier" value="Israel Post">
                                            <button class="button">עדכון Shipped</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6">עדיין אין הזמנות שמורות. לחץ על משיכת הזמנות.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
    }

    private function render_notifications_tab(array $settings)
    {
        $notifications = get_option(self::NOTIFICATIONS_OPTION, []);
        $notifications = is_array($notifications) ? $notifications : [];
        ?>
        <section class="colt-ebay__panel">
            <h2>שירות לקוחות והתראות</h2>
            <p class="colt-ebay__note">Webhook endpoint: <code dir="ltr"><?php echo esc_html($this->notification_endpoint_url()); ?></code></p>
            <p class="colt-ebay__note">Challenge Validation משתמש ב־Verification Token שמוגדר בטאב ההגדרות. הודעות נכנסות נשמרות כאן ובלוגים.</p>
            <div class="colt-ebay__table-wrap">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>זמן</th>
                            <th>Topic</th>
                            <th>Signature</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($notifications) : ?>
                            <?php foreach ($notifications as $notification) : ?>
                                <tr>
                                    <td><?php echo esc_html(wp_date('d.m.Y H:i', (int) ($notification['time'] ?? time()))); ?></td>
                                    <td><?php echo esc_html($notification['topic'] ?? ''); ?></td>
                                    <td><?php echo esc_html(!empty($notification['signature']) ? 'received' : '—'); ?></td>
                                    <td><code><?php echo esc_html(wp_json_encode($notification['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="4">עדיין לא התקבלו התראות מאיביי.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
    }

    private function render_queue_tab()
    {
        $queue = $this->queue();
        $pending = $this->queue_count_by_status($queue, 'pending');
        $complete = $this->queue_count_by_status($queue, 'complete');
        $failed = $this->queue_count_by_status($queue, 'failed');
        ?>
        <section class="colt-ebay__panel">
            <div class="colt-ebay__actions">
                <div>
                    <h2>תור פעולות eBay</h2>
                    <p class="colt-ebay__muted">פרסום מוצרים ופעולות API נכנסות לתור כדי להפחית עומס, timeout וחריגה מ־Rate Limits.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('colt_ebay_process_queue'); ?>
                        <input type="hidden" name="action" value="colt_ebay_process_queue">
                        <button class="button button-primary">עיבוד 10 משימות עכשיו</button>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('colt_ebay_clear_queue'); ?>
                        <input type="hidden" name="action" value="colt_ebay_clear_queue">
                        <button class="button">ניקוי גמורות / נכשלות</button>
                    </form>
                </div>
            </div>
            <p>
                <span class="colt-ebay__badge colt-ebay__badge--warn">Pending: <?php echo esc_html((string) $pending); ?></span>
                <span class="colt-ebay__badge colt-ebay__badge--ok">Complete: <?php echo esc_html((string) $complete); ?></span>
                <span class="colt-ebay__badge colt-ebay__badge--bad">Failed: <?php echo esc_html((string) $failed); ?></span>
            </p>
            <div class="colt-ebay__table-wrap">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>זמן</th>
                            <th>סוג</th>
                            <th>מוצר</th>
                            <th>מצב</th>
                            <th>ניסיונות</th>
                            <th>שגיאה אחרונה</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($queue) : ?>
                            <?php foreach (array_reverse($queue) as $task) : ?>
                                <?php
                                $product_id = absint($task['product_id'] ?? 0);
                                $status = (string) ($task['status'] ?? 'pending');
                                ?>
                                <tr>
                                    <td><?php echo esc_html(wp_date('d.m.Y H:i', (int) ($task['created_at'] ?? time()))); ?></td>
                                    <td><?php echo esc_html((string) ($task['type'] ?? '')); ?></td>
                                    <td>
                                        <?php if ($product_id > 0) : ?>
                                            <a href="<?php echo esc_url(get_edit_post_link($product_id)); ?>"><?php echo esc_html(get_the_title($product_id) ?: ('Product #' . $product_id)); ?></a>
                                            <div class="colt-ebay__muted">ID <?php echo esc_html((string) $product_id); ?></div>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo wp_kses_post($this->queue_status_badge($status)); ?></td>
                                    <td><?php echo esc_html((string) max(0, (int) ($task['attempts'] ?? 0))); ?></td>
                                    <td><code><?php echo esc_html((string) ($task['last_error'] ?? '')); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6">אין משימות בתור.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
    }

    private function render_logs_tab()
    {
        $logs = $this->logs();
        ?>
        <section class="colt-ebay__panel">
            <div class="colt-ebay__actions">
                <div>
                    <h2>לוגים ושגיאות</h2>
                    <p class="colt-ebay__muted">כל הקריאות החשובות מול eBay נשמרות כאן עם סטטוס ו־JSON מקוצר.</p>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('colt_ebay_clear_logs'); ?>
                    <input type="hidden" name="action" value="colt_ebay_clear_logs">
                    <button class="button">ניקוי לוגים</button>
                </form>
            </div>
            <div class="colt-ebay__table-wrap">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>זמן</th>
                            <th>רמה</th>
                            <th>הודעה</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs) : ?>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><?php echo esc_html(wp_date('d.m.Y H:i:s', (int) ($log['time'] ?? time()))); ?></td>
                                    <td><?php echo esc_html($log['level'] ?? 'info'); ?></td>
                                    <td><?php echo esc_html($log['message'] ?? ''); ?></td>
                                    <td><code><?php echo esc_html(wp_json_encode($log['context'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="4">אין לוגים עדיין.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
    }

    private function export_product($product, $mode, array $overrides = [])
    {
        if (!$product || !method_exists($product, 'get_id')) {
            return new WP_Error('missing_product', 'Product not found.');
        }

        if (method_exists($product, 'is_type') && $product->is_type('variable')) {
            return new WP_Error('variation_product', 'Variation products need Inventory Item Group mapping before export.');
        }

        $settings = $this->settings();
        $product_id = (int) $product->get_id();
        $sku = $this->product_sku($product);
        $quantity = $this->product_quantity($product);
        $category_id = $overrides['category_id'] !== '' ? (string) $overrides['category_id'] : (string) $settings['category_id'];
        $image_urls = $this->product_image_urls($product);

        if ($category_id === '') {
            return new WP_Error('category_missing', 'Missing eBay category ID.');
        }

        if (!$image_urls) {
            return new WP_Error('images_missing', 'Product has no public image URLs.');
        }

        foreach (['payment_policy_id', 'fulfillment_policy_id', 'return_policy_id', 'merchant_location_key'] as $required_setting) {
            if ((string) ($settings[$required_setting] ?? '') === '') {
                return new WP_Error('settings_missing', 'Missing required eBay setting: ' . $required_setting);
            }
        }

        $inventory_payload = $this->build_inventory_payload($product, $sku, $quantity, $settings, $image_urls);
        $inventory_result = $this->api_request('PUT', '/sell/inventory/v1/inventory_item/' . rawurlencode($sku), $inventory_payload);
        if (is_wp_error($inventory_result)) {
            return $inventory_result;
        }

        $offer_payload = $this->build_offer_payload($product, $sku, $quantity, $category_id, $settings);
        $offer_id = (string) get_post_meta($product_id, '_colt_ebay_offer_id', true);

        if ($offer_id !== '') {
            $offer_result = $this->api_request('PUT', '/sell/inventory/v1/offer/' . rawurlencode($offer_id), $offer_payload);
        } else {
            $offer_result = $this->api_request('POST', '/sell/inventory/v1/offer', $offer_payload);
            if (!is_wp_error($offer_result) && !empty($offer_result['offerId'])) {
                $offer_id = (string) $offer_result['offerId'];
            }
        }

        if (is_wp_error($offer_result)) {
            return $offer_result;
        }

        if ($offer_id === '') {
            return new WP_Error('offer_missing', 'eBay did not return an offer ID.');
        }

        update_post_meta($product_id, '_colt_ebay_sku', $sku);
        update_post_meta($product_id, '_colt_ebay_offer_id', $offer_id);
        update_post_meta($product_id, '_colt_ebay_status', 'offer');
        update_post_meta($product_id, '_colt_ebay_last_sync', time());

        if ($mode === 'publish') {
            $publish_result = $this->api_request('POST', '/sell/inventory/v1/offer/' . rawurlencode($offer_id) . '/publish');
            if (is_wp_error($publish_result)) {
                return $publish_result;
            }

            if (!empty($publish_result['listingId'])) {
                update_post_meta($product_id, '_colt_ebay_listing_id', (string) $publish_result['listingId']);
            }
            update_post_meta($product_id, '_colt_ebay_status', 'published');
        }

        $this->add_log('info', 'Product exported to eBay.', [
            'product_id' => $product_id,
            'sku' => $sku,
            'offer_id' => $offer_id,
            'mode' => $mode,
        ]);

        return true;
    }

    private function build_inventory_payload($product, $sku, $quantity, array $settings, array $image_urls)
    {
        return [
            'availability' => [
                'shipToLocationAvailability' => [
                    'quantity' => $quantity,
                ],
            ],
            'condition' => $settings['condition'] ?: 'NEW',
            'product' => [
                'title' => $this->ebay_title($product),
                'description' => wp_strip_all_tags($this->product_description($product)),
                'imageUrls' => $image_urls,
            ],
        ];
    }

    private function build_offer_payload($product, $sku, $quantity, $category_id, array $settings)
    {
        return [
            'sku' => $sku,
            'marketplaceId' => $settings['marketplace_id'],
            'format' => 'FIXED_PRICE',
            'availableQuantity' => $quantity,
            'categoryId' => (string) $category_id,
            'merchantLocationKey' => $settings['merchant_location_key'],
            'listingDescription' => $this->listing_description($product, $sku, $settings),
            'listingPolicies' => [
                'paymentPolicyId' => $settings['payment_policy_id'],
                'fulfillmentPolicyId' => $settings['fulfillment_policy_id'],
                'returnPolicyId' => $settings['return_policy_id'],
            ],
            'pricingSummary' => [
                'price' => [
                    'value' => $this->ebay_price($product, $settings),
                    'currency' => $settings['currency'],
                ],
            ],
        ];
    }

    private function sync_business_policies()
    {
        $settings = $this->settings();
        $marketplace = rawurlencode($settings['marketplace_id']);
        $payment = $this->api_request('GET', '/sell/account/v1/payment_policy?marketplace_id=' . $marketplace);
        if (is_wp_error($payment)) {
            return $payment;
        }

        $fulfillment = $this->api_request('GET', '/sell/account/v1/fulfillment_policy?marketplace_id=' . $marketplace);
        if (is_wp_error($fulfillment)) {
            return $fulfillment;
        }

        $returns = $this->api_request('GET', '/sell/account/v1/return_policy?marketplace_id=' . $marketplace);
        if (is_wp_error($returns)) {
            return $returns;
        }

        $policies = [
            'paymentPolicies' => is_array($payment['paymentPolicies'] ?? null) ? $payment['paymentPolicies'] : [],
            'fulfillmentPolicies' => is_array($fulfillment['fulfillmentPolicies'] ?? null) ? $fulfillment['fulfillmentPolicies'] : [],
            'returnPolicies' => is_array($returns['returnPolicies'] ?? null) ? $returns['returnPolicies'] : [],
            'synced_at' => time(),
        ];

        update_option(self::POLICIES_OPTION, $policies, false);
        $this->add_log('info', 'Business policies synced from eBay.', [
            'payment' => count($policies['paymentPolicies']),
            'fulfillment' => count($policies['fulfillmentPolicies']),
            'returns' => count($policies['returnPolicies']),
        ]);

        return $policies;
    }

    private function api_request($method, $path, $body = null, $retry = true)
    {
        $token = $this->valid_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $settings = $this->settings();
        $url = $this->api_url($path);
        $args = [
            'method' => $method,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Content-Language' => 'en-US',
                'X-EBAY-C-MARKETPLACE-ID' => $settings['marketplace_id'],
            ],
        ];

        if ($body !== null) {
            $args['body'] = wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = (string) wp_remote_retrieve_body($response);
        $decoded = $raw_body !== '' ? json_decode($raw_body, true) : [];
        $decoded = is_array($decoded) ? $decoded : ['raw' => $raw_body];

        if ($code === 401 && $retry) {
            $refresh = $this->refresh_access_token();
            if (!is_wp_error($refresh)) {
                return $this->api_request($method, $path, $body, false);
            }
        }

        if ($code < 200 || $code >= 300) {
            $this->add_log('error', 'eBay API error.', [
                'method' => $method,
                'path' => $path,
                'status' => $code,
                'response' => $this->trim_context($decoded),
            ]);
            return new WP_Error('ebay_api_error', 'eBay API error ' . $code . ': ' . $this->response_message($decoded));
        }

        $this->add_log('info', 'eBay API call completed.', [
            'method' => $method,
            'path' => $path,
            'status' => $code,
        ]);

        return $decoded;
    }

    private function exchange_authorization_code($code)
    {
        $settings = $this->settings();
        $response = wp_remote_post($this->token_url(), [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($settings['app_id'] . ':' . $settings['cert_id']),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $settings['redirect_uri'],
            ],
        ]);

        return $this->store_token_response($response, true);
    }

    private function refresh_access_token()
    {
        $settings = $this->settings();
        $tokens = $this->tokens();
        if (empty($tokens['refresh_token'])) {
            return new WP_Error('missing_refresh_token', 'Missing eBay refresh token.');
        }

        $response = wp_remote_post($this->token_url(), [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($settings['app_id'] . ':' . $settings['cert_id']),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokens['refresh_token'],
                'scope' => $settings['scopes'],
            ],
        ]);

        return $this->store_token_response($response, false);
    }

    private function store_token_response($response, $has_refresh_token)
    {
        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $body = is_array($body) ? $body : [];
        if ($code < 200 || $code >= 300 || empty($body['access_token'])) {
            return new WP_Error('token_error', 'Could not get eBay token: ' . $this->response_message($body));
        }

        $current = $this->tokens();
        $tokens = [
            'access_token' => (string) $body['access_token'],
            'refresh_token' => $has_refresh_token ? (string) ($body['refresh_token'] ?? '') : (string) ($current['refresh_token'] ?? ''),
            'expires_at' => time() + max(60, (int) ($body['expires_in'] ?? 7200)) - 120,
            'refresh_expires_at' => $has_refresh_token && !empty($body['refresh_token_expires_in']) ? time() + (int) $body['refresh_token_expires_in'] : (int) ($current['refresh_expires_at'] ?? 0),
            'updated_at' => time(),
        ];

        update_option(self::TOKEN_OPTION, $tokens, false);
        $this->add_log('info', 'eBay OAuth token stored.', ['has_refresh_token' => $tokens['refresh_token'] !== '' ? 'yes' : 'no']);

        return $tokens;
    }

    private function valid_access_token()
    {
        $tokens = $this->tokens();
        if (!empty($tokens['access_token']) && (int) ($tokens['expires_at'] ?? 0) > time() + 60) {
            return (string) $tokens['access_token'];
        }

        $tokens = $this->refresh_access_token();
        if (is_wp_error($tokens)) {
            return $tokens;
        }

        return (string) $tokens['access_token'];
    }

    private function render_notices()
    {
        $successes = [
            'saved' => 'הגדרות eBay נשמרו.',
            'oauth_connected' => 'החיבור לאיביי נשמר בהצלחה.',
            'policies_synced' => 'Business Policies נמשכו מאיביי ונשמרו.',
            'orders_synced' => 'הזמנות נמשכו מאיביי.',
            'fulfillment_updated' => 'פרטי המשלוח נשלחו לאיביי.',
            'logs_cleared' => 'הלוגים נוקו.',
            'queue_processed' => 'תור eBay עובד ידנית.',
            'queue_cleared' => 'משימות גמורות ונכשלות נוקו מהתור.',
        ];

        foreach ($successes as $key => $message) {
            if (isset($_GET[$key])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
        }

        if (isset($_GET['ebay_exported'])) {
            $queued = isset($_GET['ebay_queued']) ? (int) $_GET['ebay_queued'] : 0;
            $exported = (int) $_GET['ebay_exported'];
            $failed = isset($_GET['ebay_failed']) ? (int) $_GET['ebay_failed'] : 0;
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($queued . ' מוצרים נכנסו לתור. פורססו עכשיו: ' . $exported . '. נכשלו: ' . $failed) . '</p></div>';
        }

        if (isset($_GET['queue_done'])) {
            $done = (int) $_GET['queue_done'];
            $failed = isset($_GET['queue_failed']) ? (int) $_GET['queue_failed'] : 0;
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html('עיבוד תור הסתיים. הצליחו: ' . $done . '. נכשלו: ' . $failed) . '</p></div>';
        }

        if (isset($_GET['ebay_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($this->error_message((string) wp_unslash($_GET['ebay_error']))) . '</p></div>';
        }
    }

    private function render_policy_select($name, $label, $value, array $policies)
    {
        $list_id = 'colt-ebay-' . sanitize_key($name) . '-list';
        ?>
        <label>
            <span><?php echo esc_html($label); ?></span>
            <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" list="<?php echo esc_attr($list_id); ?>" placeholder="בחירה מרשימה או הזנה ידנית של Policy ID" dir="ltr">
            <datalist id="<?php echo esc_attr($list_id); ?>">
                <?php foreach ($policies as $policy) : ?>
                    <?php
                    $policy_id = (string) ($policy[$name] ?? $policy[str_replace('_id', 'Id', $name)] ?? '');
                    if ($policy_id === '') {
                        foreach ($policy as $policy_key => $policy_value) {
                            if (stripos((string) $policy_key, 'policyId') !== false) {
                                $policy_id = (string) $policy_value;
                                break;
                            }
                        }
                    }
                    $policy_name = (string) ($policy['name'] ?? $policy_id);
                    ?>
                    <?php if ($policy_id !== '') : ?>
                        <option value="<?php echo esc_attr($policy_id); ?>"><?php echo esc_html($policy_name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </datalist>
        </label>
        <?php
    }

    private function tabs()
    {
        return [
            'listings' => 'מוצרים וליסטינגים',
            'settings' => 'הגדרות וחיבור',
            'orders' => 'הזמנות איביי',
            'notifications' => 'שירות לקוחות והתראות',
            'queue' => 'תור פעולות',
            'logs' => 'לוגים',
        ];
    }

    private function default_settings()
    {
        return [
            'environment' => 'production',
            'app_id' => '',
            'dev_id' => '',
            'cert_id' => '',
            'redirect_uri' => '',
            'scopes' => implode(' ', [
                'https://api.ebay.com/oauth/api_scope/sell.inventory',
                'https://api.ebay.com/oauth/api_scope/sell.account',
                'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
                'https://api.ebay.com/oauth/api_scope/commerce.notification.subscription',
            ]),
            'marketplace_id' => 'EBAY_US',
            'currency' => 'USD',
            'exchange_rate' => '1',
            'price_markup_percent' => '0',
            'price_markup_fixed' => '0',
            'merchant_location_key' => 'default',
            'category_id' => '',
            'condition' => 'NEW',
            'payment_policy_id' => '',
            'fulfillment_policy_id' => '',
            'return_policy_id' => '',
            'description_template' => '<h2>{product_name}</h2>{description}<p><strong>SKU:</strong> {sku}</p>',
            'verification_token' => '',
            'auto_sync_enabled' => '0',
        ];
    }

    private function settings()
    {
        $settings = get_option(self::SETTINGS_OPTION, []);
        $settings = array_merge($this->default_settings(), is_array($settings) ? $settings : []);
        $settings['marketplace_id'] = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $settings['marketplace_id']));
        $settings['currency'] = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $settings['currency']));
        if ($settings['marketplace_id'] === '') {
            $settings['marketplace_id'] = 'EBAY_US';
        }
        if ($settings['currency'] === '') {
            $settings['currency'] = 'USD';
        }

        return $settings;
    }

    private function tokens()
    {
        $tokens = get_option(self::TOKEN_OPTION, []);
        return is_array($tokens) ? $tokens : [];
    }

    private function policies()
    {
        $policies = get_option(self::POLICIES_OPTION, []);
        return is_array($policies) ? $policies : [];
    }

    private function logs()
    {
        $logs = get_option(self::LOGS_OPTION, []);
        return is_array($logs) ? $logs : [];
    }

    private function queue()
    {
        $queue = get_option(self::QUEUE_OPTION, []);
        $queue = is_array($queue) ? $queue : [];
        $now = time();

        foreach ($queue as $index => $task) {
            if (!is_array($task)) {
                unset($queue[$index]);
                continue;
            }

            if ((string) ($task['status'] ?? '') === 'running' && (int) ($task['started_at'] ?? 0) < $now - 15 * MINUTE_IN_SECONDS) {
                $task['status'] = 'pending';
                $task['last_error'] = 'Recovered from a timed-out queue run.';
                $queue[$index] = $task;
            }
        }

        return array_values($queue);
    }

    private function enqueue_export_products(array $product_ids, $mode, array $overrides)
    {
        $queue = $this->queue();
        $mode = $mode === 'publish' ? 'publish' : 'draft';
        $added = 0;
        $category_id = (string) ($overrides['category_id'] ?? '');
        $active_keys = [];

        foreach ($queue as $task) {
            if (!is_array($task) || !in_array((string) ($task['status'] ?? ''), ['pending', 'running'], true)) {
                continue;
            }

            $task_overrides = is_array($task['overrides'] ?? null) ? $task['overrides'] : [];
            $active_keys[] = absint($task['product_id'] ?? 0) . '|' . (string) ($task['mode'] ?? 'draft') . '|' . (string) ($task_overrides['category_id'] ?? '');
        }
        $active_keys = array_fill_keys($active_keys, true);

        foreach ($product_ids as $product_id) {
            $product_id = absint($product_id);
            if ($product_id < 1) {
                continue;
            }

            $task_key = $product_id . '|' . $mode . '|' . $category_id;
            if (isset($active_keys[$task_key])) {
                continue;
            }

            $queue[] = [
                'id' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('colt_ebay_', true),
                'type' => 'export_product',
                'product_id' => $product_id,
                'mode' => $mode,
                'overrides' => [
                    'category_id' => $category_id,
                ],
                'status' => 'pending',
                'attempts' => 0,
                'last_error' => '',
                'created_at' => time(),
            ];
            $active_keys[$task_key] = true;
            $added++;
        }

        update_option(self::QUEUE_OPTION, $this->trim_queue($queue), false);
        $this->schedule_queue_processing();

        $this->add_log('info', 'Products queued for eBay export.', [
            'count' => $added,
            'mode' => $mode,
        ]);

        return $added;
    }

    private function process_queue_task(array $task)
    {
        $type = (string) ($task['type'] ?? '');
        if ($type !== 'export_product') {
            return new WP_Error('queue_type', 'Unsupported eBay queue task type.');
        }

        $product_id = absint($task['product_id'] ?? 0);
        $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
        $mode = (string) ($task['mode'] ?? 'draft') === 'publish' ? 'publish' : 'draft';
        $overrides = is_array($task['overrides'] ?? null) ? $task['overrides'] : [];

        return $this->export_product($product, $mode, [
            'category_id' => (string) ($overrides['category_id'] ?? ''),
        ]);
    }

    private function trim_queue(array $queue)
    {
        $active = [];
        $finished = [];

        foreach ($queue as $task) {
            if (!is_array($task)) {
                continue;
            }

            if (in_array((string) ($task['status'] ?? ''), ['complete', 'failed'], true)) {
                $finished[] = $task;
            } else {
                $active[] = $task;
            }
        }

        return array_merge($active, array_slice($finished, -80));
    }

    private function queue_has_pending(array $queue)
    {
        foreach ($queue as $task) {
            if (is_array($task) && (string) ($task['status'] ?? '') === 'pending') {
                return true;
            }
        }

        return false;
    }

    private function schedule_queue_processing()
    {
        if (!wp_next_scheduled(self::QUEUE_CRON_HOOK)) {
            wp_schedule_single_event(time() + 2 * MINUTE_IN_SECONDS, self::QUEUE_CRON_HOOK);
        }
    }

    private function queue_count_by_status(array $queue, $status)
    {
        $count = 0;
        foreach ($queue as $task) {
            if (is_array($task) && (string) ($task['status'] ?? '') === (string) $status) {
                $count++;
            }
        }

        return $count;
    }

    private function add_log($level, $message, array $context = [])
    {
        $logs = $this->logs();
        array_unshift($logs, [
            'time' => time(),
            'level' => sanitize_key($level),
            'message' => (string) $message,
            'context' => $this->trim_context($context),
        ]);

        update_option(self::LOGS_OPTION, array_slice($logs, 0, 100), false);
    }

    private function trim_context($context)
    {
        $encoded = wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded && strlen($encoded) > 4000) {
            return ['truncated' => substr($encoded, 0, 4000)];
        }

        return $context;
    }

    private function products_query($search, $stock, $paged)
    {
        $args = [
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 30,
            'paged' => max(1, (int) $paged),
            'fields' => 'ids',
        ];

        if ($search !== '') {
            $args['s'] = $search;
        }

        if ($stock !== '' && in_array($stock, ['instock', 'outofstock', 'onbackorder'], true)) {
            $args['meta_query'] = [
                [
                    'key' => '_stock_status',
                    'value' => $stock,
                ],
            ];
        }

        return new WP_Query($args);
    }

    private function pagination(WP_Query $query, array $filters)
    {
        if ((int) $query->max_num_pages < 2) {
            return '';
        }

        return paginate_links([
            'base' => add_query_arg(array_merge(['page' => 'colt-ebay', 'paged' => '%#%'], $filters), admin_url('admin.php')),
            'format' => '',
            'current' => max(1, isset($_GET['paged']) ? absint(wp_unslash($_GET['paged'])) : 1),
            'total' => (int) $query->max_num_pages,
            'prev_text' => '‹',
            'next_text' => '›',
        ]);
    }

    private function product_sku($product)
    {
        $sku = method_exists($product, 'get_sku') ? (string) $product->get_sku() : '';
        return $sku !== '' ? $sku : 'COLT-' . (int) $product->get_id();
    }

    private function product_quantity($product)
    {
        $quantity = method_exists($product, 'get_stock_quantity') ? $product->get_stock_quantity() : null;
        if ($quantity !== null) {
            return max(0, (int) $quantity);
        }

        return method_exists($product, 'get_stock_status') && $product->get_stock_status() === 'instock' ? 1 : 0;
    }

    private function ebay_title($product)
    {
        $title = html_entity_decode(wp_strip_all_tags((string) $product->get_name()), ENT_QUOTES, get_bloginfo('charset'));
        return function_exists('mb_substr') ? mb_substr($title, 0, 80) : substr($title, 0, 80);
    }

    private function product_description($product)
    {
        $description = method_exists($product, 'get_description') ? (string) $product->get_description() : '';
        if ($description === '' && method_exists($product, 'get_short_description')) {
            $description = (string) $product->get_short_description();
        }

        return $description !== '' ? $description : (string) $product->get_name();
    }

    private function listing_description($product, $sku, array $settings)
    {
        $template = (string) $settings['description_template'];
        $description = wpautop($this->product_description($product));

        return strtr($template, [
            '{product_name}' => esc_html((string) $product->get_name()),
            '{sku}' => esc_html($sku),
            '{description}' => wp_kses_post($description),
            '{short_description}' => wp_kses_post(wpautop(method_exists($product, 'get_short_description') ? (string) $product->get_short_description() : '')),
        ]);
    }

    private function product_image_urls($product)
    {
        $urls = [];
        $image_id = method_exists($product, 'get_image_id') ? (int) $product->get_image_id() : 0;
        if ($image_id > 0) {
            $url = wp_get_attachment_image_url($image_id, 'full');
            if ($url) {
                $urls[] = $url;
            }
        }

        $gallery_ids = method_exists($product, 'get_gallery_image_ids') ? $product->get_gallery_image_ids() : [];
        foreach ($gallery_ids as $gallery_id) {
            $url = wp_get_attachment_image_url((int) $gallery_id, 'full');
            if ($url) {
                $urls[] = $url;
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }

    private function ebay_price($product, array $settings)
    {
        $base = method_exists($product, 'get_regular_price') && $product->get_regular_price() !== '' ? (float) $product->get_regular_price() : (float) $product->get_price();
        $exchange_rate = max(0, (float) $settings['exchange_rate']);
        $price = $base * ($exchange_rate > 0 ? $exchange_rate : 1);
        $price *= 1 + (max(0, (float) $settings['price_markup_percent']) / 100);
        $price += max(0, (float) $settings['price_markup_fixed']);

        return number_format(max(0.01, $price), 2, '.', '');
    }

    private function listing_status_badge($status)
    {
        if ($status === 'published') {
            return '<span class="colt-ebay__badge colt-ebay__badge--ok">Published</span>';
        }

        if ($status === 'offer') {
            return '<span class="colt-ebay__badge colt-ebay__badge--warn">Offer Draft</span>';
        }

        return '<span class="colt-ebay__badge">Not Sent</span>';
    }

    private function queue_status_badge($status)
    {
        if ($status === 'complete') {
            return '<span class="colt-ebay__badge colt-ebay__badge--ok">Complete</span>';
        }

        if ($status === 'failed') {
            return '<span class="colt-ebay__badge colt-ebay__badge--bad">Failed</span>';
        }

        if ($status === 'running') {
            return '<span class="colt-ebay__badge colt-ebay__badge--warn">Running</span>';
        }

        return '<span class="colt-ebay__badge">Pending</span>';
    }

    private function token_badge(array $tokens)
    {
        if (!empty($tokens['access_token']) && (int) ($tokens['expires_at'] ?? 0) > time()) {
            return '<span class="colt-ebay__pill">OAuth מחובר · תוקף עד ' . esc_html(wp_date('d.m.Y H:i', (int) $tokens['expires_at'])) . '</span>';
        }

        if (!empty($tokens['refresh_token'])) {
            return '<span class="colt-ebay__pill">Refresh Token שמור · Access Token ירוענן בקריאה הבאה</span>';
        }

        return '<span class="colt-ebay__pill">לא מחובר לאיביי</span>';
    }

    private function posted_ids($field)
    {
        $raw = isset($_POST[$field]) ? (array) wp_unslash($_POST[$field]) : [];
        return array_values(array_unique(array_filter(array_map('absint', $raw))));
    }

    private function posted_text_values($field)
    {
        $raw = isset($_POST[$field]) ? (array) wp_unslash($_POST[$field]) : [];
        $values = array_map('sanitize_text_field', $raw);
        return array_values(array_filter($values));
    }

    private function posted_line_items()
    {
        $raw = isset($_POST['line_items']) ? (array) wp_unslash($_POST['line_items']) : [];
        $line_items = [];

        foreach ($raw as $line_item_id => $quantity) {
            $line_item_id = sanitize_text_field((string) $line_item_id);
            if ($line_item_id === '') {
                continue;
            }

            $line_items[] = [
                'lineItemId' => $line_item_id,
                'quantity' => max(1, (int) $quantity),
            ];
        }

        return $line_items;
    }

    private function decimal_from_post($field, $default = null)
    {
        $raw = isset($_POST[$field]) ? trim((string) wp_unslash($_POST[$field])) : '';
        if ($raw === '') {
            return $default;
        }

        $raw = str_replace(',', '.', $raw);
        $raw = preg_replace('/[^0-9.\-]/', '', $raw);
        return is_numeric($raw) ? (string) max(0, (float) $raw) : $default;
    }

    private function can_manage()
    {
        return current_user_can('manage_options') || current_user_can('manage_woocommerce');
    }

    private function is_woocommerce_ready()
    {
        return function_exists('wc_get_product') && post_type_exists('product');
    }

    private function redirect(array $args)
    {
        wp_safe_redirect(add_query_arg(array_merge(['page' => 'colt-ebay'], $args), admin_url('admin.php')));
        exit;
    }

    private function api_url($path)
    {
        return rtrim($this->api_base_url(), '/') . '/' . ltrim($path, '/');
    }

    private function api_base_url()
    {
        return $this->settings()['environment'] === 'sandbox' ? 'https://api.sandbox.ebay.com' : 'https://api.ebay.com';
    }

    private function auth_url()
    {
        return $this->settings()['environment'] === 'sandbox' ? 'https://auth.sandbox.ebay.com/oauth2/authorize' : 'https://auth.ebay.com/oauth2/authorize';
    }

    private function token_url()
    {
        return $this->api_base_url() . '/identity/v1/oauth2/token';
    }

    private function notification_endpoint_url()
    {
        return rest_url('colt-experience/v1/ebay-notifications');
    }

    private function listing_url($listing_id)
    {
        $base = $this->settings()['environment'] === 'sandbox' ? 'https://sandbox.ebay.com/itm/' : 'https://www.ebay.com/itm/';
        return $base . rawurlencode((string) $listing_id);
    }

    private function response_message(array $response)
    {
        if (!empty($response['errors'][0]['message'])) {
            return (string) $response['errors'][0]['message'];
        }

        if (!empty($response['error_description'])) {
            return (string) $response['error_description'];
        }

        if (!empty($response['message'])) {
            return (string) $response['message'];
        }

        return 'Unknown response';
    }

    private function error_message($code)
    {
        $messages = [
            'missing_credentials' => 'חסרים App ID או Redirect URI.',
            'oauth_state' => 'אימות OAuth נכשל בגלל state לא תקין.',
            'oauth' => 'איביי החזירה שגיאה בתהליך OAuth.',
            'oauth_code' => 'לא התקבל Authorization Code מאיביי.',
            'oauth_exchange' => 'לא ניתן להמיר Authorization Code לטוקן.',
            'policies' => 'לא ניתן למשוך Business Policies. בדוק טוקן והרשאות.',
            'no_products' => 'לא נבחרו מוצרים לשליחה.',
            'orders' => 'לא ניתן למשוך הזמנות מאיביי.',
            'fulfillment_fields' => 'חסרים Order ID, פריטים או Tracking Number.',
            'fulfillment' => 'לא ניתן לעדכן משלוח באיביי.',
        ];

        return $messages[$code] ?? 'לא ניתן להשלים את הפעולה. בדוק את הלוגים.';
    }

    private function format_address(array $address)
    {
        $parts = [
            $address['addressLine1'] ?? '',
            $address['addressLine2'] ?? '',
            $address['city'] ?? '',
            $address['stateOrProvince'] ?? '',
            $address['postalCode'] ?? '',
            $address['countryCode'] ?? '',
        ];

        return implode(', ', array_filter(array_map('strval', $parts)));
    }
}
