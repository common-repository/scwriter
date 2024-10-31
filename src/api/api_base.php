<?php

namespace SCwriter\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\API\SCwriter_Settings;

class SCwriter_ApiBase
{
    protected static function get_api_url( string $endpoint, string $api_key = '' ) : string {

        if ( !$api_key ) {
            $api_key = SCwriter_Settings::get_scwriter_api_key();
        }

        return SCWRITER_API_ENDPOINT . $endpoint . '?api_key=' . $api_key;

    }

    protected static function get_response( $response, string $default_error_message ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'code' => ''
        );

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $toReturn['error'] = true;
            $toReturn['error_message'] = $default_error_message . ' ' . $error_message;
            error_log($toReturn['error_message']);
        } else {

            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            $is_success = false;
            if ( isset($response_body['success']) && $response_body['success'] ) {
                $is_success = true;
            }
            if ( !$is_success ){

                $toReturn['error'] = true;
                $error_message = $default_error_message;

                if ( isset($response_body['data'][0]['message']['message']) ) {
                    $error_message = $response_body['data'][0]['message']['message'];
                } elseif ( isset($response_body['data'][0]['message']) ) {
                    $error_message = $response_body['data'][0]['message'];
                } elseif ( isset($response_body['data']['message']) ) {
                    $error_message = $response_body['data']['message'];
                }
                if ( isset($response_body['data'][0]['code']) ) {
                    if ( $response_body['data'][0]['code'] == 'rest_client_validation' ) {
                        $error_message = esc_html__("Provided SCwriter API Key is not valid", 'scwriter');
                    }
                    elseif ( $response_body['data'][0]['code'] == 'server_under_maintenance' ) {
                        $error_message = esc_html__("We're currently sprucing things up on our server to make your experience even better. Please check back soon! Thank you for your patience.", 'scwriter');
                    } elseif ( $response_body['data'][0]['code'] == 'article_limit' ) {
                        $usage = SCwriter_Settings::getUserUsage();
                        if ( !$usage['error'] ) {
                            /* translators: %1$d is the article limit, %2$s is the reset date, %3$s is the link to upgrade */
                            $error_message = sprintf(
                                wp_kses(
                                    /* Translators: %1$d is the number of articles, %2$s is the reset date, %3$s is the link to upgrade */
                                    esc_html__('You’ve reached your limit of %1$d articles. Your limit will reset on %2$s. To continue creating more articles, consider upgrading your plan %3$s.', 'scwriter'),
                                    array(
                                        'a' => array( 'href' => array(), 'target' => array() )
                                    )
                                ),
                                $usage['article_limit'],
                                $usage['reset_date'],
                                '<a href="' . esc_url( SCWRITER_WEBSITE ) . '" target="_blank">' . esc_html__('here', 'scwriter') . '</a>'
                            );
                        }
                    }

                    $toReturn['code'] = $response_body['data'][0]['code'];
                }

                $toReturn['error_message'] = self::wrapUrlsWithAnchorTags( $error_message );

            }

        }

        return $toReturn;

    }

    private static function wrapUrlsWithAnchorTags( string $text ) : string {

        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $matches);
    
        if ( isset($matches[0]) ) {
            foreach ( $matches[0] as $match) {
                $text = str_replace( $match, '<a href="'.$match.'" target="_blank">'.$match.'</a>', $text );
            }
        }
    
        return $text;
        
    }
    
}

?>