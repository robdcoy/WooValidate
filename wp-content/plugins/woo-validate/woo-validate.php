<?php
/**
 * Plugin Name: WooValidate Email Verification
 * Plugin URI:  https://example.com/woovalidate
 * Description: Advanced email validation for WooCommerce checkout and account flows. Performs syntax, domain, and reputation checks before orders are placed.
 * Version:     1.0.0
 * Author:      WooValidate
 * Author URI:  https://example.com
 * License:     GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woo-validate
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooValidate' ) ) {
    /**
     * Main plugin class.
     */
    class WooValidate {

        /**
         * Instance of the plugin.
         *
         * @var WooValidate|null
         */
        protected static $instance = null;

        /**
         * Plugin version.
         */
        const VERSION = '1.0.0';

        /**
         * Plugin slug.
         */
        const SLUG = 'woo-validate';

        /**
         * Create or retrieve instance.
         *
         * @return WooValidate
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * WooValidate constructor.
         */
        private function __construct() {
            $this->define_constants();
            $this->includes();
            $this->hooks();
        }

        /**
         * Define plugin constants.
         */
        protected function define_constants() {
            if ( ! defined( 'WOO_VALIDATE_VERSION' ) ) {
                define( 'WOO_VALIDATE_VERSION', self::VERSION );
            }

            if ( ! defined( 'WOO_VALIDATE_PATH' ) ) {
                define( 'WOO_VALIDATE_PATH', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WOO_VALIDATE_URL' ) ) {
                define( 'WOO_VALIDATE_URL', plugin_dir_url( __FILE__ ) );
            }
        }

        /**
         * Include required files.
         */
        protected function includes() {
            require_once WOO_VALIDATE_PATH . 'includes/class-woo-validate-validator.php';
            require_once WOO_VALIDATE_PATH . 'includes/class-woo-validate-checkout.php';
        }

        /**
         * Register hooks.
         */
        protected function hooks() {
            add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
            add_action( 'woocommerce_init', array( $this, 'boot_checkout_integration' ) );
        }

        /**
         * Load plugin textdomain.
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'woo-validate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * Boot WooCommerce integrations after WooCommerce is loaded.
         */
        public function boot_checkout_integration() {
            if ( ! class_exists( 'WooCommerce' ) ) {
                return;
            }

            if ( class_exists( 'WooValidate_Checkout' ) ) {
                new WooValidate_Checkout();
            }
        }
    }

    WooValidate::get_instance();
}
