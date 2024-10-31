<?php

namespace SCwriter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\API\SCwriter_Settings;
use SCwriter\API\SCwriter_Presets;
use SCwriter\SCwriter_CPT;

class SCwriter_Dashboard
{
	const REQUIRED_CAPABILITY = SCWRITER_PREFIX.'_create_posts_with_ai';
	
	function __construct() {
		$this->init();
	}

	public function init() : void{
		add_action( 'init', [$this, 'add_capability'] );
		add_action( 'admin_menu', [$this, 'register_pages'] );
		add_filter('parent_file', [$this, 'set_cpt_parent_menu']);
		add_filter('submenu_file', [$this, 'set_cpt_submenu_active']);
	}

	/**
	 * Set if menu is visible
	 */
	public function add_capability() : void {
		
		$current_user_id = get_current_user_id();

		if (is_multisite() && is_super_admin()) {
			if (!user_can($current_user_id, self::REQUIRED_CAPABILITY)) {
				$role = get_role( 'site_admins' );
				$role->add_cap( self::REQUIRED_CAPABILITY );
			}
        } elseif (!is_multisite() && current_user_can('manage_options')) {
			if (!user_can($current_user_id, self::REQUIRED_CAPABILITY)) {
				$role = get_role( 'administrator' );
				$role->add_cap( self::REQUIRED_CAPABILITY );
			}
        }

	}

	/**
	 * Set parent menu for CPT
	 */
	public function set_cpt_parent_menu( $parent_file ) {

		global $current_screen;

		$post_type = SCwriter_CPT::POST_TYPE;
		$menu_slug = 'scwriter-settings';

		if ($current_screen->post_type === $post_type) {
			$parent_file = $menu_slug;
		}

		return $parent_file;
		
	}

	/**
	 * Set child menu item for CPT as active
	 */
	public function set_cpt_submenu_active( $submenu_file ) {
		global $current_screen;

		$post_type = SCwriter_CPT::POST_TYPE;
		$menu_slug = 'scwriter-settings';

		if ($current_screen->post_type === $post_type) {
			$submenu_file = 'edit.php?post_type=' . $post_type . '&all_posts=1';
		}

		return $submenu_file;
	}

