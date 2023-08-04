<?php
/**
 * File used for importing pro-only files.
 *
 * @package WPCode
 */

if ( is_admin() || defined( 'DOING_CRON' ) && DOING_CRON ) {
	// Class used for loading My Library items.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-my-library.php';
	// Class used for loading My Favourites items.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-my-favourites.php';
	// Pro-specific admin page loader.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-admin-page-loader-pro.php';
	// Pro-specific admin scripts.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/admin-scripts.php';
	// Pro-specific admin scripts.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-metabox-snippets-pro.php';
	// Pro-specific ajax endpoints.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/admin-ajax-handlers.php';
	// License.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-license.php';
	// Updates.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-updates.php';
	// Addons classes.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-addons.php';
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-addons-pro.php';

	// Pro snippets table changes.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/snippets-table.php';

	// Usage tracking abstract.
	require_once WPCODE_PLUGIN_PATH . 'includes/admin/class-wpcode-usage-tracking.php';
	// Usage tracking lite.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-usage-tracking-pro.php';
}

// Pro install routines.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/install.php';
// Load the db class.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-db.php';
// Load page scripts.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-page-scripts.php';
// Load snippets by location from the metabox.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-metabox-auto-insert.php';
// Load smart tags class.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-smart-tags-pro.php';
// Load custom shortcodes class.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-custom-shortcode.php';
// Load revisions class.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-revisions.php';
// Load device type filtering.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/device-type.php';
// Load schedule filtering.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/schedule.php';
// Execute snippets.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-snippet-execute-pro.php';

// Load the block editor integration.
require_once WPCODE_PLUGIN_PATH . 'includes/pro/class-wpcode-snippet-block-editor.php';

add_action( 'plugins_loaded', 'wpcode_plugins_loaded_load_pro_files', 2 );
add_action( 'plugins_loaded', 'wpcode_load_pro_updates' );

/**
 * Require files on plugins_loaded.
 *
 * @return void
 */
function wpcode_plugins_loaded_load_pro_files() {
	// Load the updater.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/class-wpcode-updates.php';
	// Load WooCommerce auto-insert locations.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/auto-insert/class-wpcode-auto-insert-woocommerce.php';
	// Load EDD auto-insert locations.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/auto-insert/class-wpcode-auto-insert-edd.php';
	// Load MemberPress auto-insert locations.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/auto-insert/class-wpcode-auto-insert-memberpress.php';
	// Load Anywhere auto-insert locations.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/auto-insert/class-wpcode-auto-insert-anywhere.php';
	// Load Device conditional logic.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/conditional-logic/class-wpcode-conditional-device.php';
	// Load WooCommerce conditional logic.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/conditional-logic/class-wpcode-conditional-woocommerce.php';
	// Load EDD conditional logic.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/conditional-logic/class-wpcode-conditional-edd.php';
	// Load MemberPress conditional logic.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/conditional-logic/class-wpcode-conditional-memberpress.php';
	// Load Schedule conditional logic.
	require_once WPCODE_PLUGIN_PATH . 'includes/pro/conditional-logic/class-wpcode-conditional-schedule.php';
}

/**
 * Load the updates class.
 *
 * @return void
 */
function wpcode_load_pro_updates() {
	// Only load this in the admin.
	if ( ! is_admin() || ! isset( wpcode()->license ) ) {
		return;
	}
	new WPCode_Updates(
		array(
			'plugin_slug' => 'wpcode-premium',
			'plugin_path' => WPCODE_PLUGIN_BASENAME,
			'version'     => WPCODE_VERSION,
			'key'         => wpcode()->license->get(),
		)
	);
}
