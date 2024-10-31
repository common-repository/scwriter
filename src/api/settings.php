<?php

namespace SCwriter\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\SCwriter_Dashboard;
use SCwriter\Api\SCwriter_Presets;

class SCwriter_Settings extends SCwriter_ApiBase
{

    const SETTINGS_OPTION_NAME = SCWRITER_PREFIX.'_settings';
    const API_KEY_OPTION_NAME = SCWRITER_PREFIX.'_api_key';
    const USER_ROLES_OPTION_NAME = SCWRITER_PREFIX.'_user_roles';
    const PUBLISH_FREQUENCY_OPTION_NAME = SCWRITER_PREFIX.'_publish_frequency';
    const TRENDS_OPTION_NAME = SCWRITER_PREFIX.'_trends';
    const AUTOPOST_PRESET = SCWRITER_PREFIX.'_autopost_preset';
    const USAGE_TRANSIENT = SCWRITER_PREFIX.'_usage';
	
	public static function update_settings( array $data ) : array {
		
        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

        $api_key = sanitize_text_field($data['api_key']);
        
        if ( self::isEncrypted( $api_key ) ) {
            $api_key = self::decrypt( $api_key );
        }

        $openai_api_key = self::sanitize_string($data['openai_api_key']);

        $api_key_validate = self::validate_api_key( $api_key );

        if ( $api_key_validate['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $api_key_validate['error_message'];

            return $toReturn;
        }

        $openai_api_key_validate = self::validate_openai_api_key( $api_key, $openai_api_key );

        if ( $openai_api_key_validate['error'] ) {

            $toReturn['error'] = true;
            $toReturn['error_message'] = $openai_api_key_validate['error_message'];

            return $toReturn;

        }

        if ( $openai_api_key_validate['need_send'] ) {

            $json_data = array(
                "openai_api_key" => $openai_api_key,
            );
    
            $args = array(
                'body'        => wp_json_encode( $json_data, JSON_UNESCAPED_UNICODE ),
                'method'      => 'PUT',
                'timeout'     => 60,
                'headers'     => array(
                    'Content-Type' => 'application/json'
                ),
            );
    
            $response = wp_remote_request( self::get_api_url('users', $api_key), $args );

            $error_message = esc_html__('An error occurred while saving settings.', 'scwriter');
            $parsed_response = self::get_response( $response, $error_message );

            if ( $parsed_response['error'] ) {
                $toReturn['error'] = true;
                $toReturn['error_message'] = $parsed_response['error_message'];
            }

        }

        if ( !$toReturn['error'] ) {
            
            $toReturn['message'] = esc_html__('SEO Blog Writer Settings were saved successfully.', 'scwriter');
            
            $json_data = array(
                "openai_api_key"    => 'openai_api_key',
                "author"            => get_current_user_id(),
                "automated_author"  => $data['automated_author'] ?? get_current_user_id(),
                "blog_topic"        => self::sanitize_string($data['blog_topic'], 'textarea'),
            );
            
            update_option( self::SETTINGS_OPTION_NAME, $json_data, false );

            if ( !self::isEncrypted( $api_key ) ) {
                $api_key = self::encrypt( $api_key );
            }
            update_option( self::API_KEY_OPTION_NAME, $api_key, false );
            self::updateUserRolesCapabilities($data['user_roles'] ?? []);
            self::updatePublishFrequency($data['publish_frequency'] ?? 'none');
            update_option( self::AUTOPOST_PRESET, self::sanitize_string($data['preset'] ?? ''), false );
            update_option( self::TRENDS_OPTION_NAME, self::sanitize_string($data['trends'] ?? '', 'textarea'), false );

        }
        
        return $toReturn;

	}

    private static function validate_api_key( string $api_key, bool $comare_with_stored = true ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
        );

        if ( $comare_with_stored ) {
            $stored_api_key = get_option( self::API_KEY_OPTION_NAME );
            if ( $stored_api_key && $stored_api_key == $api_key ) {
                return $toReturn;
            }
        }

        if ( self::isEncrypted( $api_key ) ) {
            $api_key = self::decrypt( $api_key );
        }

        $args = array(
            'body'        => '',
            'method'      => 'GET',
            'timeout'     => 60,
            'headers'     => array(
                'Content-Type' => 'application/json'
            ),
        );

        $response = wp_remote_request( self::get_api_url('users', $api_key), $args );

        $error_message = esc_html__('Oops! Incorrect SCwriter API key provided.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( $parsed_response['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];
        } else {

            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;

            $parsed_url = parse_url( get_home_url() );
            $domain = $parsed_url['host'];

            $send_data = array(
                'domain'    => $domain,
                'email'     => $user_email
            );

            $args = array(
                'body'      => wp_json_encode( $send_data, JSON_UNESCAPED_UNICODE ),
                'method'    => 'POST',
                'timeout'   => 60,
                'headers'   => array(
                    'Content-Type' => 'application/json'
                ),
            );

            $api_url = SCWRITER_WEBSITE . '/wp-json/scwriter/v1/add_domain?api_key='.SCWRITER_CONNECTION_API_KEY;
            $response = wp_remote_request( $api_url, $args );
            $error_message = esc_html__('An error occurred while adding domain.', 'scwriter');
            $parsed_response = self::get_response( $response, $error_message );

            $presets_db = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );

