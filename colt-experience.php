<?php
/**
 * Plugin Name: COLT Experience
 * Description: Premium shortcode-driven experience pages for the COLT collectibles site.
 * Version: 0.1.0
 * Author: COLT
 * Text Domain: colt-experience
 */

if (!defined('ABSPATH')) {
    exit;
}

define('COLT_EXPERIENCE_VERSION', '0.1.0');
define('COLT_EXPERIENCE_FILE', __FILE__);
define('COLT_EXPERIENCE_DIR', plugin_dir_path(__FILE__));
define('COLT_EXPERIENCE_URL', plugin_dir_url(__FILE__));

require_once COLT_EXPERIENCE_DIR . 'includes/class-colt-experience.php';

add_action('plugins_loaded', static function () {
    Colt_Experience::instance();
});