	/**
	 * Adds pages to the Admin Panel menu
	 */
	public function register_pages() {

		$menu_slug = 'scwriter-settings';
		
		add_menu_page(
			__('SEO Blog Writer', 'scwriter'),
			__('SEO Blog Writer', 'scwriter'),
			self::REQUIRED_CAPABILITY,
			$menu_slug,
			[$this, 'markup_settings_page'],
			'dashicons-admin-customizer',
			6
		);

		$api_key = get_option( SCwriter_Settings::API_KEY_OPTION_NAME );
		$settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
		$openai_api_key = $settings['openai_api_key'] ?? '';
		$blog_topic = $settings['blog_topic'] ?? '';

		if ( $api_key && $openai_api_key && $blog_topic ) {
			add_submenu_page(
				'edit.php',
				__('Create Post with AI', 'scwriter'),
				__('Create Post with AI', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				'admin.php?page=scwriter',
				'',
			);

			add_submenu_page(
				$menu_slug,
				__('Posts', 'scwriter'),
				__('Posts', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				'edit.php?post_type=' . SCwriter_CPT::POST_TYPE . '&all_posts=1',
			);

			add_submenu_page(
				$menu_slug,
				__('Create Post', 'scwriter'),
				__('Create Post', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				'scwriter',
				[$this, 'markup_create_scwriter_post_page'],
			);

			add_submenu_page(
				$menu_slug,
				__('Presets Management', 'scwriter'),
				__('Presets', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				'scwriter-presets',
				[$this, 'markup_presets_page'],
			);

			remove_submenu_page($menu_slug,$menu_slug);
			
			add_submenu_page(
				$menu_slug,
				__('SEO Blog Writer Settings', 'scwriter'),
				__('Settings', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				$menu_slug,
				[$this, 'markup_settings_page'],
			);

			add_submenu_page(
				$menu_slug,
				__('Support', 'scwriter'),
				__('Support', 'scwriter'),
				self::REQUIRED_CAPABILITY,
				SCWRITER_PREFIX . '-support',
			);

			global $submenu;

			$support_url = 'https://seocontentwriter.actlys.com/support/?utm_source=scwriterplugin&utm_medium=menu';
			$submenu[$menu_slug][5][2] = $support_url;
			$submenu[$menu_slug][5][4] = SCWRITER_PREFIX . '-support';

		}

	}

	/**
	 * Creates the markup for the Settings page
	 */
	public function markup_settings_page() : void {
		if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {

			$variables = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
			$variables = $variables ? $variables : [];

			$api_key = get_option( SCwriter_Settings::API_KEY_OPTION_NAME );
			$api_key = $api_key ? ['api_key' => $api_key] : ['api_key' => ''];
			
			$user_roles = get_option( SCwriter_Settings::USER_ROLES_OPTION_NAME );
			$user_roles = $user_roles ? ['user_roles' => $user_roles] : ['user_roles' => []];

			$publish_frequency = get_option( SCwriter_Settings::PUBLISH_FREQUENCY_OPTION_NAME );
			$publish_frequency = $publish_frequency ? ['publish_frequency' => $publish_frequency] : ['publish_frequency' => 'none'];

			$trends = get_option( SCwriter_Settings::TRENDS_OPTION_NAME );
			$trends = $trends ? ['trends' => $trends] : ['trends' => ''];

			$presets = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );
			$presets = $presets ? ['presets' => $presets] : ['presets' => []];

			$default_preset = get_option( SCwriter_Presets::PRESETS_OPTION_DEAFULT );
			$default_preset = !is_null($default_preset) ? ['default_preset' => $default_preset] : ['default_preset' => ''];

			$autopost_preset = get_option( SCwriter_Settings::AUTOPOST_PRESET );
			$autopost_preset = !is_null($autopost_preset) ? ['default_preset' => $autopost_preset] : $default_preset;
			
			$settings = array_merge( $variables, $api_key, $user_roles, $publish_frequency, $trends, $presets, $autopost_preset );

			self::get_markup('settings', $settings);

		} else {
			wp_die( 'Access denied.' );
		}
	}

	/**
	 * Creates the markup for the Presets page
	 */
	public function markup_presets_page() : void {
		if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {

			$presets = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );
			$presets = $presets ? ['presets' => $presets] : ['presets' => []];

			$default_preset = get_option( SCwriter_Presets::PRESETS_OPTION_DEAFULT );
			$default_preset = !is_null($default_preset) ? ['default_preset' => $default_preset] : ['default_preset' => ''];

			$settings = array_merge( $presets, $default_preset );
			self::get_markup('presets', $settings);

		} else {
			wp_die( 'Access denied.' );
		}
	}

	/**
	 * Creates the markup for the Create Post page
	 */
	public function markup_create_scwriter_post_page() : void {
		if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {

			$scwriter_form_messages = get_option( SCWRITER_PREFIX.'_form_messages', false );
			$form_messages = $scwriter_form_messages ? ['form_messages' => $scwriter_form_messages] : [];
			delete_option(SCWRITER_PREFIX."_form_messages");

			$presets = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );
			$presets = $presets ? ['presets' => $presets] : ['presets' => []];

			$default_preset = get_option( SCwriter_Presets::PRESETS_OPTION_DEAFULT );
			$default_preset = !is_null($default_preset) ? ['default_preset' => $default_preset] : ['default_preset' => ''];

			$usage = SCwriter_Settings::getUserUsageCache();
			$usage = !$usage['error'] ? ['usage' => $usage] : ['usage' => []];
			
			$settings = array_merge( $form_messages, $presets, $default_preset, $usage );
			self::get_markup('new_post', $settings);
			
		} else {
			wp_die( 'Access denied.' );
		}
	}

	/**
	 * Returns markup template passing variables
	 */
	private function get_markup( string $file_name, array $variables = [] ) : void {

		$template_path = dirname( __DIR__ ) . '/views/' . $file_name . '.php';

		if ( is_file( $template_path ) ) {

			extract( ['options' => $variables] );
			require_once( $template_path );

		}

	}
}

?>