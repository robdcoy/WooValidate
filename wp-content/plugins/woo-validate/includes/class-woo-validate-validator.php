<?php
/**
 * WooValidate email validator.
 *
 * @package WooValidate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooValidate_Email_Validator' ) ) {
    /**
     * Email validation service.
     */
    class WooValidate_Email_Validator {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->disposable_domains = apply_filters( 'woo_validate_disposable_domains', $this->disposable_domains );
            $this->risky_addresses    = apply_filters( 'woo_validate_risky_addresses', $this->risky_addresses );
            $this->common_domains     = apply_filters( 'woo_validate_common_domains', $this->common_domains );
        }

        /**
         * Disposable email domains.
         *
         * @var array<string>
         */
        protected $disposable_domains = array(
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'tempmail.com',
            'yopmail.com',
        );

        /**
         * Known risky email addresses (commonly abused).
         *
         * @var array<string>
         */
        protected $risky_addresses = array(
            'test@example.com',
            'fake@example.com',
            'noreply@example.com',
        );

        /**
         * Common domains for suggestion comparison.
         *
         * @var array<string>
         */
        protected $common_domains = array(
            'gmail.com',
            'yahoo.com',
            'outlook.com',
            'hotmail.com',
            'icloud.com',
            'aol.com',
        );

        /**
         * Validate email address.
         *
         * @param string $email Email address.
         *
         * @return array{
         *     valid: bool,
         *     reason: string|null,
         *     suggestion: string|null
         * }
         */
        public function validate_email( $email ) {
            $email = trim( $email );

            if ( '' === $email ) {
                return array(
                    'valid'      => false,
                    'reason'     => __( 'Email address is required.', 'woo-validate' ),
                    'suggestion' => null,
                );
            }

            if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                return array(
                    'valid'      => false,
                    'reason'     => __( 'Please enter a valid email address.', 'woo-validate' ),
                    'suggestion' => $this->suggest_email( $email ),
                );
            }

            $domain = strtolower( substr( strrchr( $email, '@' ), 1 ) );

            if ( ! $this->has_valid_dns_records( $domain ) ) {
                return array(
                    'valid'      => false,
                    'reason'     => __( 'The email domain cannot receive messages. Please use a different email address.', 'woo-validate' ),
                    'suggestion' => $this->suggest_email( $email ),
                );
            }

            if ( $this->is_disposable_domain( $domain ) ) {
                return array(
                    'valid'      => false,
                    'reason'     => __( 'Disposable email addresses are not allowed. Please provide a permanent address.', 'woo-validate' ),
                    'suggestion' => null,
                );
            }

            if ( $this->is_risky_address( $email ) ) {
                return array(
                    'valid'      => false,
                    'reason'     => __( 'This email address is flagged as high risk. Please use another email.', 'woo-validate' ),
                    'suggestion' => null,
                );
            }

            return array(
                'valid'      => true,
                'reason'     => null,
                'suggestion' => null,
            );
        }

        /**
         * Determine if domain has valid DNS.
         *
         * @param string $domain Domain portion of email.
         * @return bool
         */
        protected function has_valid_dns_records( $domain ) {
            if ( '' === $domain ) {
                return false;
            }

            if ( function_exists( 'checkdnsrr' ) && ( checkdnsrr( $domain, 'MX' ) || checkdnsrr( $domain, 'A' ) ) ) {
                return true;
            }

            $records = dns_get_record( $domain, DNS_MX | DNS_A );

            return ! empty( $records );
        }

        /**
         * Check if email domain is disposable.
         *
         * @param string $domain Domain portion of email.
         * @return bool
         */
        protected function is_disposable_domain( $domain ) {
            return in_array( $domain, $this->disposable_domains, true );
        }

        /**
         * Check if email is risky.
         *
         * @param string $email Email address.
         * @return bool
         */
        protected function is_risky_address( $email ) {
            return in_array( strtolower( $email ), $this->risky_addresses, true );
        }

        /**
         * Suggest email correction.
         *
         * @param string $email Email address.
         * @return string|null
         */
        protected function suggest_email( $email ) {
            list( $local, $domain ) = array_pad( explode( '@', strtolower( $email ) ), 2, '' );

            if ( '' === $domain ) {
                return null;
            }

            $closest_domain = null;
            $shortest       = -1;

            foreach ( $this->common_domains as $common_domain ) {
                $distance = levenshtein( $domain, $common_domain );

                if ( $distance < 3 && ( $distance < $shortest || $shortest < 0 ) ) {
                    $shortest       = $distance;
                    $closest_domain = $common_domain;
                }
            }

            if ( null !== $closest_domain ) {
                return $local . '@' . $closest_domain;
            }

            return null;
        }
    }
}
