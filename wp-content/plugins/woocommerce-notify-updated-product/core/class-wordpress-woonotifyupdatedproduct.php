<?php

class Wordpress_WooNotifyUpdatedProduct {

	public static function init() {

		add_action( 'show_user_profile', array( __CLASS__, 'edit_user_profile' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'edit_user_profile' ) );

		add_action( 'personal_options_update', array( __CLASS__, 'edit_user_profile_update' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'edit_user_profile_update' ) );

	}

	public static function edit_user_profile( $user ) {

		$val = get_the_author_meta( 'woo_notify_product', $user->ID );
		$selectedNo = "";
		$selectedYes = "";
		if ( ( $val == 'no' ) || ( get_option( "wnup-default", "yes" ) == 'no' ) ) {
			$selectedYes = "";
			$selectedNo = "selected";
		} else {
			$selectedYes = "selected";
			$selectedNo = "";
		}
		$output = '<h3>'. __('Products notifications', 'woocommerce-notify-updated-product') . '</h3>
					<table class="form-table">
						<tr>
							<th><label>' . __('Notify me', 'woocommerce-notify-updated-product') . '</label></th>
							<td>
								<select name="woo_notify_product" id="woo_notify_product">
									<option value="yes" ' . $selectedYes . ' >' . __('Yes','woocommerce-notify-updated-product') . '</option>
									<option value="no" ' . $selectedNo . ' >' . __('No', 'woocommerce-notify-updated-product') . '</option>
								</select><br>
								<span class="description">' . __('Notify me when my products are updated.', 'woocommerce-notify-updated-product') . '</span>
							</td>
						</tr>
					</table>';

		echo $output;
	}

	public static function edit_user_profile_update( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		if ( isset( $_POST['woo_notify_product'] ) ) {
			update_usermeta( $user_id, 'woo_notify_product', $_POST['woo_notify_product'] );
		}
	}
}

Wordpress_WooNotifyUpdatedProduct::init();