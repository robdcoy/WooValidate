<?php
/**
 * WooValidate checkout integration.
 *
 * @package WooValidate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooValidate_Checkout' ) ) {
    /**
     * Handles WooCommerce checkout validation hooks.
     */
    class WooValidate_Checkout {

        /**
         * Validator service instance.
         *
         * @var WooValidate_Email_Validator
         */
        protected $validator;

        /**
         * Constructor.
         */
        public function __construct() {
            $this->validator = new WooValidate_Email_Validator();

            add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_email' ) );
            add_action( 'woocommerce_after_checkout_validation', array( $this, 'suggest_checkout_email' ), 10, 2 );
            add_action( 'woocommerce_register_post', array( $this, 'validate_registration_email' ), 10, 3 );
            add_filter( 'woocommerce_email_customer_details_fields', array( $this, 'flag_risky_email_in_order' ), 10, 3 );
        }

        /**
         * Validate checkout email before order submission.
         */
        public function validate_checkout_email() {
            if ( empty( $_POST['billing_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                return;
            }

            $email   = sanitize_email( wp_unslash( $_POST['billing_email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
            $result  = $this->validator->validate_email( $email );
            $context = __( 'billing', 'woo-validate' );

            if ( ! $result['valid'] ) {
                $message = sprintf(
                    /* translators: %1$s is the reason message. */
                    __( 'There is an issue with the %s email address: %s', 'woo-validate' ),
                    $context,
                    $result['reason']
                );

                if ( ! empty( $result['suggestion'] ) ) {
                    $message .= ' ' . sprintf(
                        /* translators: %s is the suggested email address. */
                        __( 'Did you mean %s?', 'woo-validate' ),
                        '<strong>' . esc_html( $result['suggestion'] ) . '</strong>'
                    );
                }

                wc_add_notice( wp_kses_post( $message ), 'error' );
            }
        }

        /**
         * Add suggestion notice after validation if available.
         *
         * @param array    $data   Posted data.
         * @param WP_Error $errors Validation errors.
         */
        public function suggest_checkout_email( $data, $errors ) {
            if ( ! empty( $errors->get_error_codes() ) ) {
                return;
            }

            $email  = isset( $data['billing_email'] ) ? sanitize_email( $data['billing_email'] ) : '';
            $result = $this->validator->validate_email( $email );

            if ( $result['valid'] || empty( $result['suggestion'] ) ) {
                return;
            }

            wc_add_notice(
                sprintf(
                    /* translators: %s is the suggested email address. */
                    __( 'We had trouble with your email address. Try %s if it was a typo.', 'woo-validate' ),
                    '<strong>' . esc_html( $result['suggestion'] ) . '</strong>'
                ),
                'notice'
            );
        }

        /**
         * Validate registration email during account creation.
         *
         * @param string   $username Submitted username.
         * @param string   $email    Submitted email.
         * @param WP_Error $errors   Error object.
         */
        public function validate_registration_email( $username, $email, $errors ) {
            unset( $username );

            $result = $this->validator->validate_email( $email );

            if ( ! $result['valid'] ) {
                $errors->add( 'woo_validate_email', $result['reason'] );
            }
        }

        /**
         * Add customer note when risky email is detected in orders.
         *
         * @param array    $fields        Email fields.
         * @param bool     $sent_to_admin Whether sent to admin.
         * @param WC_Order $order         Order object.
         *
         * @return array
         */
        public function flag_risky_email_in_order( $fields, $sent_to_admin, $order ) {
            unset( $sent_to_admin );

            $email = $order instanceof WC_Order ? $order->get_billing_email() : '';

            if ( empty( $email ) ) {
                return $fields;
            }

            if ( $this->validator->validate_email( $email )['valid'] ) {
                return $fields;
            }

            $fields['woo_validate_warning'] = array(
                'label' => __( 'Email validation', 'woo-validate' ),
                'value' => __( 'This order used an email address that failed validation checks.', 'woo-validate' ),
            );

            return $fields;
        }
    }
}
