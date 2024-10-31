<?php

namespace SCwriter\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class SCwriter_PostMetaDataBase {
	
	public static function set_meta_description( int $post_id, string $description ) : void {
	}
	
	public static function set_meta_title( int $post_id, string $title ) : void {
	}
	
	public static function set_focus_keyword( int $post_id, string $keyword ) : void {
	}

}