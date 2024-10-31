<?php

namespace SCwriter\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once(ABSPATH.'wp-admin/includes/plugin.php');

use SCwriter\Plugins\SCwriter_Yoast;
use SCwriter\Plugins\SCwriter_Smartcrawl;

class SCwriter_PostMetaDataUpdater {

    protected $post_id = null;
    protected $seo_plugin = '';

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
        $this->detectSeoPlugin();
    }

	protected function detectSeoPlugin() : void {

		if ( \is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$this->seo_plugin = 'yoast';
		} elseif ( \is_plugin_active( 'smartcrawl-seo/wpmu-dev-seo.php' ) || \is_plugin_active( 'wpmu-dev-seo/wpmu-dev-seo.php' ) ) {
			$this->seo_plugin = 'smartcrawl';
		} else {
            $this->seo_plugin = 'meta';
        }

	}

	public function set_meta_description( string $description ) : void {


        if ( $this->seo_plugin == 'yoast' ) {
            SCwriter_Yoast::set_meta_description( $this->post_id, $description );
        } elseif ( $this->seo_plugin == 'smartcrawl' ) {
            SCwriter_Smartcrawl::set_meta_description( $this->post_id, $description );
        } else {
            update_post_meta( $this->post_id, 'seo_meta_description', $description );
        }

	}
	
	public function set_meta_title( string $title ) : void {

        if ( $this->seo_plugin == 'yoast' ) {
            SCwriter_Yoast::set_meta_title( $this->post_id, $title );
        } elseif ( $this->seo_plugin == 'smartcrawl' ) {
            SCwriter_Smartcrawl::set_meta_title( $this->post_id, $title );
        } else {
            update_post_meta( $this->post_id, 'seo_meta_title', $title );
        }

	}
	
	public function set_focus_keyword( string $primary_keyword ) : void {

        if ( $this->seo_plugin == 'yoast' ) {
            SCwriter_Yoast::set_focus_keyword( $this->post_id, $primary_keyword );
        } elseif ( $this->seo_plugin == 'smartcrawl' ) {
            SCwriter_Smartcrawl::set_focus_keyword( $this->post_id, $primary_keyword );
        } else {
            update_post_meta( $this->post_id, 'primary_keyword', $primary_keyword );
        }

	}

}