<?php
/**
 * Plugin Name: COLT Experience
 * Plugin URI: https://github.com/Astratego-dev/colt
 * Description: Premium shortcode-driven experience pages for the COLT collectibles site.
 * Version: 1.0.0
 * Author: COLT
 * Text Domain: colt-experience
 * GitHub Plugin URI: https://github.com/Astratego-dev/colt
 * Primary Branch: main
 */

if (!defined('ABSPATH')) {
    exit;
}

define('COLT_EXPERIENCE_VERSION', '1.0.0');
define('COLT_EXPERIENCE_FILE', __FILE__);
define('COLT_EXPERIENCE_DIR', plugin_dir_path(__FILE__));
define('COLT_EXPERIENCE_URL', plugin_dir_url(__FILE__));

require_once COLT_EXPERIENCE_DIR . 'includes/class-colt-experience.php';

add_action('plugins_loaded', static function () {
    Colt_Experience::instance();
});
