<?php

namespace WeDevs\DokanPro\Shortcodes;

use WeDevs\Dokan\Abstracts\DokanShortcode;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BecomeAVendor extends DokanShortcode {
    /**
     * Shortcode name
     *
     * @since 3.7.25
     *
     * @var string Shortcode name
     */
    protected $shortcode = 'dokan-customer-migration';

    /**
     * Render best selling products
     *
     * @since 3.7.25
     *
     * @param array $atts
     *
     * @return string
     */
    public function render_shortcode( $atts ) {
        ob_start();
        dokan_get_template_part( 'account/update-customer-to-vendor', '' );
        wp_enqueue_script( 'dokan-vendor-registration' );
        return ob_get_clean();
    }
}
