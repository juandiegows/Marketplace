<?php
/**
 * Vendor Verification Status Update Email.
 *
 * An email sent to the vendor after updating the verification status by admin.
 *
 * @since 3.7.23
 */

defined( 'ABSPATH' ) || exit;

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
// translators: 1: Store Name, 2: Newline character.
echo sprintf( __( 'Hi, %1$s, %2$s', 'dokan' ), wp_strip_all_tags( $store_name ), " \n\n" );
// translators: 1: Document Type, 2: Verification Status.
echo sprintf( __( 'Your %s verification request has been %s by the admin.', 'dokan' ), wp_strip_all_tags( $document_type ), wp_strip_all_tags( $verification_status ), " \n\n" );
// translators: 1: Home URL.
echo sprintf( __( 'You can check it out by going <a href="%s">here</a>.', 'dokan' ), esc_url( $home_url ), " \n\n" );
// translators: 1: Newline character.
echo sprintf( __( 'From: Admin %s', 'dokan' ), " \n\n" );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
