<?php

namespace WeDevs\DokanPro\Modules\VendorVerification\Emails;

use WC_Email;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Vendor Verification Status Update Email.
 *
 * An email sent to the vendor after updating the verification status by admin.
 *
 * @class   DokanVendorVerificationStatusUpdate
 * @version 3.7.23
 * @author  weDevs
 * @extends WC_Email
 */
class StatusUpdate extends WC_Email {

    /**
     * Class Constructor.
     */
    public function __construct() {
        $this->id             = 'dokan_vendor_verification_status_update';
        $this->title          = __( 'Seller Verification Status Updated', 'dokan' );
        $this->description    = __( 'This email will be sent to the vendor after updating verification status by admin.', 'dokan' );
        $this->template_html  = '/emails/vendor-verification-status-update.php';
        $this->template_plain = '/emails/plain/vendor-verification-status-update.php';
        $this->template_base  = DOKAN_VERFICATION_TEMPLATE_DIR;
        $this->placeholders   = [
            '{verification_status}' => '',
            '{site_name}'           => '',
            '{site_url}'            => '',
        ];

        // Triggers for this email
        add_action( 'dokan_verification_status_change', [ $this, 'trigger' ], 20, 3 );

        // Call parent constructor
        parent::__construct();

        // Other settings
        $this->recipient = 'vendor@email.com';
    }

    /**
     * Get email subject.
     *
     * @since 3.7.23
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'Verification status {verification_status}', 'dokan' );
    }

    /**
     * Get email heading.
     *
     * @since 3.7.23
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Verification status {verification_status}', 'dokan' );
    }

    /**
     * Default content to show below main email content.
     *
     * @since 3.7.23
     *
     * @return string
     */
    public function get_default_additional_content() {
        return __( 'Thanks for using <a href="{site_url}">{site_name}</a>!', 'dokan' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @since 3.7.23
     *
     * @param int   $seller_id      Sellder id
     * @param array $seller_profile Sellder profile
     * @param array $postdata       Post data
     *
     * @return void
     */
    public function trigger( $user_id, $seller_profile, $postdata ) {
        if ( ! $this->is_enabled() ) {
            return;
        }

        if ( empty( $user_id ) || ! user_can( $user_id, 'dokandar' ) ) {
            return;
        }

        $user_data = get_userdata( $user_id );

        if ( empty( $user_data ) || empty( $user_data->data->user_email ) ) {
            return;
        }

        $verification_status = ! empty ( $postdata['status'] ) ? \WeDevs\DokanPro\Modules\VendorVerification\Module::get_translated_status( sanitize_text_field( wp_unslash( $postdata['status'] ) ) ) : '';
        $document_type       = ! empty ( $postdata['type'] ) ? sanitize_text_field( wp_unslash( $postdata['type'] ) ) : '';
        $admin_email         = get_option( 'admin_email' );
        $site_name           = get_bloginfo( 'name' );
        $site_url            = site_url();

        $this->placeholders = [
            '{verification_status}' => $verification_status,
            '{site_name}'           => $site_name,
            '{site_url}'            => $site_url,
        ];

        $this->data = [
            'store_name'          => $seller_profile['store_name'],
            'document_type'       => $document_type,
            'verification_status' => $verification_status,
            'home_url'            => $site_url,
            'admin_email'         => $admin_email,
        ];

        $this->setup_locale();
        $this->send( $user_data->data->user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @since  3.7.23
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        ob_start();
        wc_get_template(
            $this->template_html,
            [
                'email_heading'       => $this->get_heading(),
                'sent_to_admin'       => false,
                'plain_text'          => false,
                'email'               => $this,
                'store_name'          => $this->data['store_name'],
                'document_type'       => $this->data['document_type'],
                'verification_status' => $this->data['verification_status'],
                'home_url'            => $this->data['home_url'],
                'admin_email'         => $this->data['admin_email'],
                'additional_content'  => $this->get_additional_content(),
            ],
            'dokan/',
            $this->template_base
        );

        return ob_get_clean();
    }

    /**
     * Get content plain.
     *
     * @since  3.7.23
     *
     * @access public
     * @return string
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template(
            $this->template_plain,
            [
                'email_heading'       => $this->get_heading(),
                'sent_to_admin'       => false,
                'plain_text'          => true,
                'email'               => $this,
                'store_name'          => $this->data['store_name'],
                'document_type'       => $this->data['document_type'],
                'verification_status' => $this->data['verification_status'],
                'home_url'            => $this->data['home_url'],
                'admin_email'         => $this->data['admin_email'],
            ],
            'dokan/',
            $this->template_base
        );

        return ob_get_clean();
    }

    /**
     * Initialize settings form fields.
     *
     * @since 3.7.23
     *
     * @return void
     */
    public function init_form_fields() {
        $placeholder_text = sprintf(
        /* translators: %s: list of placeholders */
            __( 'Available placeholders: %s', 'dokan' ),
            '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>'
        );

        $this->form_fields = [
            'enabled'            => [
                'title'   => __( 'Vendor verification status update', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable email for vendor verification status update by admin', 'dokan' ),
                'default' => 'yes',
            ],
            'subject'            => [
                'title'       => __( 'Subject', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ],
            'heading'            => [
                'title'       => __( 'Email heading', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ],
            'additional_content' => [
                'title'       => __( 'Additional content', 'dokan' ),
                'description' => __( 'Text to appear below the main email content.', 'dokan' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'dokan' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ],
            'email_type'         => [
                'title'       => __( 'Email type', 'dokan' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'dokan' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ],
        ];
    }
}
