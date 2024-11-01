<?php

/*
 * Plugin Name: WP Tao One Time Offer
 * Plugin URI: https://wordpress.org/plugins/wp-tao-one-time-offer/
 * Description: The plugin givs you ability to set any page as OTO.
 * Version: 1.0.1
 * Author: WP Tao Co.
 * Author URI: https://wptao.org
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WPTAO_OneTimeOffer' ) ) {

	final class WPTAO_OneTimeOffer {

		private static $instance;
		private $tnow;

		public static function get_instance() {
			if ( !isset( self::$instance ) && !( self::$instance instanceof WPTAO_OneTimeOffer ) ) {
				self::$instance = new WPTAO_OneTimeOffer;
				self::$instance->constants();
				self::$instance->includes();

				// Set up localisation
				self::$instance->load_textdomain();

				self::$instance->hooks();
			}
			self::$instance->tnow = time();

			return self::$instance;
		}

		/**
		 * Constructor Function
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Setup plugin constants
		 */
		private function constants() {

			$this->define( 'WPTAO_STORE_URL', 'http://wptao.org' );
			$this->define( 'WPTAO_ONETIMEOFFER_VERSION', '1.0.1' );   // Current version
			$this->define( 'WPTAO_ONETIMEOFFER_NAME', 'WP Tao OneTimeOffer' );   // Name
			$this->define( 'WPTAO_ONETIMEOFFER_FILE', __FILE__ );   // General plugin FILE
			$this->define( 'WPTAO_ONETIMEOFFER_DIR', plugin_dir_path( __FILE__ ) );  // Root plugin path
			$this->define( 'WPTAO_ONETIMEOFFER_DOMAIN', 'wp-tao-onetimeoffer' );   // Text Domain
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required WP Tao core files.
		 */
		public function includes() {
			if ( is_admin() ) {
				require_once WPTAO_ONETIMEOFFER_DIR . 'includes/admin/class-metabox.php';
			}
		}

		/**
		 * Actions and filters
		 */
		private function hooks() {
			if ( !class_exists( 'WP_Tracker_and_Optimizer' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_no_wptao' ) );
				return;
			}

			add_action( 'template_redirect', array( $this, 'oto_redirect' ), 0 );
		}

		/*
		 * Register text domain
		 */

		private function load_textdomain() {
			$lang_dir = dirname( plugin_basename( WPTAO_ONETIMEOFFER_FILE ) ) . '/languages/';
			load_plugin_textdomain( WPTAO_ONETIMEOFFER_DOMAIN, false, $lang_dir );
		}

		/**
		 * Admin notice: WP Tao is required
		 */
		public function admin_notice_no_wptao() {
			echo '<div class="error"><p>' . __( '<b>WP Tao One Time Offer</b>: please install <a href="http://wptao.org">WP Tao</a>.', WPTAO_ONETIMEOFFER_DOMAIN ) . '</p></div>';
		}

		/**
		 * main OTO funtion
		 */
		public function oto_redirect() {

			// disabled for super admin
			if ( is_super_admin() ) {
				return;
			}

			$page_id = get_the_ID();

			if ( empty( $page_id ) ) {
				return;
			}

			$wptao_oto_url = get_post_meta( $page_id, 'wptao-oto-url', true );

			if ( empty( $wptao_oto_url ) ) {
				return;
			}

			$args = array(
				'event_action'	 => array( 'pageview' ),
				'fingerprint_id' => TAO()->fingerprints->get_id(),
				'items_per_page' => 1,
				'meta_key'		 => 'post_id',
				'meta_value'	 => $page_id
			);

			$events = TAO()->events->get_events( $args );

			if ( !empty( $events ) ) {
				wp_redirect( $wptao_oto_url );
				exit;
			}
		}

	}

}

function wptao_onetimeoffer() {
	return WPTAO_OneTimeOffer::get_instance();
}

add_action( 'plugins_loaded', 'wptao_onetimeoffer' );

// on activate

function wptao_onetimeoffer_activate() {
	
}

register_activation_hook( __FILE__, 'wptao_onetimeoffer_activate' );
