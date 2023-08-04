<?php
/**
 * Class that handles conditional logic for device type
 *
 * @package WPCode
 */

/**
 * The WPCode_Conditional_Device class.
 */
class WPCode_Conditional_Device extends WPCode_Conditional_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'device';

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Device Type', 'wpcode-premium' );
	}

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'device_type' => array(
				'label'    => __( 'Device Type', 'wpcode-premium' ),
				'type'     => 'select',
				'options'  => array(
					array(
						'label' => __( 'Desktop', 'wpcode-premium' ),
						'value' => 'desktop',
					),
					array(
						'label' => __( 'Mobile', 'wpcode-premium' ),
						'value' => 'mobile',
					),
				),
				'callback' => array( $this, 'get_device_type' ),
			),
		);
		if ( is_admin() ) {
			if ( ! wpcode()->license->get() ) {
				$this->options['device_type']['upgrade'] = array(
					'title'  => __( 'Device Type Rules are a Pro Feature', 'wpcode-premium' ),
					'text'   => __( 'Please add your license key in the Settings Panel to unlock all pro features.', 'wpcode-premium' ),
					'link'   => add_query_arg(
						array(
							'page' => 'wpcode-settings',
						),
						admin_url( 'admin.php' )
					),
					'button' => __( 'Add License Key Now', 'wpcode-premium' ),
				);
			}
		}
	}

	/**
	 * Get the Device type
	 *
	 * @return string
	 */
	public function get_device_type() {
		return wp_is_mobile() ? 'mobile' : 'desktop';
	}
}

new WPCode_Conditional_Device();
