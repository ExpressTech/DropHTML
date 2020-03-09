<?php
/**
 * Plugin Name:       DropHTML
 * Description:       Simply drop static HTML zip files and see the magic! 
 * Version:           1.0.2
 * Requires at least: 4.8
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       drop
 * Author URI: https://expresstech.io/
 * Plugin URI: https://expresstech.io/
 **/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

define( 'DROPHTML__FILE__', __FILE__ );
define( 'DROPHTML__VERSION', '1.0.2');

/**
 * DropHtml check PHP version.
 *
 * Check when the site doesn't have the minimum required PHP version.
 *
 * @since 1.3.1
 *
 * @return void
 */
function drop_html_is_php_version_compatible() {
	if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
		return false;
	}
	return true;
}

if ( ! drop_html_is_php_version_compatible() ) {
    add_action( 'admin_notices', 'drop_html_fail_php_version' );
} else {
    require plugin_dir_path( DROPHTML__FILE__ ) . 'includes/plugin.php';
}

/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function drop_html_fail_php_version() {
    $message = esc_html__( 'DropHtml requires PHP version 5.4+, plugin is currently NOT ACTIVE.', 'drophtml' );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}

/**
 * Helper function for writing to log file.
 *
 * @since 1.0.0
 *
 * @param log data to log
 * @param type log or export
 */
function drop_html_write_log( $log, $type = '1' ) {
    if ( true === WP_DEBUG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            if ( $type === '1' ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( var_export( $log, true ) );
            }
        } else {
            error_log( $log );
        }
    }
}

/**
 * The main function responsible for returning the one true DropHtml Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $drop_html = drop_html(); ?>
 *
 * @since 1.0.0
 *
 * @return The one true DropHtml Instance
 */
function drop_html() {
    if ( ! drop_html_is_php_version_compatible() ) {
        return;
    }  
    // In tests we run the instance manually.
    return DropHtml\Plugin::instance();
}

if ( ! defined('DROPHTML_TESTS' ) ) {
    drop_html();
}
