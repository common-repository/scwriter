<?php
/**
* Plugin Name: SCwriter – AI SEO Content Generator & Blog Writer
* Plugin URI: https://seocontentwriter.actlys.com/
* Description: GPT-4o-powered content writer for SEOs, business owners, and content creators. Easily generate SEO-optimized posts, manage AI costs, and create engaging, high-quality content.
* Version: 0.0.9.1
* Requires at least: 6.0
* Tested up to: 6.6.2
* Requires PHP: 7.4
* Author: actlys.com
* Author URI: https://actlys.com/
* License: GPLv2 or later
* Text Domain:  scwriter
* Domain Path:  /languages/
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Tags: AI, blog, automation, ChatGPT, content generator
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define('SCWRITER_NAME', 'SCwriter');
define('SCWRITER_PREFIX', 'scwriter');
define('SCWRITER_VERSION', '0.0.9');
define('SCWRITER_FILE', __FILE__);
define('SCWRITER_FILE_NAME', plugin_basename(__FILE__));
define('SCWRITER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCWRITER_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('SCWRITER_API_ENDPOINT', 'https://seoblogwriterserver.sparkignitepro.com/wp-json/v1/');
define('SCWRITER_WEBSITE', 'https://seocontentwriter.actlys.com');
define('SCWRITER_CONNECTION_API_KEY', '8f9910d3e0666dfde28ec85e1b7f9ed1');

require_once( __DIR__ . '/src/enums/frequency.php' );
require_once( __DIR__ . '/src/enums/status.php' );
require_once( __DIR__ . '/src/cron.php' );
require_once( __DIR__ . '/src/dashboard.php' );
require_once( __DIR__ . '/src/cpt.php' );
require_once( __DIR__ . '/src/wp.php' );
require_once( __DIR__ . '/src/api/api_base.php' );
require_once( __DIR__ . '/src/api/posts.php' );
require_once( __DIR__ . '/src/api/settings.php' );
require_once( __DIR__ . '/src/api/presets.php' );
require_once( __DIR__ . '/src/plugins/post_meta_data_updater.php' );
require_once( __DIR__ . '/src/plugins/post_meta_data_base.php' );
require_once( __DIR__ . '/src/plugins/smartcrawl.php' );
require_once( __DIR__ . '/src/plugins/yoast.php' );

$scwriter = new SCwriter\SCwriter_WP();

register_activation_hook(SCWRITER_FILE, array('SCwriter\SCwriter_WP', 'on_plugin_activate'));