<?php

namespace SCwriter\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\SCwriter_CPT;
use SCwriter\Enums\SCwriter_Status;
use SCwriter\API\SCwriter_Settings;
use SCwriter\API\SCwriter_Presets;
use SCwriter\Plugins\SCwriter_PostMetaDataUpdater;

class SCwriter_Posts extends SCwriter_ApiBase
{
	const COUNT_POSTS = 50;
	const COUNT_CATEGORIES = 25;
	const COUNT_TAGS = 100;
	
	public static function get_all_posts() : array {

        $toReturn = array(
            'error' => false,
            'articles' => []
        );

        $response = wp_remote_get( self::get_api_url('articles') );

        $error_message = __('Error get all articles in plugin Seo Blog Writer.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( $parsed_response['error'] ) {

            $toReturn['error'] = true;

        } else {

            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            $toReturn['articles'] = $response_body['data']['article'];

        }

        return $toReturn;

    }

	public static function create_post( int $scwriter_post_id, string $article_hash ) : void {
        
        $response = wp_remote_get( self::get_api_url('articles/'.$article_hash) );
        
        $error_message = __('Error get article in plugin Seo Blog Writer.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );

        if ( !$parsed_response['error'] ) {
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if ( $response_body['success'] ){
                $article = $response_body['data']['article'];
                
                $tags = [];
                $categories = [];
                if ( $article['tags'] ) {
                    $tags = explode(',', $article['tags']);
                }
                if ( $article['categories'] ) {
                    $categories = explode(',', $article['categories']);
                }

                $scwriter_post = get_post( $scwriter_post_id );
                
                $seo_title = $article['seo_title'] ?? $article['title'];

                $content = $article['content'];
                if ( self::is_gutenberg_active() ) {
                    $content = self::convert_html_to_gutenberg_blocks( $article['content'] );
                }
                
                $post_data = array(
                    'post_author' => $scwriter_post->post_author,
                    'post_title' => $article['title'],
                    'post_name' => sanitize_title($seo_title),
                    'post_content' => $content,
                    'post_status' => 'draft',
                    'post_type' => 'post',
                );

                if ( isset($article['seo_description']) ) {
                    $post_data['post_excerpt'] = $article['seo_description'];
                }
                if ( $tags ) {
                    $post_data['tags_input'] = self::get_taxonomy_ids( $tags, 'post_tag' );
                }

                if ( $categories ) {

                    $scwriter_post__post_data = get_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_post_data', true );

                    $need_create = false;

                    if ( isset($scwriter_post__post_data['enable_categories']) && $scwriter_post__post_data['enable_categories'] == 1 ){

                        if ( isset($scwriter_post__post_data['enable_categories_create_new']) && $scwriter_post__post_data['enable_categories_create_new'] == 1 ) {
                            $need_create = true;
                        }
                        
                        $post_data['post_category'] = self::get_taxonomy_ids( $categories, 'category', $need_create );
                        
                    }
                    
                }

                $post_id = wp_insert_post( $post_data );
                if (!is_wp_error($post_id)) {
                    
                    $post_meta_updater = new SCwriter_PostMetaDataUpdater( $post_id );
                    
                    if ( $article['seo_title'] ) {
                        $post_meta_updater->set_meta_title( $seo_title );
                    }
                    if ( $article['seo_description'] ) {
                       $post_meta_updater->set_meta_description( $article['seo_description'] );
                    }
                    if ( $article['primary_keyword'] ) {
                        $post_meta_updater->set_focus_keyword( $article['primary_keyword'] );
                    }
                    
                    update_post_meta( $post_id, SCWRITER_PREFIX.'_article_id', $article_hash );
                    
                    // remove duplicates
                    $keywords_array = explode(',', $article['primary_keyword'].','.$article['secondary_keywords'].','.$article['long_tail_keywords'].','.$article['nlp_keywords']);
                    $keywords_array = array_map('trim', $keywords_array);
                    $keywords_array = array_unique($keywords_array);
                    
                    update_post_meta( $post_id, 'keywords', implode(', ', $keywords_array) );

                    update_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_status', SCwriter_Status::COMPLETED );
                    update_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_wp_post_id', $post_id );

                    self::update_scwriter_title( $scwriter_post_id, $article['title'] );

                    self::sendEmail( $post_id, $scwriter_post_id, $article['title'] );
                    
                } else {
                    $error_message = $post_id->get_error_message();
                    error_log('Error creating post in plugin Seo Blog Writer: '. $error_message);
                }

            }

        }
        
    }

    public static function sendEmail( int $post_id, int $scwriter_post_id, string $post_title = '' ) : void {
        
        $created_by_user = get_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_created_by_user', true );
        $user_email = '';
        if ( $created_by_user == '1' ) {
            $user_id = get_post_field('post_author', $post_id);
            $user_email = get_the_author_meta( 'user_email', $user_id );
        } else {
            $settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
            if ( isset($settings['automated_author']) && $settings['automated_author'] ) {
                $user_email = get_the_author_meta( 'user_email', $settings['automated_author'] );
            } else {
                $user_id = isset($settings['author']) ? (int) $settings['author'] : null;
                if ( $user_id ) {
                    $user_email = get_the_author_meta( 'user_email', $user_id );
                }
            }
        }

        if ( $user_email ) {

            if ( !$post_title ) {
                $post_title = get_the_title( $post_id );
            }
            $post_author_id = get_post_field( 'post_author', $post_id );
            $recipient = get_the_author_meta( 'display_name', $post_author_id );

            $edit_link = admin_url( 'post.php?post='. $post_id .'&action=edit' );
            $subject = __('Your Draft Article is Ready:', 'scwriter') . ' '. $post_title;

            /* translators: %s: name */
            $body = sprintf(__('Hi %s', 'scwriter'), $recipient) . ",\n\n";
            /* translators: %s: post title */
            $body .= sprintf(__('Your post — “%s” — is ready for your review. Check it out here', 'scwriter'), $post_title) . "\n\n";

            $body .= $edit_link."\n\n\n";

            $body .= __('Before you hit publish, here are a few things to look over:', 'scwriter') . "\n";
                $body .= __('1. External & Internal Links: Make sure all the links are good to go.', 'scwriter') . "\n";
                $body .= __('2. SEO Tips: Double-check for SEO. Look at keywords, meta descriptions, and internal links to boost your post’s performance.', 'scwriter') . "\n";
                $body .= __('3. Content Check: Ensure everything is accurate and up-to-date. Verify any stats, quotes, or references included.', 'scwriter') . "\n";
                $body .= __('4. Tone and Style: Make sure the post fits your brand’s vibe.', 'scwriter') . "\n\n";

            $body .= __('Cheers', 'scwriter') . ",\n";
            $body .= SCWRITER_NAME . "\n\n";

            $body .= __('P.S. If you have any questions or need help, just let us know!', 'scwriter') . "\n";

            $article_id = get_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_article_id', true );
            $feedback_url = "https://seocontentwriter.actlys.com/feedback/?post=$article_id&utm_source=email&utm_medium=postcreated";
            /* translators: %s: feedback link URL */
            $body .= sprintf(__('Feedback: %s', 'scwriter'), $feedback_url) . "\n\n";

            wp_mail( $user_email, $subject, $body);

        }

    }

	public static function update_status( int $scwriter_post_id, array $article_data ) : void {

        update_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_status', $article_data['status'] );
        update_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_last_error', $article_data['last_error'] );
        update_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_completed_percentage', $article_data['completed_percentage'] );

        if ( isset( $article_data['title'] ) && $article_data['title'] ) {

            self::update_scwriter_title( $scwriter_post_id, $article_data['title'] );

        }

    }

    /**
	 * Update SBW post title
	 */
	public static function update_scwriter_title( int $scwriter_post_id, string $title ) : void {

        $created_by_trend = get_post_meta( $scwriter_post_id, SCWRITER_PREFIX.'_created_by_trend', true );

        if ( $created_by_trend ) {
            $update_args = array(
                'ID' => $scwriter_post_id,
                'post_title' => $title,
                'post_modified' => current_time('mysql'), // Update post modified time
                'do_not_create' => true // Prevent creation of a revision
            );

            wp_update_post($update_args);
        }

    }

    public static function get_article_info( string $article_id, int $retry = 1 ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => '',
            'retry' => $retry,
        );

        $args = array(
            'body'        => '',
            'timeout'     => 60,
            'headers'     => array(
                'Content-Type' => 'application/json'
            ),
        );

        $response = wp_remote_get( self::get_api_url('articles/' . $article_id), $args);
        
        $error_message = esc_html__('An error occurred while creating draft.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ( $parsed_response['error'] ) {

            $need_repeat = true;

            if ( $parsed_response['code'] == 'not_found' && $retry < 100 ) {
                sleep(10);                
                return $toReturn;
            }

            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];

        } else {

            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            $toReturn['data'] = self::prepare_draft( $response_body['data']['article'] );
            $toReturn['message'] = esc_html__('Draft successfully created.', 'scwriter');

        }

