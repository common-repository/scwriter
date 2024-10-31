<?php

namespace SCwriter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\API\SCwriter_Posts;
use SCwriter\API\SCwriter_Settings;
use SCwriter\API\SCwriter_Presets;
use SCwriter\SCwriter_Dashboard;
use SCwriter\SCwriter_CPT;
use SCwriter\SCwriter_Cron;

class SCwriter_WP{

	function __construct() {
		$dashboard = new SCwriter_Dashboard();
		SCwriter_CPT::init();
		SCwriter_Cron::init();
		$this->register_hook_callbacks();
	}

	/**
	 * Enqueues CSS, JavaScript, etc
	 */
	public function load_resources() {

		wp_enqueue_script(
			SCWRITER_PREFIX . '_js',
			plugins_url( 'javascript/' . SCWRITER_PREFIX . '.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			filemtime(__DIR__.'/../javascript/' . SCWRITER_PREFIX . '.js'),
			true
		);
		wp_localize_script(SCWRITER_PREFIX.'_js', SCWRITER_PREFIX.'_ajax',array(
			'url' => admin_url('admin-ajax.php')
		));

		wp_enqueue_script(
			SCWRITER_PREFIX . '_dashboard_js',
			plugins_url( 'javascript/' . SCWRITER_PREFIX . '_dashboard.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			filemtime(__DIR__.'/../javascript/' . SCWRITER_PREFIX . '_dashboard.js'),
			true
		);

		wp_enqueue_script(
			SCWRITER_PREFIX . '_select2_js',
			plugins_url( 'javascript/vendor/select2.full.min.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			filemtime(__DIR__.'/../javascript/vendor/select2.full.min.js'),
			true
		);

		wp_enqueue_script(
			SCWRITER_PREFIX . '_editor_js',
			plugins_url( 'javascript/vendor/editorjs.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			filemtime(__DIR__.'/../javascript/vendor/editorjs.js'),
			true
		);

		wp_enqueue_style(
			SCWRITER_PREFIX . '_styles',
			plugins_url( 'css/' . SCWRITER_PREFIX . '.css', dirname( __FILE__ ) ),
			array(),
			filemtime(__DIR__.'/../css/' . SCWRITER_PREFIX . '.css'),
			'all'
		);

		wp_enqueue_style(
			SCWRITER_PREFIX . '_select2_css',
			plugins_url( 'css/vendor/select2.min.css', dirname( __FILE__ ) ),
			array(),
			filemtime(__DIR__.'/../css/vendor/select2.min.css'),
			'all'
		);

		wp_enqueue_script(
			SCWRITER_PREFIX . '_heartbeat_js',
			plugins_url( 'javascript/scwriter_heartbeat.js', dirname( __FILE__ ) ),
			array( 'jquery', 'heartbeat' ),
			filemtime(__DIR__.'/../javascript/scwriter_heartbeat.js'),
			true
		);

	}

	/**
	 * Prepares sites to use the plugin during single or network-wide activation
	 *
	 * @param bool $network_wide
	 */
	public function activate( $network_wide ) {
		if ( $network_wide && is_multisite() ) {
			$sites = get_sites( array( 'limit' => false ) );

			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				$this->single_activate( $network_wide );
				restore_current_blog();
			}
		} else {
			$this->single_activate( $network_wide );
		}
	}

	/**
	 * Runs activation code on a new WPMS site when it's created
	 *
	 * @param int $blog_id
	 */
	public function activate_new_site( $blog_id ) {
		switch_to_blog( $blog_id );
		$this->single_activate( true );
		restore_current_blog();
		flush_rewrite_rules();
	}

	/**
	 * Prepares a single blog to use the plugin
	 *
	 * @mvc Controller
	 *
	 * @param bool $network_wide
	 */
	protected function single_activate( $network_wide ) {
		flush_rewrite_rules();
	}

	/**
	 * Rolls back activation procedures when de-activating the plugin
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Register callbacks for actions and filters
	 *
	 * @mvc Controller
	 */
	public function register_hook_callbacks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_resources' ) );

		add_action( 'wpmu_new_blog',         array( $this, 'activate_new_site' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_get_article_info', array( $this, 'handle_scwriter_get_article_info' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_create_scwriter_post', array( $this, 'handle_scwriter_create_scwriter_post' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_update_settings', array( $this, 'handle_scwriter_update_settings_ajax' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_connect', array( $this, 'handle_scwriter_connect_ajax' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_wizard_add_scwriter', array( $this, 'handle_scwriter_wizard_add_scwriter' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_wizard_add_openai', array( $this, 'handle_scwriter_wizard_add_openai' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_wizard_add_blog_topic', array( $this, 'handle_scwriter_wizard_add_blog_topic' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_save_preset', array( $this, 'handle_scwriter_update_preset_ajax' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_delete_preset', array( $this, 'handle_scwriter_delete_preset_ajax' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_heartbeat', array( $this, 'handle_scwriter_heartbeat_ajax' ) );

		add_action( 'wp_ajax_'.SCWRITER_PREFIX.'_post_action', array( $this, 'handle_scwriter_post_action_ajax' ) );

		add_filter('plugin_action_links_' . SCWRITER_FILE_NAME, array($this, 'add_settings_link'));

		add_action('admin_head', array($this, 'remove_admin_notices'));

		add_action('admin_init', array($this, 'redirect_to_settings_page'));
		
	}

	public function remove_admin_notices() : void {

		if (isset($_GET['page']) && in_array($_GET['page'], ['scwriter-settings','scwriter', 'scwriter-presets'])) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}

	}

	public function add_settings_link( array $links ) : array {

		$settings_link = '<a href="' . admin_url('admin.php?page=scwriter-settings') . '">'.__( 'Settings', 'scwriter' ).'</a>';
        array_unshift($links, $settings_link);
        return $links;

	}

	public function handle_scwriter_get_article_info() {

		$nonce_field_key = SCWRITER_PREFIX.'_nonce_field';
		$nonce_field_value = SCWRITER_PREFIX.'_nonce_field_value';

		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Posts::get_article_info( $_POST['article_id'], $_POST['retry'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}

	public function handle_scwriter_create_scwriter_post() {

		$nonce_field_key = SCWRITER_PREFIX.'_nonce_field';
		$nonce_field_value = SCWRITER_PREFIX.'_nonce_field_value';

		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Posts::create_scwriter_post( $_POST );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_update_settings_ajax() {

		$nonce_field_key = SCWRITER_PREFIX.'_settings_nonce';
		$nonce_field_value = SCWRITER_PREFIX.'_settings_updator';
		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Settings::update_settings( $_POST );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_connect_ajax() {

		$nonce_field_key = 'nonce';
		$nonce_field_value = SCWRITER_PREFIX.'_settings_updator';
		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Settings::scwriter_connect();
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_wizard_add_scwriter() {

		$nonce_field_key = SCWRITER_PREFIX.'_settings_nonce';
		$nonce_field_value = SCWRITER_PREFIX.'_settings_updator';

		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Settings::wizard_add_scwriter( $_POST['api_key'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_wizard_add_openai() {

		$nonce_field_key = SCWRITER_PREFIX.'_settings_nonce';
		$nonce_field_value = SCWRITER_PREFIX.'_settings_updator';

		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Settings::wizard_add_openai( $_POST['openai_api_key'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_wizard_add_blog_topic() {

		$nonce_field_key = SCWRITER_PREFIX.'_settings_nonce';
		$nonce_field_value = SCWRITER_PREFIX.'_settings_updator';

		if ( 
			empty($_POST) || 
			!isset($_POST[ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Settings::wizard_add_blog_topic( $_POST['blog_topic'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}
	
	public function handle_scwriter_update_preset_ajax() {

		$nonce_field_key = SCWRITER_PREFIX.'_nonce_field';
		$nonce_field_value = SCWRITER_PREFIX.'_nonce_field_value';

		if ( 
			empty($_POST) || 
			!isset($_POST['data'][ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data'][ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Presets::save_preset( $_POST['data'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}

	public function handle_scwriter_delete_preset_ajax() {

		$nonce_field_key = SCWRITER_PREFIX.'_nonce_field';
		$nonce_field_value = SCWRITER_PREFIX.'_nonce_field_value';

		if ( 
			empty($_POST) || 
			!isset($_POST['data'][ $nonce_field_key ]) || 
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data'][ $nonce_field_key ])), $nonce_field_value ) || 
			!is_user_logged_in() 
		) {
			die();
		} else {
			$response = SCwriter_Presets::delete_preset( $_POST['preset_id'] );
			echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);
			die();
		}

	}


	// Activation method
    public static function on_plugin_activate( $network_wide ) : void {

        if (is_multisite() && $network_wide) {
			return;
		}

		add_option('scwriter_plugin_activated', true);

    }

	// Redirect method to send user to settings page after activation
    public function redirect_to_settings_page() : void {
        // Check if the plugin was just activated
		if ( !is_multisite() ) {
			if (get_option('scwriter_plugin_activated', false)) {
				// Delete the option to prevent multiple redirects
				delete_option('scwriter_plugin_activated');
				
				// Redirect to settings page if in the admin area
				if (is_admin()) {
					wp_redirect(admin_url('admin.php?page=scwriter-settings'));
					exit;
				}
			}
		}
    }

	public static function handle_scwriter_heartbeat_ajax() : void {
		
		SCwriter_Cron::create_post();
		echo wp_json_encode([]);
		die();

	}

	public static function handle_scwriter_post_action_ajax() : void {
		
		$response = SCwriter_Posts::scwriter_post_action( $_POST );
		echo wp_json_encode($response, JSON_UNESCAPED_UNICODE);

		die();

	}

}