            if ( !$presets_db ) {
                $default_preset_data = SCwriter_Presets::prepare_array([], true);
                $default_preset_data['preset_id'] = 'new';
                SCwriter_Presets::save_preset( $default_preset_data );
            }
        }

        return $toReturn;

    }

    public static function getUserUsageCache() : array {

        $cached_data = get_transient( self::USAGE_TRANSIENT );

        if ( $cached_data !== false ) {
            return $cached_data;
        }

        $usage = self::getUserUsage();

        if ( !$usage['error'] && isset($usage['article_limit']) ) {
            set_transient( self::USAGE_TRANSIENT, $usage, 3600 ); 
        }

        return $usage;

    }

    public static function getUserUsage() : array {

        $toReturn = array(
            'article_limit' => 0,
            'article_count' => 0,
            'reset_date'    => '',
            'error'         => false,
            'error_message' => '',
        );

        $args = array(
            'method'      => 'GET',
            'timeout'   => 60,
            'headers'     => array(
                'Content-Type' => 'application/json'
            ),
        );

        $response = wp_remote_request( self::get_api_url('users'), $args );
        
        $error_message = esc_html__('An error occurred while getting user usage limit.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( $parsed_response['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];
        } else {
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if (
                isset($response_body['data']['usage']['article_limit']) && 
                isset($response_body['data']['usage']['article_count']) && 
                isset($response_body['data']['usage']['reset_date'])
            ) {
                $toReturn['article_limit'] = $response_body['data']['usage']['article_limit'];
                $toReturn['article_count'] = $response_body['data']['usage']['article_count'];
                $toReturn['reset_date'] = date_i18n(get_option('date_format'), strtotime($response_body['data']['usage']['reset_date']));
            } else {
                $toReturn['error'] = true;
                $toReturn['error_message'] = esc_html__('There is no data about usge of this user', 'scwriter');
            }
        }

        return $toReturn;

    }

    private static function validate_openai_api_key( string $api_key, string $openai_api_key ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'need_send' => true
        );
        
        if ( $openai_api_key === 'openai_api_key' ) {
            $toReturn['need_send'] = false;
            return $toReturn;
        }
        
        $json_data = array(
            'openai_api_key' => $openai_api_key
        );

        $args = array(
            'body'      => wp_json_encode( $json_data, JSON_UNESCAPED_UNICODE ),
            'method'    => 'POST',
            'timeout'   => 60,
            'headers'   => array(
                'Content-Type' => 'application/json'
            ),
        );

        $response = wp_remote_request( self::get_api_url('validation/openai', $api_key), $args );

        $error_message = esc_html__('An error occurred while validating OpenAi API key.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( $parsed_response['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];
        }

        return $toReturn;

    }

    public static function sanitize_string( string $string, string $type = 'input' ) : string {

        if ( $type == 'input' ) {

            $string = sanitize_text_field($string);
            $string = stripslashes($string);

        } elseif ( $type == 'textarea' ) {

            $lines = explode("\n", sanitize_textarea_field($string));
            $lines = array_filter(array_map('trim', $lines));
            $lines = array_filter(array_map('sanitize_text_field', $lines));
            $lines = array_filter(array_map('stripslashes', $lines));
            $string = implode("\n", $lines);
            
        }

        return $string;

    }

    private static function updatePublishFrequency( string $publish_frequency ) : void {

        $current_publish_frequency = get_option( self::PUBLISH_FREQUENCY_OPTION_NAME );

        if ( $current_publish_frequency != $publish_frequency ) {
            wp_clear_scheduled_hook( SCWRITER_PREFIX . '_create_trend_article' );
            update_option( self::PUBLISH_FREQUENCY_OPTION_NAME, $publish_frequency );
        }

    }

    private static function updateUserRolesCapabilities( array $enabled_user_roles ) : void {

        global $wp_roles;

        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        foreach ($editable_roles as $slug => $editable_role) {
            $can_edit = true;
            if ( $slug == 'administrator' ) {
                $can_edit = is_multisite();
            }
            if ( $can_edit ) {
                if ( in_array($slug, $enabled_user_roles) ) {
                    $role = get_role( $slug );
                    $role->add_cap( SCwriter_Dashboard::REQUIRED_CAPABILITY );
                } else {
                    $role = get_role( $slug );
                    $role->remove_cap( SCwriter_Dashboard::REQUIRED_CAPABILITY );
                }
            }
        }

        update_option( self::USER_ROLES_OPTION_NAME, $enabled_user_roles );

    }

    public static function scwriter_connect() {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;

        $parsed_url = parse_url( get_home_url() );
        $domain = $parsed_url['host'];

        $send_data = array(
            'domain'    => $domain,
            'email'     => $user_email,
            'first_name'=> $first_name,
            'last_name' => $last_name,
        );

        $args = array(
            'body'      => wp_json_encode( $send_data, JSON_UNESCAPED_UNICODE ),
            'method'    => 'POST',
            'timeout'   => 60,
            'headers'   => array(
                'Content-Type' => 'application/json'
            ),
        );

        $api_url = SCWRITER_WEBSITE . '/wp-json/scwriter/v1/connect?api_key='.SCWRITER_CONNECTION_API_KEY;
        $response = wp_remote_request( $api_url, $args );
        
        $error_message = esc_html__('An error occurred while connecting with SCwriter.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( $parsed_response['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];
        } else {

            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            $api_key = $response_body['data']['api_key'];
            $api_key = self::encrypt( $api_key );
            update_option( self::API_KEY_OPTION_NAME, $api_key, false );

            $presets_db = get_option( SCwriter_Presets::PRESETS_OPTION_NAME );
            if ( !$presets_db ) {
                $default_preset_data = SCwriter_Presets::prepare_array([], true);
                $default_preset_data['preset_id'] = 'new';
                SCwriter_Presets::save_preset( $default_preset_data );
            }

            $toReturn['message'] = esc_html__('SCwriter API Key successfully saved.', 'scwriter');

        }

        return $toReturn;

    }

    public static function wizard_add_scwriter( string $in_api_key ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

        $api_key = sanitize_text_field($in_api_key);
        
        $api_key_validate = self::validate_api_key( $api_key, false );

        if ( $api_key_validate['error'] ) {
            $toReturn['error'] = true;
            $toReturn['error_message'] = $api_key_validate['error_message'];

            return $toReturn;
        } else {

            $api_key = self::encrypt( $api_key );
            update_option( self::API_KEY_OPTION_NAME, $api_key, false );

            $toReturn['message'] = esc_html__('SCwriter API Key successfully saved.', 'scwriter');

        }

        return $toReturn;

    }

    public static function get_scwriter_api_key() : string {

        $api_key = '';

        $stored_api_key = get_option( self::API_KEY_OPTION_NAME );

        if ( $stored_api_key ) {
            if ( !self::isEncrypted( $stored_api_key ) ) {
                $api_key = $stored_api_key;
                $encrypted_api_key = self::encrypt( $stored_api_key );
                update_option( self::API_KEY_OPTION_NAME, $encrypted_api_key, false );
            } else {
                $api_key = self::decrypt( $stored_api_key );
            }
        }

        return $api_key;

    }

    public static function wizard_add_openai( string $in_openai_api_key ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

        $openai_api_key = sanitize_text_field($in_openai_api_key);
        
        $openai_api_key_validate = self::validate_openai_api_key( '', $openai_api_key );

        if ( $openai_api_key_validate['error'] ) {

            $toReturn['error'] = true;
            $toReturn['error_message'] = $openai_api_key_validate['error_message'];

            return $toReturn;

        } else {

            $json_data = array(
                "openai_api_key"    => 'openai_api_key',
            );
            
            update_option( self::SETTINGS_OPTION_NAME, $json_data, false );

            $toReturn['message'] = esc_html__('OpenAI API Key successfully saved.', 'scwriter');
            $toReturn['redirect_to'] = admin_url('admin.php?page=scwriter-settings');

        }

        return $toReturn;

    }
    
    public static function wizard_add_blog_topic( string $blog_topic ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

		$settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
        
        $settings['blog_topic'] = self::sanitize_string($blog_topic, 'textarea');

        update_option( self::SETTINGS_OPTION_NAME, $settings, false );

        $toReturn['message'] = esc_html__('Blog Topic successfully saved.', 'scwriter');
        $toReturn['redirect_to'] = admin_url('admin.php?page=scwriter-settings');

        return $toReturn;

    }
    
    private static function encrypt(string $string): string {
        $hashedKey = hash('sha256', 'BLABLABLA_HASH_KEY', true); // Generate the binary hash
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        
        // Encrypt the string
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $hashedKey, OPENSSL_RAW_DATA, $iv);

        // Base64 encode IV and encrypted data separately, then concatenate them
        return base64_encode('ENCRYPTED::' . base64_encode($iv) . '::' . base64_encode($encrypted));
    }

    private static function decrypt(string $encryptedData): string|bool {
        // Decode the base64 encoded data
        $decodedData = base64_decode($encryptedData);

        // Ensure the decoded data is valid and contains the expected 'ENCRYPTED::' prefix
        if (strpos($decodedData, 'ENCRYPTED::') !== 0) {
            return false; // Invalid data format
        }
        
        // Remove the 'ENCRYPTED::' prefix
        $decodedData = substr($decodedData, strlen('ENCRYPTED::'));

        // Split the IV and encrypted data using '::' as the delimiter
        $parts = explode('::', $decodedData, 2);
        
        // Ensure both IV and encrypted data parts are present
        if (count($parts) !== 2) {
            return false; // Malformed data
        }

        // Extract IV and encrypted data
        [$ivBase64, $encryptedBase64] = $parts;

        // Decode the IV and encrypted data from base64
        $iv = base64_decode($ivBase64);
        $encrypted = base64_decode($encryptedBase64);

        // If either IV or encrypted data is invalid, return false
        if ($iv === false || $encrypted === false) {
            return false;
        }

        // Hash the key for decryption
        $hashedKey = hash('sha256', 'BLABLABLA_HASH_KEY', true); // Use binary hash

        // Decrypt the data
        return openssl_decrypt($encrypted, 'AES-256-CBC', $hashedKey, OPENSSL_RAW_DATA, $iv);
    }

    private static function isEncrypted(string $string): bool {
        $decrypted = self::decrypt($string);
        return $decrypted !== false && !empty($decrypted);
    }

}

?>
