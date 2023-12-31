<?php
/**
 * class-'woocommerce-notify-updated-product'.php
 *
 * Copyright (c) Antonio Blanco http://www.eggemplo.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco (eggemplo)
 * @package woocommerce-notify-updated-product
 * @since woocommerce-notify-updated-product 1.0.0
 */

/**
 * WooNotifyUpdatedProduct class
 */
class WooNotifyUpdatedProduct {

	public static function init() {
		global $woocommerce;

		$enabled = get_option( 'wnup-enable', 1 );
		if ( $enabled ) {
			add_action('woocommerce_product_write_panel_tabs', array(__CLASS__,'woocommerce_product_write_panel_tabs') );

			if ( version_compare( $woocommerce->version, '3.0.0', '>=' ) ) {
				add_action('woocommerce_product_data_panels', array(__CLASS__,'woocommerce_product_data_panels') );
			} else {
				add_action('woocommerce_product_write_panels', array(__CLASS__,'woocommerce_product_data_panels') );
			}

			add_action('woocommerce_process_product_meta', array(__CLASS__,'woocommerce_process_product_meta') );
		}
	}

	public static function woocommerce_product_write_panel_tabs() {

		echo '<li class="notify_updated_product_tab general_options"><a href="#notify_updated_product_data">' . __( 'Notify Updated Product', 'woocommerce-notify-updated-product' ) . '</a></li>';

	}

	public static function woocommerce_product_data_panels() {
		global $post;

		$email_content = get_post_meta( $post->ID, 'wnup-content', true );

		?>
		<div id="notify_updated_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<p class="description">
					<?php echo __( 'Notify to the customers about this update', 'woocommerce-notify-updated-product' ); ?>
				</p>
			</div>

			<div class="options_notify_updated_product custom_tab_options">
				<table class="form-table">
					<tr valign="top">
					<th scope="row"><strong><?php echo __( 'Send notifications:', 'woocommerce-notify-updated-product' ); ?></strong></th>
					<td>
						<input type="checkbox" name="wnup-enable" value="1" />
						<span class="description"><?php _e("Notifications will be sent when you click on 'Update' button.", 'woocommerce-notify-updated-product');?></span>
					</tr>

					<tr valign="top">
						<th scope="row"><strong><?php echo __( 'Subject:', 'woocommerce-notify-updated-product' ); ?></strong></th>
						<td>
						<?php 
						$subject = trim( get_post_meta( $post->ID, 'wnup-subject', true ) );
						?>
						<input type="text" name="wnup-subject" value="<?php echo $subject; ?>" />
						<p style="clear:both;" >
						<span class="description"><?php _e('If empty, default title value will be used.', 'woocommerce-notify-updated-product');?></span>
						</p>
					</tr>

					<tr valign="top">
						<th scope="row"><strong><?php echo __( 'Email content:', 'woocommerce-notify-updated-product' ); ?></strong></th>
						<td>
							<textarea name="wnup-content" cols="50" rows="10" ><?php echo $email_content; ?></textarea>
							<p style="clear:both;" >
								<span class="description"><?php _e('If empty, default email content value will be used.', 'woocommerce-notify-updated-product');?></span>
							</p>
						</td>
					</tr>

				</table>
			</div>
		</div>
	<?php
	}

	public static function woocommerce_process_product_meta( $post_id ) {

		$_POST['wnup-subject'] = stripslashes( $_POST['wnup-subject'] );
		$_POST['wnup-content'] = stripslashes( $_POST['wnup-content'] );

		if ( isset( $_POST['wnup-content'] ) ) {
			update_post_meta( $post_id, 'wnup-content', ( isset($_POST['wnup-content']) && ( $_POST['wnup-content'] !== "" ) ) ? trim($_POST['wnup-content']) : '' );
		}
		if ( isset( $_POST['wnup-subject'] ) ) {
			update_post_meta( $post_id, 'wnup-subject', ( isset($_POST['wnup-subject']) && ( $_POST['wnup-subject'] !== "" ) ) ? trim($_POST['wnup-subject']) : '' );
		}

		if ( isset( $_POST['wnup-enable'] ) && ( $_POST['wnup-enable'] == 1 ) ) {
			self::notifyUsers( $post_id );
		} 
	}

