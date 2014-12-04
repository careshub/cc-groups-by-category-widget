<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           CC Group Pages
 *
 * @wordpress-plugin
 * Plugin Name:       CC Groups-by-Category Widget
 * Description:       Adds a category-aware group list, for use on category pages.
 * Version:           1.0.0
 * Author:            David Cavins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cc-groups-by-category
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-deactivator.php';

/** This action is documented in includes/class-plugin-name-activator.php */
// register_activation_hook( __FILE__, array( 'Plugin_Name_Activator', 'activate' ) );

/** This action is documented in includes/class-plugin-name-deactivator.php */
// register_deactivation_hook( __FILE__, array( 'Plugin_Name_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages.php';

/**
 * Load the main class
 *
 * @since    1.0.0 
 */
function cc_groups_by_cat_widget_init() {
	// The main class
	require_once plugin_dir_path( __FILE__ ) . 'cc-groups-by-category-widget.php';
}
add_action( 'bp_include', 'cc_groups_by_cat_widget_init' );