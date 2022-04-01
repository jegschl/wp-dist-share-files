<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://empdigital.cl
 * @since             1.0.0
 * @package           Wp_Dosf
 *
 * @wordpress-plugin
 * Plugin Name:       Dist or Share Files with Wordpress
 * Plugin URI:        https://empdigital.cl
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Jorge Garrido
 * Author URI:        https://empdigital.cl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-dosf
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
define( 'WP_DOSF_VERSION', '1.0.0' );
define( 'WP_DOSF_PLUGIN_PATH',dirname( __FILE__ ) );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-dosf-activator.php
 */
function activate_wp_dosf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dosf-activator.php';
	Wp_Dosf_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-dosf-deactivator.php
 */
function deactivate_wp_dosf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dosf-deactivator.php';
	Wp_Dosf_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_dosf' );
register_deactivation_hook( __FILE__, 'deactivate_wp_dosf' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dosf.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_dosf() {

	$plugin = new Wp_Dosf();
	$plugin->run();

}
run_wp_dosf();
