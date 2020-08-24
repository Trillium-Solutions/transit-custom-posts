<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Timetable extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('Timetable', array(
				'menu_icon' => 'dashicons-clock',
				'rewrite'	=> array( 'slug' => 'timetables' ),
				'supports'    => get_option('tcp_timetable_editor' ) ? array( 'title' ) : array('title', 'editor')
		));
		$this->add_meta_box('Timetable Fields', array(
			'Timetable ID'		=> array(),
			'Route ID'		 	=> array(), 
			'Route Label'		=> array(),
			'Direction ID'		=> array(),
			'Direction Label'	=> array(),
			'Days of Week'		=> array(),
			'Service Notes'		=> array(),
			'Start Date'		=> array(),
			'End Date'			=> array(),	
		), 'side');
	}
	
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function isActive() {
		return isset(self::$instance);
	}
}