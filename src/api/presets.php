<?php

namespace SCwriter\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\Api\SCwriter_Settings;

class SCwriter_Presets extends SCwriter_ApiBase
{

    const PRESETS_OPTION_NAME = SCWRITER_PREFIX.'_presets';
    const PRESETS_OPTION_DEAFULT = SCWRITER_PREFIX.'_default_preset';
	
	public static function save_preset( array $data ) : array {
		
        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => '',
            'preset_id' => '',
        );

        $json_data = self::prepare_array( $data, true );

        $presets_db = get_option( self::PRESETS_OPTION_NAME );
        $presets = $presets_db ? $presets_db : [];
        $preset_id = SCwriter_Settings::sanitize_string($data['preset_id']);
        $added_new = false;
        if ( $preset_id == 'new' || $preset_id == 'default' ) {
            $new_preset_id = 'preset-'.time();
            $json_data['preset_id'] = $new_preset_id;
            $toReturn['preset_id'] = $new_preset_id;
            $preset_id = $new_preset_id;
            $presets[] = $json_data;
            $added_new = true;
        } else {
            $json_data['preset_id'] = $preset_id;

            foreach ($presets as &$item) {
                if ($item['preset_id'] === $preset_id) {
                    $item = $json_data;
                    break;
                }
            }
        }

        update_option( self::PRESETS_OPTION_DEAFULT, $preset_id, false );

        update_option( self::PRESETS_OPTION_NAME, $presets, false );

        $toReturn['message'] = !$added_new ? __('Preset saved successfully.', 'scwriter') : __('New preset added successfully.', 'scwriter');

