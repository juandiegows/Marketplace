<?php
/**
 * Class used to execute snippets in the Pro plugin.
 *
 * @package WPCode
 */

/**
 * WPCode_Snippet_Execute_Pro class.
 */
class WPCode_Snippet_Execute_Pro extends WPCode_Snippet_Execute {

	/**
	 * Load the classes and options available for executing code.
	 *
	 * @return void
	 */
	public function load_types() {
		parent::load_types();
		// Class for handling block snippets.
		require_once WPCODE_PLUGIN_PATH . 'includes/pro/execute/class-wpcode-snippet-execute-blocks.php';

		$this->types['blocks'] = array(
			'class'  => 'WPCode_Snippet_Execute_Blocks',
			'label'  => __( 'Blocks Snippet', 'wpcode-premium' ),
			'is_pro' => true,
		);
	}

	/**
	 * Pro types are always for licensed users.
	 *
	 * @param string $key The type key.
	 *
	 * @return false
	 */
	public function is_type_pro( $key ) {
		if ( empty( wpcode()->license->get() ) ) {
			return parent::is_type_pro( $key );
		}

		return false;
	}
}
