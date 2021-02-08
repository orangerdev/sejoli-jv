<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sejoli.co.id
 * @since             1.0.0
 * @package           Sejoli_JV
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Joint Venture
 * Plugin URI:        https://sejoli.co.id
 * Description:       Implements JV feature to Sejoli Premium Membership Plugin
 * Version:           1.0.0
 * Author:            Sejoli
 * Author URI:        https://sejoli.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-jv
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_JV_VERSION', 	'1.0.0' );
define( 'SEJOLI_JV_DIR', 		plugin_dir_path( __FILE__ ) );
define( 'SEJOLI_JV_URL', 		plugin_dir_url( __FILE__ ) );

add_action('muplugins_loaded', 'sejoli_jv_check_sejoli');

function sejoli_jv_check_sejoli() {

	if(!defined('SEJOLISA_VERSION')) :

		add_action('admin_notices', 'sejoli_jv_no_sejoli_functions');

		function sejoli_jv_no_sejoli_functions() {
			?><div class='notice notice-error'>
			<p><?php _e('Anda belum menginstall atau mengaktifkan SEJOLI terlebih dahulu.', 'sejoli'); ?></p>
			</div><?php
		}

		return;
	endif;

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sejoli-jv-activator.php
 */
function activate_sejoli_jv() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-jv-activator.php';
	Sejoli_JV_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sejoli-jv-deactivator.php
 */
function deactivate_sejoli_jv() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-jv-deactivator.php';
	Sejoli_JV_Deactivator::deactivate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-jv.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sejoli_jv() {

	$plugin = new Sejoli_JV();
	$plugin->run();

}

require_once(SEJOLI_JV_DIR . 'third-parties/yahnis-elsts/plugin-update-checker/plugin-update-checker.php');

$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/orangerdev/sejoli-jv',
	__FILE__,
	'sejoli-jv'
);

$update_checker->setBranch('master');

run_sejoli_jv();

register_activation_hook( __FILE__, 'activate_sejoli_jv' );
register_deactivation_hook( __FILE__, 'deactivate_sejoli_jv' );
