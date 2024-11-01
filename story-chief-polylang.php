<?php
/**
 * Plugin Name: StoryChief Polylang
 * Plugin URI: https://storychief.io/wordpress-polylang
 * Description: This plugin lets StoryChief and Polylang work together.
 * Version: 1.0.5
 * Author: Gregory Claeyssens
 * Author URI: http://storychief.io
 * License: GPL2
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'STORYCHIEF_PPL_VERSION', '1.0.5' );
define( 'STORYCHIEF_PPL__MINIMUM_WP_VERSION', '4.6' );
define( 'STORYCHIEF_PPL__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STORYCHIEF_PPL__PLUGIN_BASE_NAME', plugin_basename(__FILE__) );

require_once( STORYCHIEF_PPL__PLUGIN_DIR . 'class.storychief-ppl.php' );

add_action( 'init', array( 'Storychief_PPL', 'init' ) );
