<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Cat extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('cat', array(
				'menu_icon' => 'dashicons-location-alt',
				'rewrite'	=> array( 'slug' => 'cats' ),
				'show_in_nav_menus'	=> true,
				'show_in_menu'	=> true,
				'supports' => get_option('tcp_cat_editor' ) ? array( 'title' ) : array('title', 'editor')
		));
		$this->add_meta_box('cat Fields', array(
			'cat Custom Name'	=> array(
				'helper'	=> 'The custom name will override the cat display name in settings',
			),
			'cat ID' 			=> array(),
			'cat Short Name' 	=> array(),
			'cat Long Name'	=> array(),
			'cat Description'	=> array(),
			'cat Color'		=> array(),
			'cat Text Color'	=> array(),
			'cat Sort Order'	=> array(),
			'Agency ID'			=> array(),
			'cat Cooltest'	=> array(),	
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
		register_widget( 'tcp_cat_Widget' );
	}
}

class TCP_cat_Widget extends WP_Widget {
	
	function __construct() {
		parent::__construct(
			'tcp_cat_widget', 
			'cats', 
			array(
				'description' => 'A list of all cats', 
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
		tcp_list_cats();
		echo $args['after_widget'];
	}
}