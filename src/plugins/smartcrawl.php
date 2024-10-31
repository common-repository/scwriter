<?php

namespace SCwriter\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SCwriter_Smartcrawl extends SCwriter_PostMetaDataBase {
	
	public static function set_meta_description( int $post_id, string $description ) : void {
	
		update_post_meta( $post_id, '_wds_metadesc', $description );
	}
	
	public static function set_meta_title( int $post_id, string $title ) : void {
	
		update_post_meta( $post_id, '_wds_title', $title );
	}
	
	public static function set_focus_keyword( int $post_id, string $keyword ) : void {
	
		update_post_meta( $post_id, '_wds_focus-keywords', $keyword );
	}

}