        return $toReturn;

    }

    private static function strip_outlines( string $outline ) : string {

        $allowed_tags = ['<h2>', '<h3>', '<ul>', '<ol>', '<li>', '<p>'];
        $clean_outline = strip_tags( $outline, $allowed_tags );

        return $clean_outline;

    }

    private static function prepare_draft( array $data ) : array {
        
        $toReturn = array(
            'title' => '',
            'outline' => '',
            'primary_keyword' => '',
            'secondary_keywords' => '',
        );

        if ( isset($data['outlines']) ) {
            $toReturn['outlines'] = self::strip_outlines( $data['outlines'] );
        }
        if ( isset($data['title']) ) {
            $toReturn['title'] = sanitize_text_field( $data['title'] );
        }
        if ( isset($data['primary_keyword']) ) {
            $toReturn['primary_keyword'] = sanitize_text_field( $data['primary_keyword'] );
        }

        $secondary_keywords = [];
        if ( isset($data['secondary_keywords']) && $data['secondary_keywords'] ) {
            $secondary_keywords[] = $data['secondary_keywords'];
        }
        if ( isset($data['long_tail_keywords']) && $data['long_tail_keywords'] ) {
            $secondary_keywords[] = $data['long_tail_keywords'];
        }
        if ( isset($data['nlp_keywords']) && $data['nlp_keywords'] ) {
            $secondary_keywords[] = $data['nlp_keywords'];
        }

        if ( $secondary_keywords ) {
            $toReturn['secondary_keywords'] = implode(',', $secondary_keywords);
        }


        return $toReturn;

    }

    private static function has_content($node) {
		return trim($node->nodeValue) !== '';
	}

    public static function convert_html_to_gutenberg_blocks( string $html ) : string {

        libxml_use_internal_errors(true);
		$dom = new \DOMDocument();
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Document</title></head><body>'.$html.'</body></html>';
		@$dom->loadHTML($html);
        libxml_clear_errors();

		$output = '';
	
		foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
			// Ignore comments like <!--googleoff: index-->
			if ($node->nodeType === XML_COMMENT_NODE) {
				continue;
			}
	
			switch ($node->nodeName) {
	
				case (preg_match('/^h[1-6]$/', $node->nodeName) ? true : false):
					if (self::has_content($node)) {
						$level = substr($node->nodeName, 1);
                        $innerHtml = '';

                        // Get the inner HTML of the heading tag (children only, no outer <h#> tags)
                        foreach ($node->childNodes as $child) {
                            $innerHtml .= $dom->saveHTML($child);
                        }

                        // Output the Gutenberg block with level and class
                        $output .= '<!-- wp:heading {"level":' . $level . '} -->' . PHP_EOL;
                        $output .= '<' . $node->nodeName . ' class="wp-block-heading">' . $innerHtml . '</' . $node->nodeName . '>' . PHP_EOL;
                        $output .= '<!-- /wp:heading -->' . PHP_EOL . PHP_EOL;
					}
					break;
	
				case 'section':
                    if (self::has_content($node)) {
                        $class_name = $node->getAttribute('class');

                        foreach ($node->childNodes as $child) {
                            if ( $child->nodeName === 'h3' ) {
                                $h3_class_name = $class_name . '__title';
                                $innerHtml = $dom->saveHTML($child);
                                $innerHtml = str_replace( '<h3', '<h3 class="wp-block-heading '.$h3_class_name.'"', $innerHtml );
                                $output .= '<!-- wp:heading {"level":3} -->' . PHP_EOL;
                                $output .= $innerHtml . PHP_EOL;
                                $output .= '<!-- /wp:heading -->' . PHP_EOL . PHP_EOL;
                            } elseif ( $child->nodeName === 'ul' ) {
                                $ul_class_name = $class_name . '__list';
                                $output .= self::parseUl( $child, $dom, $ul_class_name );
                            }
                        }
                    }
                    break;

				case 'ul':
					$output .= self::parseUl( $node, $dom );
					break;
	
				case 'ol':
					if ($node->hasChildNodes()) {
						$output .= '<!-- wp:list {"ordered":true} -->' . PHP_EOL;
                        $output .= '<ol class="wp-block-list">' . PHP_EOL;
                
                        // Loop through <li> items inside <ol>
                        foreach ($node->childNodes as $child) {
                            if ($child->nodeName === 'li') {
                                $innerLiHtml = '';
                
                                // Get the inner HTML of each <li> item
                                foreach ($child->childNodes as $liChild) {
                                    $innerLiHtml .= $dom->saveHTML($liChild);
                                }
                
                                // Output each list item in Gutenberg format
                                $output .= '<!-- wp:list-item -->' . PHP_EOL;
                                $output .= '<li>' . $innerLiHtml . '</li>' . PHP_EOL;
                                $output .= '<!-- /wp:list-item -->' . PHP_EOL;
                            }
                        }
                
                        $output .= '</ol>' . PHP_EOL;
                        $output .= '<!-- /wp:list -->' . PHP_EOL . PHP_EOL;
                    }
					break;

				case 'hr':
					$output .= '<!-- wp:separator -->' . PHP_EOL;
						$output .= '<hr />' . PHP_EOL;
					$output .= '<!-- /wp:separator -->' . PHP_EOL . PHP_EOL;
					break;
	
				case 'p':

                    $hasImage = false;
					foreach ($node->childNodes as $pChild) {
						if ($pChild->nodeName === 'figure') {
							$output .= self::parseFigure( $pChild, $dom );
							$hasImage = true;
							break;
						}
					}
					
					if (!$hasImage && self::has_content($node)) {

						$output .= '<!-- wp:paragraph -->' . PHP_EOL;
						$output .= $dom->saveHTML($node) . PHP_EOL;
						$output .= '<!-- /wp:paragraph -->' . PHP_EOL. PHP_EOL;

					}

					break;

                case ($node->nodeName === 'figure'):
                    if ($node->hasChildNodes()) {
                        $output .= self::parseFigure( $node, $dom );
                    }
                    break;
	
				default:
					if (self::has_content($node)) {
						$output .= '<!-- wp:html -->' . PHP_EOL;
						$output .= $dom->saveHTML($node) . PHP_EOL;
						$output .= '<!-- /wp:html -->' . PHP_EOL. PHP_EOL;
					}
					break;
			}
		}
	
		return $output;
	}

    private static function parseUl( $node, $dom, string $class_name = '' ) : string {
        
        $output = '';

        if (self::has_content($node)) {
            if ($node->hasChildNodes()) {
                $output .= '<!-- wp:list -->' . PHP_EOL;
                $output .= '<ul class="wp-block-list '.$class_name.'">' . PHP_EOL;
        
                // Loop through <li> items inside <ul>
                foreach ($node->childNodes as $child) {
                    if ($child->nodeName === 'li') {
                        $innerLiHtml = '';
        
                        // Get the inner HTML of each <li> item
                        foreach ($child->childNodes as $liChild) {
                            $innerLiHtml .= $dom->saveHTML($liChild);
                        }
        
                        // Output each list item in Gutenberg format
                        $output .= '<!-- wp:list-item -->' . PHP_EOL;
                        $output .= '<li>' . $innerLiHtml . '</li>' . PHP_EOL;
                        $output .= '<!-- /wp:list-item -->' . PHP_EOL;
                    }
                }
        
                $output .= '</ul>' . PHP_EOL;
                $output .= '<!-- /wp:list -->' . PHP_EOL . PHP_EOL;
            }
        }

        return $output;

    }

    private static function parseFigure( $node, $dom ) : string {

        $output = '';
        $alignClass = '';
        $additionalClasses = [];
        
        // Check for alignment classes
        if (strpos($node->getAttribute('class'), 'aligncenter') !== false) {
            $alignClass = 'center';
            $additionalClasses[] = 'aligncenter';
        } elseif (strpos($node->getAttribute('class'), 'alignright') !== false) {
            $alignClass = 'right';
            $additionalClasses[] = 'alignright';
        } elseif (strpos($node->getAttribute('class'), 'alignleft') !== false) {
            $alignClass = 'left';
            $additionalClasses[] = 'alignleft';
        } elseif (strpos($node->getAttribute('class'), 'alignfull') !== false) {
            $alignClass = 'full';
            $additionalClasses[] = 'alignfull';
        } elseif (strpos($node->getAttribute('class'), 'alignwide') !== false) {
            $alignClass = 'wide';
            $additionalClasses[] = 'alignwide';
        }

        // Add any additional classes found
        $additionalClasses[] = 'wp-caption';

        $output .= '<!-- wp:image {"linkDestination":"custom","align":"' . $alignClass . '","className":"' . implode(' ', $additionalClasses) . '"} -->' . PHP_EOL;

        // Start figure block
        $output .= '<figure class="wp-block-image ' . implode(' ', $additionalClasses) . '">' . PHP_EOL;

        // Find the <img> element within the <figure>
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'img') {
                // Get the image attributes
                $imgSrc = $child->getAttribute('src');
                $imgAlt = $child->getAttribute('alt');

                // Output the image tag
                $output .= '<img src="' . htmlspecialchars($imgSrc) . '" alt="' . htmlspecialchars($imgAlt) . '"/>' . PHP_EOL;
            }

            // Check for <figcaption> to process the caption
            if ($child->nodeName === 'figcaption') {
                $innerCaptionHtml = '';

                // Get the inner HTML of the figcaption
                foreach ($child->childNodes as $captionChild) {
                    $innerCaptionHtml .= $dom->saveHTML($captionChild);
                }

                // Output the figcaption
                $output .= '<figcaption class="wp-element-caption">' . PHP_EOL;
                $output .= $innerCaptionHtml;
                $output .= '</figcaption>' . PHP_EOL;
            }
        }

        // Close the figure block
        $output .= '</figure>' . PHP_EOL;
        $output .= '<!-- /wp:image -->' . PHP_EOL . PHP_EOL;

        return $output;
    }
	
	private static function is_gutenberg_active() {
		$gutenberg    = false;
		$block_editor = false;

		if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
			// Gutenberg is installed and activated.
			$gutenberg = true;
		}

		if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
			// Block editor.
			$block_editor = true;
		}

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		$use_block_editor = ( get_option( 'classic-editor-replace' ) === 'block' );

		return $use_block_editor;
	}

    /**
	 * Silent mode if user_id sent
	 */
    public static function create_scwriter_post( array $data, int $user_id = PHP_INT_MIN ) : array {
        
        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => ''
        );

        $silent_mode = false;

        if ( $user_id == PHP_INT_MIN ) {
            $user_id = get_current_user_id();
        } else {
            $silent_mode = true;
        }

        $prepared_post_data = self::prepare_post_data( $data );
        $json_data = $prepared_post_data['json_data'];
        $is_draft = isset($json_data['enable_preview_outline']) && $json_data['enable_preview_outline'] == '1';
        $is_draft_publishing = isset($json_data['outlines']) && $json_data['outlines'];
        
        $args = array(
            'body'        => wp_json_encode( $json_data, JSON_UNESCAPED_UNICODE ),
            'timeout'     => 60,
            'method'      => 'POST',
            'headers'     => array(
                'Content-Type' => 'application/json'
            ),
        );
        
        $api_url = 'articles';


        if ( $is_draft_publishing ) {
            $article_id = sanitize_text_field($data['article_id']);
            $args['method'] = 'PUT';
            $api_url = 'articles/' . $article_id;
        }

        $response = wp_remote_request( self::get_api_url($api_url), $args);

        $error_message = __('An error occurred while adding the post to the queue.', 'scwriter');
        $parsed_response = self::get_response( $response, $error_message );
        
        if ( $parsed_response['error'] ) {

            $toReturn['error'] = true;
            $toReturn['error_message'] = $parsed_response['error_message'];

        } else {

            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if ( $is_draft && !$is_draft_publishing ){

                $toReturn['article_id'] = $response_body['data']['article_id'];

            } else {
                $toReturn['message'] = __('The post is queued. You can refresh the page to see the progress. Once the post is ready, you will receive an email notification.', 'scwriter');

                $post_title = $json_data['primary_keyword'] ? $json_data['primary_keyword'] : esc_html__('Automatically created post', 'scwriter');
                $post_data = array(
                    'post_title'    => $post_title,
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'post_author'   => $user_id,
                    'post_type'     => SCwriter_CPT::POST_TYPE,
                );
                $post_id = wp_insert_post( $post_data );
                add_post_meta( $post_id, SCWRITER_PREFIX.'_article_id', $response_body['data']['article_id'] );
                add_post_meta( $post_id, SCWRITER_PREFIX.'_status', SCwriter_Status::QUEUED );
                add_post_meta( $post_id, SCWRITER_PREFIX.'_created_by_trend', $json_data['primary_keyword'] ? '0' : '1' );
                add_post_meta( $post_id, SCWRITER_PREFIX.'_created_by_user', $silent_mode ? '0' : '1' );
                add_post_meta( $post_id, SCWRITER_PREFIX.'_post_data', $prepared_post_data['post_data_to_save'] );

                delete_transient( SCwriter_Settings::USAGE_TRANSIENT );

                if ( !$silent_mode ) {
                    update_option(SCWRITER_PREFIX.'_form_messages', $toReturn, 'no');
                    $toReturn['redirect_to'] = admin_url( 'edit.php?post_type=' . SCwriter_CPT::POST_TYPE . '&all_posts=1' );
                }
            }
        }

        return $toReturn;

    }

	public static function prepare_post_data( array $data ) : array {
        
        $primary_keyword = sanitize_text_field($data['primary_keyword']);
        
        $title = isset($data['title']) ? sanitize_text_field($data['title']) : '';

        $secondary_keywords = isset($data['secondary_keywords']) ? self::convert_textarea_to_array(sanitize_text_field($data['secondary_keywords'])) : '';

        $json_data = array(
            'primary_keyword' => $primary_keyword,
            'internal_links' => self::get_posts(),
        );

        unset($data['secondary_keywords']);
        $prepared_data = SCwriter_Presets::prepare_array( $data );
        unset($prepared_data['preset_name']);

        $article_length_min = 0;
        $article_length_max = 0;
        
        if ( $prepared_data['article_length'] != 0 && $prepared_data['article_length'] != 'article_length_custom' ) {
            $article_length = explode(',',$prepared_data['article_length']);
            $article_length_min = $article_length[0];
            $article_length_max = $article_length[1] ?? $article_length[0];
        } else {
            if ( $prepared_data['article_length'] == 'article_length_custom' && isset($prepared_data['article_length_custom']) ) {
                $article_length_min = $prepared_data['article_length_custom'];
            }
        }
        if ( isset($prepared_data['article_length_custom']) ) {
            unset($prepared_data['article_length_custom']);
        }
        $prepared_data['article_length'] = array(
            'min' => $article_length_min,
            'max' => $article_length_max
        );

        if ( isset($prepared_data['external_links_stop_words']) ) {
            $prepared_data['external_links_stop_words'] = self::convert_textarea_to_array($prepared_data['external_links_stop_words']);
        }
        if ( isset($prepared_data['stop_words']) ) {
            $prepared_data['stop_words'] = self::convert_textarea_to_array($prepared_data['stop_words']);
        }
        
        $json_data = array_merge( $json_data, $prepared_data );
        
        if ( $title && !empty($title)) {
            $json_data['title'] = $title;
        }

        if ( $secondary_keywords ) {
            $json_data['secondary_keywords'] = $secondary_keywords;
        }

        $settings = get_option( SCwriter_Settings::SETTINGS_OPTION_NAME );
        $json_data['blog_topic'] = $settings['blog_topic'];

        if ( isset($data['enable_categories']) && $data['enable_categories'] == 1 ){
            $json_data['categories'] = self::get_categories();
        }
        if ( isset($data['enable_tags']) &&  $data['enable_tags'] == 1 ){
            $json_data['tags'] = self::get_tags();
        }
        $json_data['enable_categories_create_new'] = 0;
        if ( isset($data['enable_categories_create_new']) && $data['enable_categories_create_new'] == 1 ) {
            $json_data['enable_categories_create_new'] = 1;
        }

        if ( isset($data['outlines']) && $data['outlines'] ) {
            $json_data['outlines'] = self::strip_outlines( $data['outlines'] );
            $json_data['enable_preview_outline'] = '1';
        }

        $post_data_to_save = $json_data;
        unset($post_data_to_save['internal_links']);
        unset($post_data_to_save['categories']);
        unset($post_data_to_save['tags']);
        $post_data_to_save['enable_categories'] = isset($data['enable_categories']) && $data['enable_categories'] == 1 ? 1 : 0;

        $toReturn = array(
            'post_data_to_save' => $post_data_to_save,
            'json_data' => $json_data,
        );

        return $toReturn;

	}

    /**
	 * Creating automatic scwriter post with error limit exceeded
	 */
    public static function create_scwriter_post_limit( string $trend, int $user_id, array $usage ) : void {
		
        $post_title = $trend ? sanitize_text_field($trend) : esc_html__('Automatically created post', 'scwriter');

        $post_data = array(
            'post_title'    => $post_title,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => $user_id,
            'post_type'     => SCwriter_CPT::POST_TYPE,
        );
        $post_id = wp_insert_post( $post_data );

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
        add_post_meta( $post_id, SCWRITER_PREFIX.'_status', SCwriter_Status::FAILED );
        add_post_meta( $post_id, SCWRITER_PREFIX.'_created_by_trend', 1 );
        add_post_meta( $post_id, SCWRITER_PREFIX.'_created_by_user', 0 );
        add_post_meta( $post_id, SCWRITER_PREFIX.'_last_error', $error_message );

	}

    public static function convert_textarea_to_array( string $text ) : array {

        $array = array();

        $exploded_value = preg_split('/[\n,]+/', $text);

        foreach ($exploded_value as $value) {
            $trimmed_value = trim($value);
            if ($trimmed_value !== '') {
                $array[] = $trimmed_value;
            }
        }

        return $array;

    }

    private static function get_posts() : array {

        $posts = get_posts( array(
            'numberposts'       => self::COUNT_POSTS,
            'orderby'           => 'date',
            'order'             => 'DESC',
            'post_type'         => 'post',
            'post_status'       => 'publish',
        ) );

        $posts_array = array();

        if( $posts ){
            foreach ( $posts as $post_item ) {
                $posts_array[] = array(
                    'title' => $post_item->post_title,
                    'url' => get_permalink($post_item->ID),
                );
            }
        }

        return $posts_array;
        
    }

    private static function get_taxonomy_ids( array $taxonomy_names, string $taxonomy, bool $need_create = true ) : array {

        $taxonomy_ids = array();

        foreach ($taxonomy_names as $taxonomy_name) {

            $existing_taxonomy = get_term_by('name', $taxonomy_name, $taxonomy);

            if ($existing_taxonomy) {
                $taxonomy_ids[] = $existing_taxonomy->term_id;
            } elseif ( $need_create ) {
                $new_taxonomy = wp_insert_term($taxonomy_name, $taxonomy);

                if (!is_wp_error($new_taxonomy)) {
                    $taxonomy_ids[] = $new_taxonomy['term_id'];
                }
            }

        }

        return $taxonomy_ids;

    }

    private static function get_categories() : array {

        $categories = get_terms( [
            'taxonomy'     => 'category',
            'type'         => 'post',
            'orderby'      => 'count',
            'order'        => 'DESC',
            'hide_empty'   => 1,
            'number'       => self::COUNT_CATEGORIES,
        ] );
        
        $categories_array = array();

        if( $categories ){
            foreach( $categories as $cat ){
                $categories_array[] = $cat->name;
            }
        }

        return $categories_array;

    }

    private static function get_tags() : array {

        $tags = get_terms( [
            'taxonomy'     => 'post_tag',
            'type'         => 'post',
            'orderby'      => 'count',
            'order'        => 'DESC',
            'hide_empty'   => 1,
            'number'       => self::COUNT_CATEGORIES,
        ] );
        
        $tags_array = array();

        if( $tags ){
            foreach( $tags as $tag ){
                $tags_array[] = $tag->name;
            }
        }

        return $tags_array;

    }

    public static function scwriter_post_action( array $data ) : array {

        $toReturn = array(
            'error' => false,
            'error_message' => '',
            'message' => '',
        );

        $post_id = sanitize_text_field( $data['post_id'] );
        $type = sanitize_text_field( $data['type'] );

        $post_title = get_the_title($post_id);

        if ( $type == 'delete' ) {

            $result = wp_delete_post($post_id, true);
            if ( $result ) {
                $toReturn['message'] = sprintf(__('The post has been deleted successfully.', 'scwriter'), $post_title);
            } else {
                $toReturn['error_message'] = sprintf(__('Failed to delete the post.', 'scwriter'), $post_title);
                $toReturn['error'] = true;
            }

        } elseif ( $type == 'restart' ) {

            $article_id = get_post_meta( $post_id, SCWRITER_PREFIX.'_article_id', true );

            if ( $article_id ){

                $args = array(
                    'body'        => wp_json_encode( [], JSON_UNESCAPED_UNICODE ),
                    'timeout'     => 60,
                    'method'      => 'PUT',
                    'headers'     => array(
                        'Content-Type' => 'application/json'
                    ),
                );
                
                $api_url = 'articles/'.$article_id.'/restart/';
        
                $response = wp_remote_request( self::get_api_url($api_url), $args);
        
                $error_message = sprintf(__('An error occurred while restarting the post.', 'scwriter'), $post_title);
                $parsed_response = self::get_response( $response, $error_message );

                if ( $parsed_response['error'] ) {

                    $toReturn['error'] = true;
                    $toReturn['error_message'] = $parsed_response['error_message'];
        
                } else {
        
                    $toReturn['message'] = sprintf(__('The post has been restarted successfully.', 'scwriter'), $post_title);
                    update_post_meta( $post_id, SCWRITER_PREFIX.'_status', SCwriter_Status::QUEUED );

                }


            } else {
                $toReturn['error'] = true;
                $toReturn['error_message'] = sprintf(__('There is an issue with the post data.', 'scwriter'), $post_title);
            }

        }

        return $toReturn;

    }

}

?>