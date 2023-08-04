<?php
/**
 * Smart tags class for the pro version.
 *
 * @package WPCode
 */

/**
 * WPCode_Smart_Tags_Pro class.
 */
class WPCode_Smart_Tags_Pro extends WPCode_Smart_Tags {

	/**
	 * Add filters to replace the tags in the snippet code.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'wpcode_snippet_output_html', array( $this, 'replace_tags_in_snippet' ), 10, 2 );
		add_filter( 'wpcode_snippet_output_text', array( $this, 'replace_tags_in_snippet' ), 10, 2 );
		add_filter( 'wpcode_snippet_output_js', array( $this, 'replace_tags_in_snippet' ), 10, 2 );
		add_filter( 'wpcode_snippet_output_universal', array( $this, 'replace_tags_in_snippet' ), 10, 2 );
	}

	/**
	 * Method to get the id to avoid passing parameters to the core function.
	 *
	 * @return int
	 */
	public function get_the_ID() {
		return get_the_ID();
	}

	/**
	 * Method to get the title to avoid passing parameters to the core function.
	 *
	 * @return string
	 */
	public function get_the_title() {
		return get_the_title();
	}

	/**
	 * Get a comma-separated list of categories to replace the smart tag [categories].
	 *
	 * @return string
	 */
	public function tag_value_categories() {
		return wp_strip_all_tags( get_the_category_list( ',' ) );
	}

	/**
	 * Get the current user email.
	 *
	 * @return string
	 */
	public function tag_value_email() {
		return $this->get_user_detail( 'user_email' );
	}

	/**
	 * Get the first name tag.
	 *
	 * @return string
	 */
	public function tag_value_first_name() {
		return $this->get_user_detail( 'first_name' );
	}

	/**
	 * Get the last name tag.
	 *
	 * @return string
	 */
	public function tag_value_last_name() {
		return $this->get_user_detail( 'last_name' );
	}

	/**
	 * Get an user detail if loggedin.
	 *
	 * @param string $detail The key of the user detail.
	 *
	 * @return int|mixed|string
	 */
	private function get_user_detail( $detail ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user = wp_get_current_user();

		return isset( $user->$detail ) ? $user->$detail : '';
	}

	/**
	 * Check if WooCommerce is installed & active on the site.
	 *
	 * @return bool
	 */
	public function woocommerce_available() {
		return class_exists( 'woocommerce' );
	}

	/**
	 * Get the Woo order, if available.
	 *
	 * @return bool|WC_Order|WC_Order_Refund
	 */
	public function get_wc_order() {
		if ( ! $this->woocommerce_available() ) {
			return false;
		}

		global $wp;

		// Check cart class is loaded or abort.
		if ( is_null( WC()->cart ) ) {
			return false;
		}

		if ( empty( $wp->query_vars['order-received'] ) ) {
			return false;
		}

		$order_id = $wp->query_vars['order-received'];

		return wc_get_order( $order_id );
	}

	/**
	 * Return the WC order number, if available.
	 *
	 * @return string|int
	 */
	public function tag_value_wc_order_number() {
		$order = $this->get_wc_order();

		if ( ! $order ) {
			return '';
		}

		return $order->get_order_number();
	}

	/**
	 * Return the WC order subtotal,  if available.
	 *
	 * @return string|float
	 */
	public function tag_value_wc_order_subtotal() {
		$order = $this->get_wc_order();

		if ( ! $order ) {
			return '';
		}

		return $order->get_subtotal();
	}

	/**
	 * Return the WC order total, if available.
	 *
	 * @return string|float
	 */
	public function tag_value_wc_order_total() {
		$order = $this->get_wc_order();

		if ( ! $order ) {
			return '';
		}

		return $order->get_total();
	}

	/**
	 * Get the custom field value.
	 *
	 * @param array $parameters Array of extracted parameters.
	 *
	 * @return string
	 */
	public function tag_value_custom_field( $parameters = array() ) {
		if ( empty( $parameters['custom_field'] ) ) {
			return '';
		}

		// Let's see if we can find a meta with that key.
		$meta = get_post_meta( get_the_ID(), $parameters['custom_field'], true );

		// If we found a meta, let's return it.
		if ( ! empty( $meta ) ) {
			return $meta;
		}

		return '';
	}

	/**
	 * Replace smart tags in the code passed.
	 *
	 * @param string         $code The code to replace the smart tags in.
	 * @param WPCode_Snippet $snippet The snippet object.
	 * @param bool           $replace_old_format Whether to replace the old format of smart tags or not.
	 *
	 * @return string
	 */
	public function replace_tags_in_snippet( $code, $snippet = null, $replace_old_format = false ) {

		$tags = $this->get_tags_to_replace( $snippet );

		$smart_tags = $this->get_all_smart_tags( $code );

		foreach ( $smart_tags as $smart_tag_in_code => $smart_tag_key ) {
			$parameters = $this->get_parameters_from_tag( $smart_tag_in_code );
			if ( ! isset( $tags[ $smart_tag_key ] ) ) {
				continue;
			}
			$parameters['smart_tag_key'] = $smart_tag_key;
			$parameters['snippet']       = $snippet;

			$function = $tags[ $smart_tag_key ];

			$code = str_replace( $smart_tag_in_code, call_user_func( $function, $parameters ), $code );
		}

		if ( $replace_old_format ) {
			// The initial version of this used square brackets instead of curly braces.
			// We need to support that for backwards compatibility.
			foreach ( $tags as $tag => $function ) {
				$code = str_replace( '[' . $tag . ']', call_user_func( $function ), $code );
			}
		}

		return $code;
	}

