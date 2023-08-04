<?php
/**
 * woo-notify-updated-product.php
 *
 * Copyright (c) 2011,2012 Antonio Blanco http://www.eggemplo.com
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
 *
 * Plugin Name: Woocommerce Notify Updated Product
 * Plugin URI: https://www.eggemplo.com/plugin/woocommerce-notify-updated-product/
 * Description: Notify customers when their products are updated.
 * Version: 1.5.3
 * Author: eggemplo
 * Author URI: http://www.eggemplo.com
 * Text Domain: woocommerce-notify-updated-product
 * Domain Path: /languages
 * License: GPLv3
 */

define( 'WOO_NOTIFY_UPDATED_PRODUCT_PLUGIN_NAME', 'woo-notify-updated-product' );

define( 'WOO_NOTIFY_UPDATED_PRODUCT_FILE', __FILE__ );

if ( !defined( 'WOO_NOTIFY_UPDATED_PRODUCT_CORE_DIR' ) ) {
	define( 'WOO_NOTIFY_UPDATED_PRODUCT_CORE_DIR', WP_PLUGIN_DIR . '/woo-notify-updated-product/core' );
}

class WooNotifyUpdatedProduct_Plugin {

	private static $notices = array();

	public static function init() {

		load_plugin_textdomain( 'woocommerce-notify-updated-product', null, WOO_NOTIFY_UPDATED_PRODUCT_PLUGIN_NAME . '/languages' );

		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );

	}

	public static function wp_init() {

		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}

		$woo_is_active = in_array( 'woocommerce/woocommerce.php', $active_plugins );

		if ( !$woo_is_active ) {
			if ( !$woo_is_active ) {
				self::$notices[] = "<div class='error'>" . __( 'The <strong>Woocommerce Notify Updated Product</strong> plugin requires the <a href="http://wordpress.org/extend/plugins/woocommerce" target="_blank">Woocommerce</a> plugin to be activated.', 'woocommerce-notify-updated-product' ) . "</div>";
			} 
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( array( __FILE__ ) );
		} else {
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );
			//call register settings function
			add_action( 'admin_init', array( __CLASS__, 'register_woocommerce_notify_updated_product_settings' ) );

			if ( !class_exists( "WooNotifyUpdatedProduct" ) ) {
					include_once 'core/class-woonotifyupdatedproduct.php';
				}
			if ( !class_exists( "Wordpress_WooNotifyUpdatedProduct" ) ) {
				include_once 'core/class-wordpress-woonotifyupdatedproduct.php';
			}
		}

	}

	public static function register_woocommerce_notify_updated_product_settings() {

	}

	public static function admin_notices() { 
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		add_submenu_page(
				'woocommerce',
				__( 'Notify Updated Product' ),
				__( 'Notify Updated Product' ),
				'manage_options',
				'woocommerce-notify-updated-product',
				array( __CLASS__, 'woocommerce_notify_updated_product_settings' )
		);
	}

	public static function woocommerce_notify_updated_product_settings () {
	?>
	<div class="wrap">
	<h2><?php echo __( 'Woocommerce Notify Updated Product', 'woocommerce-notify-updated-product' ); ?></h2>
	<?php 
	$alert = "";

	if ( isset( $_POST['submit'] ) ) {
		$alert = __("Saved", 'woocommerce-notify-updated-product');

		if ( isset( $_POST[ "enable" ] ) ) {
			add_option( "wnup-enable",$_POST[ "enable" ] );
			update_option( "wnup-enable", $_POST[ "enable" ] );
		} else {
			add_option( "wnup-enable", 0 );
			update_option( "wnup-enable", 0 );
		}

		$_POST['from'] = stripslashes( $_POST['from'] );
		$_POST['subject'] = stripslashes( $_POST['subject'] );
		$_POST['content'] = stripslashes( $_POST['content'] );

		add_option( "wnup-email",$_POST[ "from" ] );
		update_option( "wnup-email", $_POST[ "from" ] );

		add_option( "wnup-subject",$_POST[ "subject" ] );
		update_option( "wnup-subject", $_POST[ "subject" ] );

		add_option( "wnup-content",$_POST[ "content" ] );
		update_option( "wnup-content", $_POST[ "content" ] );

		add_option( "wnup-default",$_POST[ "default" ] );
		update_option( "wnup-default", $_POST[ "default" ] );

	}

	if ($alert != "") {
		echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
	}

	?>
	<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
	<form method="post" action="">
	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row"><strong><?php echo __( 'Enable:', 'woocommerce-notify-updated-product' ); ?></strong></th>
	        <td>
	        	<?php 
				$enable = get_option( "wnup-enable", 1 );
				$checked = "";
				if ( $enable ) {
					$enable = "1";
					$checked = "checked";
				} else {
					$enable = "0";
				}
				?>
				<input type="checkbox" name="enable" value="1" <?php echo $checked; ?> />

			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'Default user status value:', 'woocommerce-notify-updated-product' ); ?></strong></th>
			<td>
			<?php 
				$status = get_option( "wnup-default", "yes" );
				$selectedYes = "";
				$selectedNo = "selected";
				if ( $status == "yes" ) {
					$selectedYes = "selected";
					$selectedNo = "";
				}
			?>
				<select name="default">
					<option value="yes" <?php echo $selectedYes;?> ><?php _e('Enabled', 'woocommerce-notify-updated-product')?></option>
					<option value="no" <?php echo $selectedNo;?> ><?php _e('Disabled','woocommerce-notify-updated-product');?></option>
				</select>
				<p>
				<span class="description"><?php _e('Default notifications status on products.','woocommerce-notify-updated-product');?></span>
				</p>
			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'From email:', 'woocommerce-notify-updated-product' ); ?></strong></th>
			<td>
			<?php 
				$from = get_option( "wnup-email", "" );
				if ( $from == "" ) {
					$from = get_bloginfo('admin_email');
				}
			?>
				<input type="text" name="from" value="<?php echo $from; ?>" />
				<p>
				<span class="description"><?php _e('If empty, admin email will be used.','woocommerce-notify-updated-product');?></span>
				</p>
			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'Subject:', 'woocommerce-notify-updated-product' ); ?></strong></th>
			<td>
			<?php 
				$subject = get_option( "wnup-subject", "" );
				if ( $subject == "" ) {
					$subject = get_bloginfo('name');
				}
			?>
				<input type="text" name="subject" value="<?php echo $subject; ?>" size="52" />
				<p>
				<span class="description"><?php _e('If empty, blog name will be used.','woocommerce-notify-updated-product');?></span>
				</p>
			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'Default email content:', 'woocommerce-notify-updated-product' ); ?></strong></th>
			<td>
				<textarea name="content" cols="50" rows="10" ><?php echo get_option( "wnup-content", "" ); ?></textarea>
				<p>
				<span class="description"><?php _e('You can use [product_name] to display the product name.','woocommerce-notify-updated-product');?></span>
				<span class="description"><?php _e('You can use [product_url] to display the product url.','woocommerce-notify-updated-product');?></span>
				</p>
			</tr>

		</table>

		<?php submit_button( __( "Save", 'woocommerce-notify-updated-product' ) ); ?>

		<?php settings_fields( 'woocommerce-notify-updated-product' ); ?>

	</form>

	</div>
	</div>
	<?php 
	}


}
WooNotifyUpdatedProduct_Plugin::init();
