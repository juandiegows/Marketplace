<?php

namespace WeDevs\DokanPro;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Hooks class
 *
 * @since 3.7.25
 */
class Hooks {
    /**
     * Class constructor
     *
     * @since 3.7.25
     */
    public function __construct() {
        add_filter( 'dokan_withdraw_disable', [ $this, 'withdraw_disable_withdraw_operation' ], 3 );
        add_filter( 'dokan_prepare_for_calculation', [ $this, 'add_combine_commission' ], 10, 6 );
        add_action( 'dokan_before_update_vendor', [ $this, 'save_admin_additional_commission' ], 10, 2 );
    }

    /**
     * Disable entire Dokan withdraw mechanism.
     *
     * @since 3.7.25 moved from includes/functions.php to here
     *
     * @param bool $is_disabled
     *
     * @return bool
     */
    public function withdraw_disable_withdraw_operation( $is_disabled ) {
        if ( $is_disabled ) {
            return $is_disabled;
        }

        return 'on' === dokan_get_option( 'hide_withdraw_option', 'dokan_withdraw', 'off' );
    }

    /**
     * Dokan add combine commission
     *
     * @since  2.9.14
     * @since  3.7.25 moved from includes/functions.php to here
     *
     * @param float  $earning [earning for a vendor or admin]
     * @param float  $commission_rate
     * @param string $commission_type
     * @param float  $additional_fee
     * @param float  $product_price
     * @param int    $order_id
     *
     * @return float
     */
    public function add_combine_commission( $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id ) {
        if ( 'combine' === $commission_type ) {
            // vendor will get 100 percent if commission rate > 100
            if ( $commission_rate > 100 ) {
                return (float) wc_format_decimal( $product_price );
            }

            // If `_dokan_item_total` returns `non-falsy` value that means, the request comes from the `order refund request`.
            // So modify `additional_fee` to the correct amount to get refunded. (additional_fee/item_total)*product_price.
            // Where `product_price` means item_total - refunded_total_for_item.
            $item_total    = get_post_meta( $order_id, '_dokan_item_total', true );
            $product_price = (float) wc_format_decimal( $product_price );
            if ( $order_id && $item_total ) {
                $order          = wc_get_order( $order_id );
                $additional_fee = ( $additional_fee / $item_total ) * $product_price;
            }

            $earning       = ( (float) $product_price * $commission_rate ) / 100;
            $total_earning = $earning + $additional_fee;
            $earning       = (float) $product_price - $total_earning;
        }

        return floatval( wc_format_decimal( $earning ) );
    }

    /**
     * Dokan save admin additional_fee
     *
     * @since 2.9.14
     * @since 3.7.25 moved from includes/functions.php to here
     *
     * @param int   $vendor_id
     * @param array $data
     *
     * @return void
     */
    public function save_admin_additional_commission( $vendor_id, $data ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( isset( $data['admin_additional_fee'] ) ) {
            $vendor = dokan()->vendor->get( $vendor_id );
            $vendor->update_meta( 'dokan_admin_additional_fee', wc_format_decimal( $data['admin_additional_fee'] ) );
        }
    }
}