        return $toReturn;

	}

	public static function prepare_array( array $data, bool $fill_all_keys = false ) : array {
        
        $result = array();

        if ( isset($data['preset_name']) ) {
            $result['preset_name'] = SCwriter_Settings::sanitize_string($data['preset_name']);
        }
        if ( isset($data['article_length']) ) {
            $result['article_length'] = SCwriter_Settings::sanitize_string($data['article_length']);
        }
        if ( isset($data['article_length_custom']) ) {
            $result['article_length_custom'] = SCwriter_Settings::sanitize_string($data['article_length_custom']);
        }
        if ( isset($data['enable_real_time_data']) ) {
            $result['enable_real_time_data'] = $data['enable_real_time_data'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_preview_outline']) ) {
            $result['enable_preview_outline'] = $data['enable_preview_outline'] == '1' ? '1' : '0';
        }
        if ( isset($data['secondary_keywords']) ) {
            $result['secondary_keywords'] = SCwriter_Settings::sanitize_string($data['secondary_keywords'], 'textarea');
        }
        if ( isset($data['enable_serp_analysis']) ) {
            $result['enable_serp_analysis'] = $data['enable_serp_analysis'] == '1' ? '1' : '0';
        }
        if ( isset($data['tone_of_voice']) ) {
            $result['tone_of_voice'] = SCwriter_Settings::sanitize_string($data['tone_of_voice']);
        }
        if ( isset($data['point_of_view']) ) {
            $result['point_of_view'] = SCwriter_Settings::sanitize_string($data['point_of_view']);
        }
        if ( isset($data['language']) ) {
            $result['language'] = SCwriter_Settings::sanitize_string($data['language'] ?? 'en');
        }
        if ( isset($data['country']) ) {
            $result['country'] = SCwriter_Settings::sanitize_string($data['country'] ?? 'United States');
        }
        if ( isset($data['enable_images']) ) {
            $result['enable_images'] = $data['enable_images'] == '1' ? '1' : '0';
        }
        if ( isset($data['images_source']) ) {
            $result['images_source'] = SCwriter_Settings::sanitize_string($data['images_source']);
        }
        if ( isset($data['enable_external_links']) ) {
            $result['enable_external_links'] = $data['enable_external_links'] == '1' ? '1' : '0';
        }
        if ( isset($data['external_links_stop_words']) ) {
            $result['external_links_stop_words'] = SCwriter_Settings::sanitize_string($data['external_links_stop_words'], 'textarea');
        }
        if ( isset($data['enable_internal_links']) ) {
            $result['enable_internal_links'] = $data['enable_internal_links'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_introduction']) ) {
            $result['enable_introduction'] = $data['enable_introduction'] == '1' ? '1' : '0';
        }
        if ( isset($data['introduction_extra_prompt']) ) {
            $result['introduction_extra_prompt'] = SCwriter_Settings::sanitize_string($data['introduction_extra_prompt'], 'textarea');
        }
        if ( isset($data['enable_faq']) ) {
            $result['enable_faq'] = $data['enable_faq'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_table_of_contents']) ) {
            $result['enable_table_of_contents'] = $data['enable_table_of_contents'] == '1' ? '1' : '0';
        }
        if ( isset($data['table_contents_css_class']) ) {
            $result['table_contents_css_class'] = SCwriter_Settings::sanitize_string($data['table_contents_css_class']);
        }
        if ( isset($data['table_contents_title']) ) {
            $result['table_contents_title'] = SCwriter_Settings::sanitize_string($data['table_contents_title']);
        }
        if ( isset($data['title_extra_prompt']) ) {
            $result['title_extra_prompt'] = SCwriter_Settings::sanitize_string($data['title_extra_prompt'], 'textarea');
        }
        if ( isset($data['body_extra_prompt']) ) {
            $result['body_extra_prompt'] = SCwriter_Settings::sanitize_string($data['body_extra_prompt'], 'textarea');
        }
        if ( isset($data['enable_conclusion']) ) {
            $result['enable_conclusion'] = $data['enable_conclusion'] == '1' ? '1' : '0';
        }
        if ( isset($data['conclusion_extra_prompt']) ) {
            $result['conclusion_extra_prompt'] = SCwriter_Settings::sanitize_string($data['conclusion_extra_prompt'], 'textarea');
        }
        if ( isset($data['enable_categories']) ) {
            $result['enable_categories'] = $data['enable_categories'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_categories_create_new']) ) {
            $result['enable_categories_create_new'] = $data['enable_categories_create_new'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_tags']) ) {
            $result['enable_tags'] = $data['enable_tags'] == '1' ? '1' : '0';
        }
        if ( isset($data['enable_dividers']) ) {
            $result['enable_dividers'] = $data['enable_dividers'] == '1' ? '1' : '0';
        }
        if ( isset($data['global_prompt']) ) {
            $result['global_prompt'] = SCwriter_Settings::sanitize_string($data['global_prompt'], 'textarea');
        }
        if ( isset($data['stop_words']) ) {
            $result['stop_words'] = SCwriter_Settings::sanitize_string($data['stop_words'], 'textarea');
        }
        if ( isset($data['enable_improve_readability']) ) {
            $result['enable_improve_readability'] = $data['enable_improve_readability'] == '1' ? '1' : '0';
        }

        if ( $fill_all_keys ) {

            $table_of_contents = array(
                esc_html__('In the article', 'scwriter'),
                esc_html__('Inside the article', 'scwriter'),
                esc_html__('Within the article', 'scwriter'),
                esc_html__('Throughout the article', 'scwriter'),
                esc_html__('In the manuscript', 'scwriter'),
                esc_html__('Inside the article', 'scwriter'),
                esc_html__('In this article', 'scwriter'),
                esc_html__('In the post', 'scwriter'),
                esc_html__('Inside the post', 'scwriter'),
                esc_html__('Within the post', 'scwriter'),
                esc_html__('Throughout the post', 'scwriter'),
                esc_html__('In the blog post', 'scwriter'),
                esc_html__('Inside the post', 'scwriter'),
                esc_html__('In this post', 'scwriter'),
                esc_html__('In the story', 'scwriter'),
                esc_html__('Inside the story', 'scwriter'),
                esc_html__('Within the story', 'scwriter'),
                esc_html__('Throughout the story', 'scwriter'),
                esc_html__('In the narrative', 'scwriter'),
                esc_html__('Inside the story', 'scwriter'),
                esc_html__('In this story', 'scwriter'),
            );
            $random_table_of_contents = $table_of_contents[array_rand($table_of_contents)];
            $random_table_contents_css_class = sanitize_title($random_table_of_contents);

            $all_keys = array(
                'preset_name' => esc_html__('Default', 'scwriter'),
                'article_length' => '0',
                'article_length_custom' => '400',
                'enable_real_time_data' => '0',
                'enable_preview_outline' => '0',
                'secondary_keywords' => '',
                'enable_serp_analysis' => '0',
                'tone_of_voice' => esc_html__('Conversational, Friendly, Knowledgeable and Clear', 'scwriter'),
                'point_of_view' => esc_html__('First Person Singular (I, me, my, mine)', 'scwriter'),
                'language' => 'en',
                'country' => 'US',
                'enable_images' => '0',
                'images_source' => 'placeholders',
                'enable_external_links' => '0',
                'external_links_stop_words' => '',
                'enable_internal_links' => '0',
                'enable_introduction' => '1',
                'introduction_extra_prompt' => '',
                'enable_faq' => '',
                'enable_table_of_contents' => '1',
                'table_contents_css_class' => $random_table_of_contents,
                'table_contents_title' => $random_table_of_contents,
                'title_extra_prompt' => '',
                'body_extra_prompt' => '',
                'enable_conclusion' => '1',
                'conclusion_extra_prompt' => '',
                'enable_categories' => '0',
                'enable_categories_create_new' => '1',
                'enable_tags' => '0',
                'enable_dividers' => '0',
                'global_prompt' => '',
                'stop_words' => '',
                'enable_improve_readability' => '1',
                'title' => ''
            );

            foreach ( $all_keys as $key => $value ) {
                if ( !isset($result[$key]) ){
                    $result[$key] = $value;
                }
            }

        }

        return $result;

    }

	public static function delete_preset( string $preset_id ) : array {
		
        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => '',
            'preset_id' => '',
        );

        $default_preset_id = get_option( self::PRESETS_OPTION_DEAFULT );
        $presets_db = get_option( self::PRESETS_OPTION_NAME );
        $presets = $presets_db ? $presets_db : [];
        $preset_id = SCwriter_Settings::sanitize_string($preset_id);

        $value_to_delete = $preset_id;
        $presets = array_filter($presets, function($item) use ($value_to_delete) {
            return $item['preset_id'] !== $value_to_delete;
        });
        $presets = array_values($presets);

        if ( $default_preset_id == $preset_id ) {
            $default_preset_key = array_key_first( $presets );
            $default_preset_id = $presets[$default_preset_key]['preset_id'];
            update_option( self::PRESETS_OPTION_DEAFULT, $default_preset_id, false );
        }
        
        update_option( self::PRESETS_OPTION_NAME, $presets, false );

        $toReturn['preset_id'] = $default_preset_id;

        $toReturn['message'] = __('Preset deleted successfully.', 'scwriter');

        return $toReturn;

	}

    private static function sanitize_string( string $string, string $type = 'input' ) : string {

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

}

?>
