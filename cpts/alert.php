<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Alert extends TCP_CustomPostType {
	private static $instance;

	protected function __construct() {
		parent::__construct('Alert', array(
				'menu_icon'   => 'dashicons-warning',
				'rewrite'	  => array( 'slug' => 'alerts' ),
				'has_archive' => true,
				'supports'    => get_option('tcp_alerts_editor' ) ? array( 'title' ) : array('title', 'editor')
		));
		$this->add_meta_box('Affected Routes', array(
			'Affected Routes' => array(
				'type'   => 'text',
				'helper' => 'Enable <em>route</em> custom posts to attach affected routes',
			),
		), 'side');
		$this->add_meta_box('Alert Dates', array(
			'Effective Date' => array(
				'type' => 'date',
			),
			'End Date'	=> array(
				'type' => 'date',
			),
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

	public function custom_metabox( $post, $data ) {
		// If 'route' custom posts not active, create default metaboxes
		$custom_types = TCP_CUSTOM_TYPES;
		if ( ! in_array('tcp_use_routes', $custom_types) || $data['id'] != 'affected_routes' ) {
			parent::custom_metabox( $post, $data );
		} else {
			// Otherwise, populate with route names
			$custom_fields = $data['args'][0];
			$options = array();

			$sort_order = get_option('tcp_route_sortorder');
			
			if ($sort_order == 'route_short_name'):
				$orderby = 'meta_value_num';
			elseif ($sort_order == 'route_long_name'):
				$orderby = 'meta_value';
			elseif ($sort_order == 'route_sort_order'):
				$orderby = array( 'meta_value_num' => 'ASC', 'title' => 'ASC');
			else:
				$orderby = 'title';
			endif;
			
			$routes = get_posts(array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> 'route',
				'meta_key'			=> $sort_order,
				'orderby'			=> $orderby,
				'order'				=> 'ASC'
			));
			wp_reset_postdata();
			if ( !$routes ) {
				echo '<p>Add routes manually or with GTFS update in order to attach affected routes</p><small>If routes are active but not showing up here, check the user guide for solutions to common problems</small>';
				return;
			}
			foreach ($routes as $route) {
				$options[$route->post_name] = get_route_name($route->ID);
			}
			$options['all_routes'] = 'All Routes';
			$custom_fields['Affected Routes'] = array(
				'type' 	      => 'multiple_checkbox',
				'options'     => $options,
        		'placeholder' => '',
        		'classes'     => '',
        		'helper'      => '',
				'default'	  => array(),
			);
			$data['args'][0] = $custom_fields;
			parent::custom_metabox( $post, $data );
		}
	}

	public function register_widgets() {
		register_widget( 'tcp_Alert_Widget' );
	}
}

class TCP_Alert_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'tcp_alert_widget',
			'Alerts',
			array(
				'description' => 'A list of the most recent alerts.',
			)
		);
	}

	// Back-end Display Form
	function form($instance) {
		$show_num = ! empty( $instance['show_num'] ) ? $instance['show_num'] : '5';
		$show_affected = ! empty( $instance['show_affected'] ) ? 'checked' : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_num' ); ?>">Number of alerts to show:</label>
			<input type="number" id="<?php echo $this->get_field_id( 'show_num' ); ?>" name="<?php echo $this->get_field_id( 'show_num' ); ?>" value="<?php echo esc_attr( $show_num ); ?>" class="tiny-text" size="3">
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_affected' ); ?>" name="<?php echo $this->get_field_id( 'show_affected' ); ?>" <?php echo $show_affected ?> class="checkbox">
			<label for="<?php echo $this->get_field_id( 'show_affected' ); ?>">Show affected routes?</label>
		</p>
		<?php
	}

	// Update instance
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['show_num'] = intval( $new_instance['show_num'] );
		$instance['show_affected'] = $new_instance['show_affected'];
		return $instance;
	}

	// Front-end Display
	// TODO: Add underscores translation support basically everywhere
	function widget($args, $instance) {
		$title = apply_filters( 'widget_title', 'Alerts');
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		//TODO: add in alert icon
		$this->the_alerts_list($instance);
		// Link to all alerts page/archive?
		echo $args['after_widget'];
	}

	// Show all alerts regardless of whether they are in effect yet.
	//TODO: Add sortby effective date
	function the_alerts_list($instance) {
		$args = array(
			'post_type' => 'alert',
			'posts_per_page' => $instance['show_num'],
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'		=> 'end_date',
					'value'		=> '',
					'compare'	=> '==',
				),
				array(
					'key'		=> 'end_date',
					'value' 	=> date("Y-m-d"),
					'compare'	=> '>=',
					'type'		=> 'DATE',
				),
			),
		);
		$my_query = new WP_Query($args);
		if ( $my_query->have_posts() ) :
			echo '<ul class="tcp-alert-list">';
			while ( $my_query->have_posts() ) : $my_query->the_post();
				printf('<li><a href="%s">%s</a> <span class="effective-date small">Effective: %s</span></li>',
				get_the_permalink(), get_the_title(), get_post_meta(get_the_ID(), 'effective_date', true) );
			endwhile;
			echo '</ul>';
			printf('<a href="%s">See all alerts &raquo;</a>', get_post_type_archive_link( 'alert') );
		else:
			echo '<p>No alerts at this time. Enjoy the ride!</p>';
		endif;
		wp_reset_postdata();
	}
}
