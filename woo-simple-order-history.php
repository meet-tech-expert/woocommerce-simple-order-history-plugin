<?php
/**
 * Plugin Name: Woo Simple Order History
 * Plugin URI: 
 * Description: Track customer order history with their order.
 * Version: 1.0.0
 * Author: Rinkesh Gupta
 * Author URI: https://github.com/meet-tech-expert/
 * License: GPL2
 * Text Domain: woo-simple-order-history
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Main plugin instantiation class.
 *
 * @since 1.0.0
 */
class WooSimple_Order_History {

	/**
	 * @var Single instance of the WCCH class.
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	var $version = '1.0.0';

	/**
	 * Main WooSimple_Order_History Instance
	 *
	 * Ensures only one instance of WooSimple_Order_History is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return WooSimple_Order_History - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Fire up the engines.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Define plugin constants
		$this->plugin_file    = __FILE__;
		$this->basename       = plugin_basename( $this->plugin_file );
		$this->directory_path = plugin_dir_path( $this->plugin_file );
		$this->directory_url  = plugin_dir_url( $this->plugin_file );

		// Handle plugin activation and deactivation
		register_activation_hook( $this->plugin_file, array( $this, 'activation' ) );
		register_deactivation_hook( $this->plugin_file, array( $this, 'deactivation' ) );

		// Basic setup
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );
		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
		add_action( 'plugins_loaded', array( $this, 'includes' ) );
		//add_action( 'plugins_loaded', array( $this, 'maybe_update_plugin' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

	}

	/**
	 * Plugin activation hook.
	 *
	 * @since  1.2.0
	 */
	public function activation() {
		$this->includes();
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @since  1.2.0
	 */
	public function deactivation() {
		$this->includes();
	}

	/**
	 * Load localization.
	 *
	 * @since 1.0.0
	 */
	public function i18n() {
		load_plugin_textdomain( 'woo-simple-order-history', false, $this->directory_path . '/languages/' );
	} /* i18n() */

	/**
	 * Include file dependencies.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		if ( $this->meets_requirements() ) {
			require_once( $this->directory_path . '/includes/show-history.php' );
		}
	} /* includes() */

	/**
	 * Register JS files.
	 *
	 * @since 1.2.0
	 */
	public function load_scripts() {
		/*wp_enqueue_script( 'wcch-tracking', $this->directory_url . 'assets/js/tracking.js', array( 'jquery' ), '1.2.0' );
		wp_localize_script( 'wcch-tracking', 'wcch', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'currentUrl' => home_url( add_query_arg( null, null ) ),
		) );
		*/
	}

	/**
	 * Check if all requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if requirements are met, otherwise false.
	 */
	private function meets_requirements() {
		return ( class_exists( 'WooCommerce' ) && version_compare( WC()->version, '2.1.0', '>=' ) );
	} /* meets_requirements() */

	/**
	 * Output error message and disable plugin if requirements are not met.
	 *
	 * This fires on admin_notices.
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {

		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'WooSimple Order History requires WooCommerce 2.1.0 or greater and has been <a href="%s">deactivated</a>. Please install, activate or update WooCommerce and then reactivate this plugin.', 'woo-simple-order-history' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}

	} /* maybe_disable_plugin() */

	/**
	 * Run an update routine for the plugin.
	 *
	 * @since 1.2.0
	 */
	function maybe_update_plugin() {

		// Bail early if not on an admin page
		if ( ! is_admin() ) {
			return;
		}

		// Get the stored and current plugin database versions
		$stored_db_version = get_option( 'wcch_plugin_db_version', '0.0.0' );

		// Only trigger updates when stored version is lower than current version
		if ( version_compare( $stored_db_version, $this->version, '<' ) ) {
			require_once( $this->directory_path . '/includes/updates.php' );
			do_action( 'wcch_plugin_update', $stored_db_version, $this->version );
			update_option( 'wcch_plugin_db_version', $this->version );
		}

	} /* maybe_update_plugin() */
}

/**
 * Returns the main instance of WCCH.
 *
 * @since  1.0.0
 * @return WooCommerce
 */
function woosimple_order_history() {
	return WooSimple_Order_History::instance();
}

woosimple_order_history();