	public static function notifyUsers ( $product_id ) {

		$users_email = self::get_customers_bought_product ( $product_id );

		if ( sizeof( $users_email ) > 0 ) {
			// check if user is subscribe
			$emails = array();
			foreach ( $users_email as $email ) {
				if ( $user = get_user_by( 'email',$email['email'] ) ) {
					if ( get_user_meta( $user->ID, 'woo_notify_product', true ) == 'yes' ) {
						$emails[] = $email['email'];
					}
					if ( get_user_meta( $user->ID, 'woo_notify_product', true ) == "" ) { // user has not this meta
						if ( get_option( "wnup-default", "yes" ) == 'yes' ) {
							$emails[] = $email['email'];
						}
					}
				} else {
					// guest is subscribe :-)
					$emails[] = $email['email'];
				}
			}
			// send emails
			self::sendEmails ( $emails, $product_id );
		}
	}

	public static function get_customers_bought_product ( $product_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare( "
					SELECT postmeta.meta_value as email
					FROM {$wpdb->prefix}woocommerce_order_items as order_items
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON order_items.order_item_id = itemmeta.order_item_id
					LEFT JOIN {$wpdb->postmeta} AS postmeta ON order_items.order_id = postmeta.post_id
					LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
					WHERE
					posts.post_status IN ( 'wc-completed', 'wc-processing' ) AND
					itemmeta.meta_value  = %s AND
					itemmeta.meta_key    IN ( '_variation_id', '_product_id' ) AND
					postmeta.meta_key    IN ( '_billing_email' )
					GROUP BY postmeta.meta_value 
				", $product_id
			),
			ARRAY_A
		);
	}

	public static function sendEmails ( $emails, $product_id ) {

		if ( is_array( $emails ) && ( sizeof( $emails ) > 0 ) ) {

			$from = trim( get_option( 'wnup-from', "" ) );
			if ( $from == "" ) {
				$from = get_bloginfo('admin_email');
			}
			// filter
			$from = apply_filters( 'woo_notify_updated_product_from_email', $from );

			$headers[] = 'From: ' . get_bloginfo('name') . " <" . $from . ">";
			$headers[] = 'Content-type: text/html';

			//$to = $emails;
			$to = array();
			foreach ( $emails as $bcc ) {
				if ( $bcc !== false ) {
					$headers[] = 'Bcc: ' . $bcc;
				}
			}

			$subject = trim( get_post_meta( $product_id, 'wnup-subject', true ) );
			if ( $subject == "" ) {
				$subject = trim( get_option( 'wnup-subject', "" ) );
			}
			if ( $subject == "" ) {
				$subject = get_bloginfo('name');
			}
			// filter
			$subject = apply_filters( 'woo_notify_updated_product_subject', $subject );

			$email_content = trim( get_post_meta( $product_id, 'wnup-content', true ) );
			if ( $email_content == "" ) {
				$email_content = trim( get_option( 'wnup-content', "" ) );
			}

			// tags content
			$tags = array();
			$tags['product_name'] = get_the_title($product_id);
			$tags['product_url'] = get_the_permalink($product_id);
			$tags['product_id'] = $product_id;

			$tags = apply_filters( 'woo_notify_updated_product_tags', $tags );

			foreach ( $tags as $key => $value ) {
					$email_content = str_replace( "[" . $key . "]", $value, $email_content );
			}

			// filter
			$email_content = apply_filters( 'woo_notify_updated_product_content', $email_content );

			@wp_mail( $to, $subject, $email_content, $headers );

		}
	}
}

WooNotifyUpdatedProduct::init();
