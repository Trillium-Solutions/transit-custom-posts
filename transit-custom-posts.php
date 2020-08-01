<?php
/**
* Plugin Name: Transit Custom Posts
* Plugin URI: https://trilliumtransit.github.io/transit-custom-posts/
* Description: Creates route, alert, timetable, and other custom post types used for transit sites. Programmatically updates data from a GTFS feed.
* Version: 0.9.3
* Author: NomeQ
* Author URI: https://trilliumtransit.com/
* License: GPL2
*/

// Define plugin constants 


// Admin settings page
require_once('settings-page.php');

// API functions
require_once('api.php');

// Custom post type class files
require_once( plugin_dir_path( __FILE__ ) . 'cpts/alert.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/route.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/timetable.php');
require_once( plugin_dir_path( __FILE__ ) . 'cpts/board-meeting.php');

// Settings page custom post type options
if ( get_option('tcp_custom_types') ) {
	$custom_types = get_option('tcp_custom_types');

	if ( in_array('tcp_use_routes', $custom_types) ) {
		$routes = TCP_Route::getInstance();
	}
	if ( in_array('tcp_use_alerts', $custom_types) ) {
		$alerts = TCP_Alert::getInstance();
	}
	if ( in_array('tcp_use_timetables', $custom_types) ) {
		$timetables = TCP_Timetable::getInstance();
	}
	if ( in_array('tcp_use_board', $custom_types) ) {
		$board_meetings = TCP_BoardMeeting::getInstance();
	}
}
