<?php
/*
  Plugin Name: Podcastor
  Description: Posdcast player. Podcasting companion.
  Version: 1
  Author: Fabricelifaa
  Author URI: https://fab2dev.com/
  Copyright: Fabricelifaa
  License: GPLv2 or later
  Text Domain: f_podcator
  Domain Path: /lang
 */


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


define( 'PODCASTOR_VERSION', '4.1.6' );
define( 'PODCASTOR__MINIMUM_WP_VERSION', '4.0' );
define( 'PODCASTOR__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PODCASTOR__PLUGIN_URI', plugins_url("", __FILE__) );


require_once( PODCASTOR__PLUGIN_DIR . 'class.podcast-error.php' );
require_once( PODCASTOR__PLUGIN_DIR . 'class.podcast.php' );

add_action( 'init', array( 'Podcaster', 'init' ) );