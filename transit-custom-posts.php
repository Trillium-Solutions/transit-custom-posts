<?php
/**
* Plugin Name: Transit Custom Posts
* Plugin URI: https://trilliumtransit.github.io/transit-custom-posts/
* Description: Creates route, alert, timetable, and other custom post types used for transit sites. Programmatically updates data from a GTFS feed.
* Version: 2.0
* Author: NomeQ
* Author URI: https://trilliumtransit.com/
* License: GPL2
*/

// Define Constants 
define( 'TCP_CUSTOM_TYPES', get_option( 'tcp_custom_types', false ) );

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
if ( TCP_CUSTOM_TYPES ) {
	if ( in_array('tcp_use_routes', TCP_CUSTOM_TYPES ) ) {
		$routes = TCP_Route::getInstance();
	}
	if ( in_array('tcp_use_alerts', TCP_CUSTOM_TYPES ) ) {
		$alerts = TCP_Alert::getInstance();
	}
	if ( in_array('tcp_use_timetables', TCP_CUSTOM_TYPES ) ) {
		$timetables = TCP_Timetable::getInstance();
	}
	if ( in_array('tcp_use_board', TCP_CUSTOM_TYPES ) ) {
		$board_meetings = TCP_BoardMeeting::getInstance();
	}
}