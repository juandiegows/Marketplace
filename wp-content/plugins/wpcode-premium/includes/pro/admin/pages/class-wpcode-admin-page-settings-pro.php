<?php
/**
 * Pro-specific settings admin page.
 *
 * @package WPCode
 */

/**
 * Pro-specific settings admin page.
 */
class WPCode_Admin_Page_Settings_Pro extends WPCode_Admin_Page_Settings {

	/**
	 * Extend the settings page with pro-specific fields.
	 *
	 * @return void
	 */
	public function output_content() {
		$this->metabox_row(
			__( 'License Key', 'wpcode-premium' ),
			$this->get_license_key_field(),
			'wpcode-setting-license-key'
		);

		$this->common_settings();

		wp_nonce_field( $this->action, $this->nonce_name );

	}

	/**
	 * License key field for the Pro settings page.
	 *
	 * @return false|string
	 */
	public function get_license_key_field() {
		$license      = (array) get_option( 'wpcode_license', array() );
		$key          = ! empty( $license['key'] ) ? $license['key'] : '';
		$type         = ! empty( $license['type'] ) ? $license['type'] : '';
		$is_valid_key = ! empty( $key ) &&
		                ( isset( $license['is_expired'] ) && $license['is_expired'] === false ) &&
		                ( isset( $license['is_disabled'] ) && $license['is_disabled'] === false ) &&
		                ( isset( $license['is_invalid'] ) && $license['is_invalid'] === false );

		$hide        = $is_valid_key ? '' : 'wpcode-hide';
		$account_url = wpcode_utm_url(
			'https://library.wpcode.com/account/downloads/',
			'settings-page',
			'license-key',
			'account'
		);

		ob_start();
		?>
		<span class="wpcode-setting-license-wrapper">
			<input type="password" id="wpcode-setting-license-key" value="<?php echo esc_attr( $key ); ?>" class="wpcode-input-text" <?php disabled( $is_valid_key ); ?>>
		</span>
		<button type="button" id="wpcode-setting-license-key-verify" class="wpcode-button <?php echo $is_valid_key ? 'wpcode-hide' : ''; ?>"><?php esc_html_e( 'Verify Key', 'wpcode-premium' ); ?></button>
		<button type="button" id="wpcode-setting-license-key-deactivate" class="wpcode-button <?php echo esc_attr( $hide ); ?>"><?php esc_html_e( 'Deactivate Key', 'wpcode-premium' ); ?></button>
		<button type="button" id="wpcode-setting-license-key-deactivate-force" class="wpcode-button wpcode-hide"><?php esc_html_e( 'Force Deactivate Key', 'wpcode-premium' ); ?></button>
		<p class="type <?php echo esc_attr( $hide ); ?>">
			<?php
			printf(
			/* translators: %s: the license type */
				esc_html__( 'Your license key level is %s.', 'wpcode-premium' ),
				'<strong>' . esc_html( $type ) . '</strong>'
			);
			?>
		</p>
		<p>
			<?php
			printf(
			/* translators: %1$s: opening link tag, %2$s: closing link tag */
				esc_html__( 'You can find your license key in your %1$sWPCode account%2$s.', 'wpcode-premium' ),
				'<a href="' . esc_url( $account_url ) . '" target="_blank">',
				'</a>'
			);
			?>
		</p>
		<?php

		return ob_get_clean();
	}
}
