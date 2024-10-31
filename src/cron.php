<?php

namespace SCwriter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\SCwriter_CPT;
use SCwriter\Enums\SCwriter_Status;
use SCwriter\API\SCwriter_Posts;
use SCwriter\Api\SCwriter_Settings;
use SCwriter\Api\SCwriter_Presets;

class SCwriter_Cron
{

    public static function init() : void{
		add_action( 'init', [__CLASS__, 'schedule_custom_events'] );

        add_action( SCWRITER_PREFIX.'_create_post', [__CLASS__, 'create_post'] );
        add_action( SCWRITER_PREFIX.'_create_trend_article', [__CLASS__, 'create_trend_article'] );
	}

    /**
	 * Check articles and create posts
	 */
	public static function create_post() : void {
		
		$api_key = get_option( SCwriter_Settings::API_KEY_OPTION_NAME );
		$settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
		$openai_api_key = $settings['openai_api_key'] ?? '';
		$blog_topic = $settings['blog_topic'] ?? '';

		if ( !$api_key || !$openai_api_key || !$blog_topic ){
			exit;
		}

		$posts = get_posts( [
			'numberposts' => -1,
			'post_type'   => SCwriter_CPT::POST_TYPE,
			'meta_query' => array(
				array(
					'key'   => SCWRITER_PREFIX.'_status',
					'value' => [SCwriter_Status::QUEUED, SCwriter_Status::IN_PROGRESS],
					'compare' => 'IN',
				),
			)
		] );

		if ( $posts ) {

			$posts_response = SCwriter_Posts::get_all_posts();

			if ( !$posts_response['error'] && $posts_response['articles'] ) {
				$articles_hashes = array_column( $posts_response['articles'], 'id_hash' );
				

				foreach ( $posts as $post_obj ) {
					$article_id = get_post_meta( $post_obj->ID, SCWRITER_PREFIX.'_article_id', true );
					
					// set expired if not in external DB
					if ( !in_array($article_id, $articles_hashes) ) {
						$update_data = array(
							'status' => SCwriter_Status::EXPIRED,
							'last_error' => 'Not found',
							'completed_percentage' => 0,
						);
						SCwriter_Posts::update_status( $post_obj->ID, $update_data );
					} else {
						$article_key = array_search($article_id, $articles_hashes);

						if ( $posts_response['articles'][$article_key]['status'] == SCwriter_Status::COMPLETED ) {
							SCwriter_Posts::create_post( $post_obj->ID, $article_id );
						} else {
							SCwriter_Posts::update_status( $post_obj->ID, $posts_response['articles'][$article_key] );
						}
					}
				}

			}

		}

		exit;
	}

    /**
	 * Create article by trends
	 */
	public static function create_trend_article() : void {

		$api_key = get_option( SCwriter_Settings::API_KEY_OPTION_NAME );
		$settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
		$openai_api_key = $settings['openai_api_key'] ?? '';
		$blog_topic = $settings['blog_topic'] ?? '';

		if ( !$api_key || !$openai_api_key || !$blog_topic ){
			exit;
		}
		
		$presets = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );
		$autopost_preset = get_option( SCwriter_Settings::AUTOPOST_PRESET );

		if ( $autopost_preset && $presets ) {

			$trends = get_option( SCwriter_Settings::TRENDS_OPTION_NAME );
			$trend = '';

			$usage_limit_exceeded = false;
			$usage = SCwriter_Settings::getUserUsage();
			if ( !$usage['error'] ) {
				$article_limit = (int) $usage['article_limit'];
				$article_count = (int) $usage['article_count'];
				if ( $article_limit != 0 && $article_count >= $article_limit ) {
					$usage_limit_exceeded = true;
				}
			}
			
			if ( $trends ) {
				$lines = explode("\n", $trends);

				// Get the first line if it exists
				if (!empty($lines)) {
					$trend = trim($lines[0]);
					
					if ( !$usage_limit_exceeded ) {
						// Remove the first line from the array
						array_shift($lines);
						
						// Check if not empty $lines
						if ( count($lines) > 0 ) {
							$trends = implode("\n", $lines);
						} else {
							$trends = '';
						}

						update_option( SCwriter_Settings::TRENDS_OPTION_NAME, $trends );
					}
				}
			}

			$settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
			if ( isset($settings['automated_author']) ) {
				$user_id = $settings['automated_author'];
			} else {
				$user_id = isset($settings['author']) ? (int) $settings['author'] : PHP_INT_MIN;
			}

			if ( !$usage_limit_exceeded ) {
				$preset_data = array();
				foreach ( $presets as $preset ) {
					if ( $preset['preset_id'] == $autopost_preset ) {
						$preset_data = $preset;
						break;
					}
				}
				
				$data = array_merge( $preset_data, ['primary_keyword' => $trend] );
				
				SCwriter_Posts::create_scwriter_post( $data, $user_id );
			} else {
				SCwriter_Posts::create_scwriter_post_limit( $trend, $user_id, $usage );
			}

		}

		exit;
	}

	/**
	 * Registers new custom schedules
	 */
	public static function add_custom_schedules( array $schedules ) : array {
		
		$schedules[ SCWRITER_PREFIX.'_interval' ] = array(
			'interval' => 5*MINUTE_IN_SECONDS,
			'display'  => 'Every 5 min',
		);

		$schedules[ SCWRITER_PREFIX.'_hourly' ] = array(
			'interval' => HOUR_IN_SECONDS,
			'display'  => 'Every 1 hour',
		);
		
		$schedules[ SCWRITER_PREFIX.'_daily' ] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => 'Every day',
		);
		
		$schedules[ SCWRITER_PREFIX.'_weekly' ] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => 'Every week',
		);
		
		$schedules[ SCWRITER_PREFIX.'_biweekly' ] = array(
			'interval' => 2*WEEK_IN_SECONDS,
			'display'  => 'Every 2 weeks',
		);
		
		$schedules[ SCWRITER_PREFIX.'_monthly' ] = array(
			'interval' => MONTH_IN_SECONDS,
			'display'  => 'Every month',
		);
	
		return $schedules;
	}

	/**
	 * Schedules custom events
	 */
	public static function schedule_custom_events() : void {
		// the actual hook to register new custom schedule
		add_filter( 'cron_schedules', [__CLASS__, 'add_custom_schedules'] );
			
		// Create a new post
		if( !wp_next_scheduled( SCWRITER_PREFIX.'_create_post' ) ){
            wp_schedule_event( time(), SCWRITER_PREFIX.'_interval', SCWRITER_PREFIX.'_create_post' );
		}

		$publish_frequency = get_option( SCwriter_Settings::PUBLISH_FREQUENCY_OPTION_NAME );
		if ( $publish_frequency && $publish_frequency != 'none' ) {
			if( !wp_next_scheduled( SCWRITER_PREFIX.'_create_trend_article' ) ){
				wp_schedule_event( time(), SCWRITER_PREFIX.'_'.$publish_frequency, SCWRITER_PREFIX.'_create_trend_article' );
			}
		}
		
	}
	
}

?>