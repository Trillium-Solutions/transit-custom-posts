<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Route extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('Route', array(
				'menu_icon' => 'dashicons-location-alt',
				'rewrite'	=> array( 'slug' => 'routes' ),
				'show_in_nav_menus'	=> true,
				'show_in_menu'	=> true,
				'supports' => get_option('tcp_route_editor' ) ? array( 'title' ) : array('title', 'editor')
		));
		$this->add_meta_box('Route Fields', array(
			'Route Custom Name'	=> array(
				'helper'	=> 'The custom name will override the route display name in settings',
			),
			'Route ID' 			=> array(),
			'Route Short Name' 	=> array(),
			'Route Long Name'	=> array(),
			'Route Description'	=> array(),
			'Route Color'		=> array(),
			'Route Text Color'	=> array(),
			'Route Sort Order'	=> array(),
			'Agency ID'			=> array(),	
		));
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
	
	public function register_widgets() {
		register_widget( 'tcp_Route_Widget' );
	}
}

class TCP_Route_Widget extends WP_Widget {
	
	function __construct() {
		parent::__construct(
			'tcp_route_widget', 
			'Routes', 
			array(
				'description' => 'A list of all routes', 
			) 
		);
	}
	
	// Back-end Display Form
	function form($instance) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}
	
	// Update instance
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		return $instance;
	}
	
	// Front-end Display
	// TODO: Add underscores translation support basically everywhere
	function widget($args, $instance) {
		$title = $instance['title'];
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		tcp_list_routes();
		echo $args['after_widget'];
	}
}