<?php
/**
 * Pro-specific admin page loader.
 * Replaces the classes used for generic pages with pro-specific ones.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Admin_Page_Loader_Pro.
 */
class WPCode_Admin_Page_Loader_Pro extends WPCode_Admin_Page_Loader {

	public function hooks() {
		parent::hooks();
		add_filter( 'plugin_action_links_' . WPCODE_PLUGIN_BASENAME, array( $this, 'pro_action_links' ) );
	}

	/**
	 * Require pro-specific files.
	 *
	 * @return void
	 */
	public function require_files() {
		parent::require_files();
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/pages/class-wpcode-admin-page-snippet-manager-pro.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/pages/class-wpcode-admin-page-library-pro.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/pages/class-wpcode-admin-page-revisions.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/pages/class-wpcode-admin-page-settings-pro.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/admin/pages/class-wpcode-admin-page-pixel-pro.php';
	}

	/**
	 * Override pro-specific pages.
	 *
	 * @return void
	 */
	public function prepare_pages() {
		parent::prepare_pages();

		$this->pages['snippet_manager'] = 'WPCode_Admin_Page_Snippet_Manager_Pro';
		$this->pages['library']         = 'WPCode_Admin_Page_Library_Pro';
		$this->pages['revisions']       = 'WPCode_Admin_Page_Revisions';
		$this->pages['settings']        = 'WPCode_Admin_Page_Settings_Pro';
		$this->pages['pixel']           = 'WPCode_Admin_Page_Pixel_Pro';
	}

	/**
	 * Add pro-specific links.
	 *
	 * @param array $links The links array.
	 *
	 * @return array
	 */
	public function pro_action_links( $links ) {
		if ( isset( $links['pro'] ) ) {
			unset( $links['pro'] );
		}
		$custom = array();

		if ( isset( $links['settings'] ) ) {
			$custom['settings'] = $links['settings'];

			unset( $links['settings'] );
		}

		$custom['support'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>',
			wpcode_utm_url(
				'https://library.wpcode.com/account/support/',
				'all-plugins',
				'plugin-action-links',
				'support'
			),
			esc_attr__( 'Go to WPCode.com Support page', 'wpcode-premium' ),
			esc_html__( 'Support', 'wpcode-premium' )
		);

		return array_merge( $custom, (array) $links );
	}
}
