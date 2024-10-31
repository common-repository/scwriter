<?php

namespace SCwriter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SCwriter\Enums\SCwriter_Status;

class SCwriter_CPT
{
    const POST_TYPE = SCWRITER_PREFIX . '_post';

	public static function init() : void {
		add_action( 'init', __CLASS__ . '::custom_post_type' );
        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', __CLASS__ . '::custom_columns_header' );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', __CLASS__ . '::custom_columns_content', 10, 2 );
        add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', __CLASS__ . '::date_sortable' );
        add_action( 'pre_get_posts', __CLASS__ . '::date_orderby' );
        add_action( 'admin_notices', __CLASS__ . '::custom_posts_list_message');
        add_action('restrict_manage_posts',  __CLASS__ . '::custom_add_custom_column_filter');
        add_action('parse_query', __CLASS__ . '::custom_filter_by_custom_column');
        add_filter( 'bulk_actions-edit-' . self::POST_TYPE, __CLASS__ . '::remove_draft_from_bulk_actions', 99 );
        add_filter( 'post_row_actions', __CLASS__ . '::remove_row_actions', 99, 1 );
        add_filter('views_edit-' . self::POST_TYPE, __CLASS__ . '::update_quicklinks');
	}

	/**
	 * Remove bluk action button
	 */
    public static function remove_draft_from_bulk_actions( array $actions ) : array {
        return [];
    }

	/**
	 * Remove top quick links
	 */
    public static function update_quicklinks($views) {

        unset($views['publish']);
        unset($views['draft']);
        unset($views['trash']);

        return $views;

    }

	/**
	 * Remove action links from items
	 */
    public static function remove_row_actions( $actions )
    {
        if( get_post_type() === self::POST_TYPE ){
            $actions = [];
        }
        return $actions;
    }

	/**
	 * Register CPT
	 */
	public static function custom_post_type() : void {
		
		$labels = array(
            'name'                  => _x( 'AI Posts', 'Post Type General Name', 'scwriter' ),
            'singular_name'         => _x( 'AI Post', 'Post Type Singular Name', 'scwriter' ),
            'menu_name'             => __( 'AI Post', 'scwriter' ),
            'name_admin_bar'        => __( 'AI Post', 'scwriter' ),
            'archives'              => __( 'AI Posts', 'scwriter' ),
            'attributes'            => __( 'AI Posts', 'scwriter' ),
            'parent_item_colon'     => __( 'Parent AI Post:', 'scwriter' ),
            'all_items'             => __( 'All AI Posts', 'scwriter' ),
            'add_new_item'          => __( 'Add New AI Post', 'scwriter' ),
            'add_new'               => __( 'Add New', 'scwriter' ),
            'new_item'              => __( 'New AI Post', 'scwriter' ),
            'edit_item'             => __( 'Edit AI Post', 'scwriter' ),
            'update_item'           => __( 'Update AI Post', 'scwriter' ),
            'view_item'             => __( 'View AI Post', 'scwriter' ),
            'view_items'            => __( 'View AI Posts', 'scwriter' ),
            'search_items'          => __( 'Search AI Post', 'scwriter' ),
            'not_found'             => __( 'Not found', 'scwriter' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'scwriter' ),
            'featured_image'        => __( 'Featured Image', 'scwriter' ),
            'set_featured_image'    => __( 'Set featured image', 'scwriter' ),
            'remove_featured_image' => __( 'Remove featured image', 'scwriter' ),
            'use_featured_image'    => __( 'Use as featured image', 'scwriter' ),
            'insert_into_item'      => __( 'Insert into AI Post', 'scwriter' ),
            'uploaded_to_this_item' => __( 'Uploaded to this AI Post', 'scwriter' ),
            'items_list'            => __( 'AI Posts list', 'scwriter' ),
            'items_list_navigation' => __( 'AI Posts list navigation', 'scwriter' ),
            'filter_items_list'     => __( 'Filter AI Posts list', 'scwriter' ),
        );
        $args = array(
            'label'                 => __( 'AI Post', 'scwriter' ),
            'description'           => __( 'AI Posts', 'scwriter' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
            'capabilities' => [
                'create_posts' => 'do_not_allow',
                'edit_post' => 'do_not_allow',
                'edit_others_posts' => 'do_not_allow',
                'publish_posts' => 'do_not_allow',
                'read_post' => 'do_not_allow',
                'read_private_posts' => 'do_not_allow',
                'delete_post' => 'do_not_allow'
            ],
        );
        register_post_type( self::POST_TYPE, $args );
        
	}

    public static function custom_posts_list_message() : void {
        global $pagenow;

        if ( $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == self::POST_TYPE ) {
            $scwriter_form_messages = get_option( SCWRITER_PREFIX.'_form_messages', false );
            if ( $scwriter_form_messages ) {
                if ( $scwriter_form_messages['error'] === true ) {
                    echo sprintf('<div class="notice notice-error"><p>%s</p></div>', esc_html($scwriter_form_messages['error_message']));
                } elseif ( $scwriter_form_messages['message'] ) {
                    echo sprintf('<div class="notice notice-success"><p>%s</p></div>', esc_html($scwriter_form_messages['message']));
                }
                delete_option(SCWRITER_PREFIX."_form_messages");
            }
        }
    }

    public static function date_sortable( array $columns ) : array {

        $columns[SCWRITER_PREFIX.'_date'] = SCWRITER_PREFIX.'_date';

        return $columns;

    }

    public static function date_orderby( $query ) {

        if( ! is_admin() )
            return;

        $orderby = $query->get( 'orderby');
        if( SCWRITER_PREFIX.'_date' == $orderby ) {
            $query->set('orderby','date');
        }

    }

    public static function custom_columns_header( array $columns ) : array {
        
        $date_column = $columns['date'];
        unset($columns['date']);
        unset($columns['cb']);

        $columns[SCWRITER_PREFIX.'_status'] = __('Status', 'scwriter');
        $columns[SCWRITER_PREFIX.'_post_link'] = __('Progress', 'scwriter');
        $columns[SCWRITER_PREFIX.'_author'] = __('Author', 'scwriter');
        $columns[SCWRITER_PREFIX.'_date'] = __('Date', 'scwriter');
        $columns[SCWRITER_PREFIX.'_action'] = __('Action', 'scwriter');

        return $columns;

    }

    public static function custom_columns_content( string $column_name, int $post_id ) {

        if ( $column_name === SCWRITER_PREFIX.'_status' ) {
            
            $status = get_post_meta( $post_id, SCWRITER_PREFIX.'_status', true );
            if ( $status ) {
                echo esc_html(SCwriter_Status::getName( $status ));
            } else {
                echo '-- // --';
            }

        } elseif ( $column_name === SCWRITER_PREFIX.'_post_link' ) {
            
            $status = get_post_meta( $post_id, SCWRITER_PREFIX.'_status', true );
            if ( $status == SCwriter_Status::COMPLETED ) {

                $wp_post_id = get_post_meta( $post_id, SCWRITER_PREFIX.'_wp_post_id', true );
                if ( $wp_post_id ) {
                    $edit_post_link = get_edit_post_link( $wp_post_id );
                    if ( $edit_post_link ) {
                        echo '<a href="'.esc_url($edit_post_link).'">'.esc_html__('View', 'scwriter').'</a>';
                    }
                }

            } elseif ( $status == SCwriter_Status::QUEUED ) {

                esc_html_e('~5 mins.', 'scwriter');

            } elseif ( $status == SCwriter_Status::IN_PROGRESS ) {
                
                $completed_percentage = get_post_meta( $post_id, SCWRITER_PREFIX.'_completed_percentage', true );

                echo esc_html(round(floatval($completed_percentage))).'% / 100%';

            } else {

                $last_error = get_post_meta( $post_id, SCWRITER_PREFIX.'_last_error', true );
                $post_id_hash = '';
                $show_additional_error = true;

                if ( $last_error ) {
                    $post_id_hash = get_post_meta( $post_id, SCWRITER_PREFIX.'_article_id', true );
                    if ( strpos($last_error, '<a') !== false ) {
                        $show_additional_error = false;
                        echo wp_kses(
                            $last_error,
                            array(
                                'a' => array( 'href' => array(), 'target' => array() )
                            )
                        );
                    } else {
                        echo esc_html($last_error);
                        echo "<br>";
                    }
                }
                if ( $show_additional_error ) {
                    $support_url = SCWRITER_WEBSITE . '/support/?post='.$post_id_hash.'&utm_source=scwriterplugin&utm_medium=postcreationerror';
                    /* translators: %s is the link to the support page */
                    $error_message = sprintf(
                        wp_kses(
                            /* Translators: %s is the link to support */
                            __('Please contact %s for further assistance.', 'scwriter'),
                            array(
                                'a' => array( 'href' => array(), 'target' => array() )
                            )
                        ),
                        '<a href="' . esc_url( $support_url ) . '" target="_blank">' . __('support', 'scwriter') . '</a>'
                    );
                    echo $error_message;
                }

            }

        } elseif ( $column_name === SCWRITER_PREFIX.'_action' ) {

            $status = get_post_meta( $post_id, SCWRITER_PREFIX.'_status', true );
            if ( $status == SCwriter_Status::COMPLETED ) {

                $wp_post_id = get_post_meta( $post_id, SCWRITER_PREFIX.'_wp_post_id', true );
                if ( $wp_post_id ) {
                    $edit_post_link = get_edit_post_link( $wp_post_id );
                    if ( $edit_post_link ) {
                        echo '<a href="'.esc_url($edit_post_link).'">'.esc_html__('View', 'scwriter').'</a>';
                    }
                }

            } elseif ( in_array($status, [SCwriter_Status::FAILED, SCwriter_Status::EXPIRED] ) ) {

                $delete_button = '<a href="#restart" class="button button-secondary scwriter-post-action scwriter-post-restart" data-confirm="'.esc_html__('Are you sure you want to restart the creation of this post?', 'scwriter').'" data-type="restart" data-postid="'.$post_id.'">'.esc_html__('Restart', 'scwriter').'<span class="scwriter-loading"></span></a>';
                echo $delete_button;

                $delete_button = '<a href="#delete" class="button button-secondary scwriter-post-action scwriter-post-delete" data-confirm="'.esc_html__('Are you sure you want to delete this post? You can\'t undo this.', 'scwriter').'" data-type="delete" data-postid="'.$post_id.'">'.esc_html__('Delete', 'scwriter').'<span class="scwriter-loading"></span></a>';
                echo $delete_button;

            }
        
        } elseif ( $column_name === SCWRITER_PREFIX.'_date' ) {
            
            $date = get_the_time( get_option('date_format'), $post_id );
            $time = get_the_time( get_option('time_format'), $post_id );

            /* translators: %1$s: date, %2$s: time */
            printf(esc_html__('%1$s at %2$s', 'scwriter'), esc_html($date), esc_html($time));

        } elseif ( $column_name === SCWRITER_PREFIX.'_author' ) {
            
            $author_name = get_post_field('post_author', $post_id);
            if ($author_name) {
                $author_name = get_the_author_meta('display_name', $author_name);
                echo esc_html($author_name);
            } else {
                esc_html_e('No Author', 'scwriter');
            }

        }

    }

    public static function custom_filter_by_custom_column( $query ) : void {
        
        global $pagenow;
        $post_type = self::POST_TYPE;
    
        if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == self::POST_TYPE && isset($_GET[SCWRITER_PREFIX.'_status']) && $_GET[SCWRITER_PREFIX.'_status'] != '') {

            $status_raw = sanitize_text_field($_GET[SCWRITER_PREFIX.'_status']);
            $all_statuses = SCwriter_Status::getAllStatuses();
            if ( isset($all_statuses[$status_raw]) ) {
                $status = $status_raw;
            } else {
                $status = '';
            }
        
            if ( $status ) {
                $query->query_vars['meta_key'] = SCWRITER_PREFIX.'_status';
                $query->query_vars['meta_value'] = $status;
            }
        }

    }

    public static function custom_add_custom_column_filter() : void {

        global $typenow;
    
        if ( $typenow == self::POST_TYPE ) {

            $selected_raw = isset($_GET[SCWRITER_PREFIX.'_status']) ? sanitize_text_field($_GET[SCWRITER_PREFIX.'_status']) : '';

            $all_statuses = SCwriter_Status::getAllStatuses();
            if ( isset($all_statuses[$selected_raw]) ) {
                $selected = $selected_raw;
            } else {
                $selected = '';
            }
    
            echo '<select name="scwriter_status">';
                echo '<option value="">' . esc_html__('All statuses', 'scwriter') . '</option>';
                foreach ($all_statuses as $value => $label) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($value),
                        selected($selected, $value, false),
                        esc_html($label)
                    );
                }
            echo '</select>';
        }

    }
    

}

?>