	/**
	 * Get all smart tags in the content.
	 *
	 * @param string $content Content.
	 *
	 * @return array
	 * @since 1.6.7
	 *
	 */
	private function get_all_smart_tags( $content ) {

		/**
		 * A smart tag should start and end with a curly brace.
		 * ([a-z0-9_]+) a smart tag name and also the first capturing group. Lowercase letters, digits, and an  underscore.
		 * (|[ =][^\n}]*) - second capturing group:
		 * | no characters at all or the following:
		 * [ =][^\n}]* space or equal sign and any number of any characters except new line and closing curly brace.
		 */
		preg_match_all( '~{([a-z0-9_]+)(|[ =][^\n}]*)}~', $content, $smart_tags );

		return array_combine( $smart_tags[0], $smart_tags[1] );
	}

	/**
	 * Extract parameters from smart tag.
	 *
	 * @param string $tag The tag with parameters.
	 *
	 * @return array
	 */
	public function get_parameters_from_tag( $tag ) {
		preg_match_all( '/(\w+)=(["\'])(.+?)\2/', $tag, $attributes );
		$parameters = array_combine( $attributes[1], $attributes[3] );

		return $parameters;
	}

	/**
	 * Parse the tags data and return just tag > function pairs.
	 *
	 * @param WPCode_Snippet $snippet The snippet object.
	 *
	 * @return array
	 */
	public function get_tags_to_replace( $snippet = null ) {
		$tags_data = $this->get_tags();

		$tags = array();
		foreach ( $tags_data as $category_data ) {
			foreach ( $category_data['tags'] as $tag => $tag_details ) {
				$tags[ $tag ] = $tag_details['function'];
			}
		}

		if ( $snippet instanceof WPCode_Snippet && ! empty( $snippet->attributes ) ) {
			// Let's see if we have any shortcode attributes to use as smart tags.
			foreach ( $snippet->attributes as $attribute => $value ) {
				$tags[ 'attr_' . $attribute ] = array( $this, 'get_shortcode_attribute_value' );
			}
		}

		return $tags;
	}

	/**
	 * Upgrade notice data.
	 *
	 * @return array
	 */
	public function upgrade_notice_data() {
		if ( ! empty( wpcode()->license->get() ) ) {
			return array();
		}

		return array(
			'title'  => __( 'Smart Tags are a Premium feature', 'wpcode-premium' ),
			'text'   => __( 'Please add your license key in the Settings Panel to unlock all pro features.', 'wpcode-premium' ),
			'button' => __( 'Add License Key Now', 'wpcode-premium' ),
			'link'   => add_query_arg(
				array(
					'page' => 'wpcode-settings',
				),
				admin_url( 'admin.php' )
			),
		);
	}

	/**
	 * Get the id of the current post/page author.
	 *
	 * @return string
	 */
	public function tag_value_author_id() {
		return $this->get_author_detail( 'ID' );
	}

	/**
	 * Get the name of the current post/page author.
	 *
	 * @return string
	 */
	public function tag_value_author_name() {
		return $this->get_author_detail( 'display_name' );
	}

	/**
	 * Get the posts URL for the current post author.
	 *
	 * @return string
	 */
	public function tag_value_author_url() {
		return $this->get_author_detail( 'posts_url' );
	}

	/**
	 * Get the login URL.
	 *
	 * @return string
	 */
	public function tag_value_login_url() {
		return wp_login_url();
	}

	/**
	 * Get the logout URL.
	 *
	 * @return string
	 */
	public function tag_value_logout_url() {
		return wp_logout_url();
	}

	/**
	 * Get the detail of the current post/page author.
	 *
	 * @param string $detail The detail to get (id, name, etc) from WP_User.
	 *
	 * @return int|mixed|string
	 */
	public function get_author_detail( $detail ) {
		// Get the current post author from the global object.
		$author = get_post_field( 'post_author', get_the_ID() );

		// If we don't have an author, return an empty string.
		if ( ! $author ) {
			return '';
		}

		// Get the author details.
		$author_data = get_userdata( $author );

		if ( 'posts_url' === $detail ) {
			return get_author_posts_url( $author_data->ID );
		}

		// If we don't have the requested detail, return an empty string.
		if ( ! isset( $author_data->{$detail} ) ) {
			return '';
		}

		// Return the requested detail.
		return $author_data->{$detail};
	}

	/**
	 * Get the shortcode attribute value for the current snippet.
	 *
	 * @param array $parameters Parameters passed to the smart tag.
	 *
	 * @return string
	 */
	public function get_shortcode_attribute_value( $parameters = array() ) {
		if ( isset( $parameters['snippet'] ) && isset( $parameters['smart_tag_key'] ) && $parameters['snippet'] instanceof WPCode_Snippet ) {
			$snippet = $parameters['snippet'];
			$key     = substr_replace( $parameters['smart_tag_key'], '', 0, 5 );

			if ( isset( $snippet->attributes[ $key ] ) ) {
				return $snippet->attributes[ $key ];
			}
		}

		return '';
	}
}
