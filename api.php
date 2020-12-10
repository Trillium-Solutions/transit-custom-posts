<?php
/**
 * Public API functions
 */

 /**
 * Returns a sorted array of route objects.
 */
 function tcp_get_routes() {
 	if ( ! post_type_exists( 'route') ) {
 		// Fail silently
 		return;
 	}

 	// route sort
 	$order = get_option('tcp_route_sortorder');
 	$orderby = $order == 'route_sort_order' ? array( 'meta_value_num' => 'ASC', 'title' => 'ASC') : 'title';
 	$route_args = array(
 		'post_type'	  => 'route',
 		'numberposts' => -1,
 		'meta_key'	  => $order,
 		'orderby'	  => $orderby,
 	);
 	return get_posts( $route_args );
}

/**
* Outputs all route names with formatting.
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type string "before" Text or HTML displayed before route list.
*         Default: '<div class="tcp_route_list">'
*     @type string "after" Text or HTML displayed after route list.
*         Default: '</div>'
*     @type string "sep" Text or HTML displayed between items.
*         Default: ' '
*     @type bool "use_color" Add route color as background style.
*         Default: false
*     @type bool "show_alert" Display alert icon if route has active alert
*         Default: false
*     @type bool "show_circle" Deprecated. @see get_route_name()
*     @type string "route_name" Deprecated. @see get_route_name()
* }
*/
function tcp_list_routes( $args = array() ) {
	if ( ! post_type_exists('route') ) {
		// Fail silently
		return;
	}
	$defaults = array(
		'before'		=> '<div class="tcp_route_list">',
		'after'			=> '</div>',
		'sep'			=> ' ',
		'use_color'		=> false,
		'show_circle'	=> false,
		'route_name'	=> 'long_name',
		'show_alert'	=> false,
    	'alert_markup' => 'default',

	);
	$args = wp_parse_args( $args, $defaults );

	$route_posts = tcp_get_routes();
	$rcolor = '';
	$routes = array();

	// Format and output each route in the database
	foreach ( $route_posts as $route ) {

		// Use route_color and route_text_color to style route
		if ( $args['use_color'] ) {
			$text = '#' . get_post_meta( $route->ID, 'route_text_color', true);
			$background = '#' . get_post_meta( $route->ID, 'route_color', true);
			$rcolor = 'style="background:'. $background . '; color:' . $text . ';"';
		}
		$alert_icon = '';


		if ( $args['show_alert'] ) {
			// Get current date using timezone set in Wordpress
      		// Set default to PST due to majority of clients
			$timestamp = time();
      		$zone_string = 'America/Los_Angeles';
      		if ( get_option('timezone_string')) {
        		$zone_string = get_option('timezone_string');
      		}
			$dt = new DateTime("now", new DateTimeZone( $zone_string ));
			$dt->setTimestamp($timestamp);

      		if ( $q->have_posts() ) {
        		if ( $args['alert_markup'] === 'default' ) {
          			$alert_icon = file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/icon-alert.php' );
        		} else {
					$alert_icon = $args['alert_markup'];
        		}
	   		}
		}
		// Add formatted route link to an array
		$routes[] = '<a href="' . get_the_permalink($route->ID) . '" class="' . $route->post_name . '"' . $rcolor . '>' . get_route_name($route->ID) . $alert_icon . '</a>';
	}
	echo $args['before'] . join( $args['sep'], $routes ) . $args['after'];
}

/**
* Displays the route title with formatting from plugin options.
*
* To be used inside the loop of a route post, otherwise fails silently.
*
* @global WP_Post $post
*
*/
function the_route_title() {

	global $post;

	if ( ! post_type_exists( 'route' ) || $post->post_type != 'route' ) {
		// Fail silently
		return;
	}

	// Use TCP filter if applicable
	if ( has_filter( 'tcp_filter_route_title' ) ) {

		echo apply_filters( 'tcp_filter_route_title', $post->ID );

	} else {

		$title = get_route_name( $post->ID );

		$style = '';

		// Use route color as background if route circle not in use
		if ( strpos(get_option('tcp_route_display'), '%route_circle%') === false ) {
			$color = '#' . get_post_meta( $post->ID, 'route_color', true );
			$text = '#' . get_post_meta( $post->ID, 'route_text_color', true );
			$style = 'style="background:' . $color . '; color:' . $text . ';"';
		}

		$html = '<h1 class="page-title route-title" ' . $style .'>' . $title . '</h1>';

		echo $html;
	}
}

/**
* Outputs formatted route name.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
* @return string Formatted route name.
*/
function get_route_name( $post_id = NULL ) {

	if ( ! post_type_exists( 'route' ) ) {

		// Return empty string if routes not in use
		return '';
	}

	// Use the current global post if no id is provided
	if ( empty( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	$format = get_option('tcp_route_display');
	// Allow name to be overridden
	if ( get_post_meta( $post_id, 'route_custom_name', true ) != '' ) {
		 $format = get_post_meta( $post_id, 'route_custom_name', true );
	}

	// Replace magic tags with meta values
	$format = str_replace( '%short_name%', get_post_meta( $post_id, 'route_short_name', true), $format );
	$format = str_replace( '%long_name%', get_post_meta( $post_id, 'route_long_name', true ), $format );
	$format = str_replace( '%route_circle%', get_route_circle( $post_id ), $format );

	/**
	* Filters the formatted route name.
	*
	* @param string $format The formatted route name
	*/
	$format = apply_filters( 'tcp_route_name', $format );

	return $format;
}

/**
 * Not implemented.
 */
function the_route_meta() {
	return;
}

/**
* Generates HTML for a route circle.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
* @param string $size Size class to output. Default: "medium".
*
* @return string Formatted route circle HTML.
*/
function get_route_circle( $post_id = NULL, $size = "medium" ) {

	if ( ! post_type_exists( 'route' ) ) {
		// Fail silently if routes don't exist.
		return;
	}

	if ( empty( $post_id ) ) {
		// Setup global postdata.
		global $post;
		$post_id = $post->ID;
	}

	// Get route metadata
	$route_color = get_post_meta( $post_id, 'route_color', true );
	$text_color  = get_post_meta( $post_id, 'route_text_color', true );
	$text        = get_post_meta( $post_id, 'route_short_name', true );

	if ( empty( $text ) ) {
		$text = get_option( 'tcp_route_circle_custom_name', '' );
	}

	$html = sprintf('<span class="route-circle route-circle-%1$s" style="background-color: #%2$s; color: #fff">%4$s</span>', $size, $route_color, $text_color, $text);

	if ( has_filter('get_route_circle') ) {
		$html = apply_filters('get_route_circle', $post_id );
	}

	return $html;
}

/**
* Outputs route description from post meta.
*
* Shortcut for outputting the route description inside the loop.
*
* @global WP_Post $post
* @see get_post_meta()
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type string "before" Text or HTML displayed before route description.
*         Default: '<div class="tcp_route_description">'
*     @type string "after" Text or HTML displayed after route description.
*         Default: '</div>'
*/
function the_route_description( $args = array() ) {
	global $post;
	if ( !post_type_exists( 'route' ) ) {
		return;
	}

	$defaults = array(
		'before'		=> '<div class="tcp_route_description">',
		'after'			=> '</div>',
	);
	$args = wp_parse_args( $args, $defaults );

	$description = get_post_meta( $post->ID, 'route_description', true );

	echo $args['before'] . $description . $args['after'];
}

/**
* Outputs all current alerts with metadata and formatting.
*
* By default, creates a collapsible container and only outputs a single
* route's alerts when used within the loop on a route page.
*
* @global WP_Post $post
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type bool "collapse" Create collapsible div with full alert text.
*         Default: true
*     @type bool "single_route" Only show a single route's alerts.
*         Default: false
*     @type bool "show_affected" Show routes affected by this alert.
*         Default: true
*     @type string sep_affected" Separator to use if "show_affected" is true.
*         Default: ", "
*     @type int "number_posts" Number of alerts to show.
*         Default: -1
* }
*
* @return int Number of alerts or false
*/
function tcp_do_alerts( $args = array() ) {

	if ( ! post_type_exists( 'alert' ) ) {
		return false;
	}

	$defaults = array(
		'collapse'		      => false,
		'use_button'          => false,
		'single_route'	      => false,
		'show_affected'	      => true,
		'sep_affected'	      => ', ',
		'number_posts'	      => -1,
		'excerpt_only'        => false,
		'feed-id'		      => '',
		'affected-routes'     => true,
		'route-circles'       => true,
		'link_text'           => 'Permalink',
		'affected_text'	      => 'Affected Routes: ',
		'alerts-title'        => 'Current Alerts',
		'alerts-id'           => 'tcp-alerts',
	);

	global $post;

	if ( $post->post_type == 'route' ) {
		$defaults['single_route']  = true;
		$defaults['show_affected'] = false;
	}

	// Overwrite defaults with supplied $args
	$args = wp_parse_args( $args, $defaults );
	// TRANSIT ALERTS FORMATTING
	if ( get_option( 'tcp_alerts_transit_alerts' ) ) {

		// Use transit alerts options instead of querying for alerts
		if ( function_exists('transit_alerts_get_alerts') && defined('WPTA_FEEDS') ) {

			if ( array_key_exists( 'route-id', $args ) ) {
				$alerts = transit_alerts_get_alerts( $args );
			}
			else if ( $defaults['single_route'] ) {
				$args['route-id'] = $post->post_name;
				$alerts = transit_alerts_get_alerts( $args );
			} else {
				$alerts = transit_alerts_get_alerts( $args );
			}

			if ( ! empty( $alerts ) ) {

				$alerts_title = array_key_exists( 'alerts-title', $args ) ? $args['alerts-title'] : $defaults['alerts-title'];

				$alerts_id  = array_key_exists( 'alerts-id', $args ) ? $args['alerts-id'] : $defaults['alerts-id'];

				include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-header.php' );

				$alert_count = 1;

				// Set up alert custom classes if applicable
				$alert_container_class = '';
				$alert_title_class = '';
				$alert_desc_class = '';
				$alert_dates_class = '';

				if ( array_key_exists( 'custom-classes', $args ) ) {
					if (  array_key_exists('alert-container', $args['custom-classes'] ) ) {
						$alert_container_class = $args['custom-classes']['alert-container'];
					}
				}
				if ( array_key_exists( 'custom-classes', $args ) ) {
					if (  array_key_exists('alert-container', $args['custom-classes'] ) ) {
						$alert_container_class = $args['custom-classes']['alert-container'];
					}
					if (  array_key_exists('alert-title', $args['custom-classes'] ) ) {
						$alert_title_class = 'class="' . esc_attr( $args['custom-classes']['alert-title'] ) . '"';
					}
					if (  array_key_exists('alert-desc', $args['custom-classes'] ) ) {
						$alert_desc_class = $args['custom-classes']['alert-desc'];
					}
					if (  array_key_exists('alert-dates', $args['custom-classes'] ) ) {
						$alert_dates_class = $args['custom-classes']['alert-dates'];
					}
				}

				foreach( $alerts as $alert ) {

					$panel_class   = 'panel-' . $alert_count;
					$alert_url     = $alert['url'];
					$alert_title   = $alert['title'];
					$alert_desc    = $alert['description'];
					$alert_dates   = $alert['dates'];
					$collapsible   = $defaults['collapse'];
					$link_text     = $default['link_text'];
					$affected_text = $defaults['affected_text'];


					// Check for and set button
					$alert_button = array_key_exists( 'use_button', $args ) ? $args ['use_button'] : '';

					// Check for and set collapsible class
					if ( array_key_exists( 'collapse', $args ) ) {
						$collapsible = $args['collapse'] === 'true' ? 'collapse' : '' . 'panel-' . $alert_count;
					}

					// Check for and set alert title
					if ( array_key_exists( 'link_text', $args ) ) {
						$link_text  = $args['link_text'] === 'title' ? $alert_title : $args['link_text'];
					}

					// Check for and set affected text
					if ( array_key_exists( 'affected_text', $args ) ) {
						if ( $alert['affected-routes'] ) {
							$affected_text = $affected_text . ' ' . implode( ',', $alert['affected-routes'] );
						} else {
							$affected_text = '';
						}
					}

					// Set route circles.
					if ( ! empty( $alert['route-circles'] ) ) {
						$alert_title =  '<div class="route-circle-list">' . $alert['route-circles'] . '</div>' . $alert_title;
					}

					include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-panel.php' );
					$alert_count++;
				}

				include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-footer.php' );
			}

		}
	} else {
		// CPT ALERTS FORMATTING
		// Get alerts where the end date is either not set or is in the future.
		$query_args = array(
			'post_type'		  => 'alert',
			'posts_per_page'  => $args['number_posts'],
			'meta_query' => array(
				'relation' => 'OR',
					array(
						'key'	  => 'end_date',
						'compare' => 'NOT EXISTS',
        				'value'   => '',
					),
					array(
						'key'	  => 'end_date',
						'value'   => current_time('Y-m-d'),
						'compare' => '>=',
						'type'	  => 'DATE',
				),
			),
		);

		// Overwrite meta query for single route alerts
		if ( $args['single_route'] ) {
			$query_args['meta_query'] = array(
				'relation'	=> 'AND',
				array(
					'relation' => 'OR',
						array(
						'key'	  => 'end_date',
						'compare' => 'NOT EXISTS',
          				'value'   => '',
					),
					array(
						'key'	  => 'end_date',
						'value'   => current_time('Y-m-d'),
						'compare' => '>=',
						'type'	  => 'DATE',
					),
				),
				array(
					'relation' => 'OR',
					array(
						'key'	  => 'affected_routes',
						'value'   => 'all_routes',
						'compare' => 'LIKE',
					),
					array(
						'key'	  => 'affected_routes',
						'value'	  => $post->post_name,
						'compare' => 'LIKE',
					),
				),
			);
		}
		$alert_query = new WP_Query( $query_args );

		if ( $alert_query->have_posts() ) {

			// Begin alert header
			include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-header.php' );

			while ( $alert_query->have_posts() ) {

				$alert_query->the_post();

				$panel_class         = 'panel-' . get_the_ID();
				$alert_url           = get_permalink();
				$alert_title         = get_the_title();
				$alert_desc          = get_the_content();
				$alert_dates         = $date_text;
				$collapsible         = $defaults['collapse'];
				$affected_text       = $defaults['affected_text'];
				$link_text           = $alert_title;

				// Check for and set button
				$alert_button = array_key_exists( 'use_button', $args ) ? $args ['use_button'] : '';

				// Check for and set excerpt option
				if ( array_key_exists( 'excerpt_only', $args ) ) {
					$alert_desc = $args['excerpt_only'] ? get_the_excerpt() : get_the_content();
				}

				// Check for and set collapsible class
				if ( array_key_exists( 'collapse', $args ) ) {
					$collapsible = $args['collapse'] ? 'collapse' : '' . 'panel-' . get_the_ID();
				}

				// Check for and set alert title
				if ( array_key_exists( 'link_text', $args ) ) {
					$link_text  = $args['link_text'] === 'title' ? $alert_title : $args['link_text'];
				}


				// Check for and set affected text
				if ( $args['show_affected'] ) {
					$affected_routes = tcp_get_affected( get_the_ID(), $args['sep_affected'] );
					$affected_text   = $args['affected_text'] . ' ' . $affected_routes;
				} else {
					$affected_text = '';
				}

				// Retrieve formatted date text for effective date(s)
				$alert_dates     = tcp_get_alert_dates( get_the_ID() );

				// Add route circles to alert title if applicable
				if (  $args['route-circles'] ) {
					$route_circles = '<div class="route-circles">';
					$the_affected  = get_post_meta( get_the_ID(), 'affected_routes', true );
					foreach ( $the_affected as $key => $value ) {
						$affected_route_post = get_posts( array(
							'post_type' => 'route',
							'name' => $key )
						);
						$route_circles .= get_route_circle( $affected_route_post[0]->ID );
					}
					$route_circles .= '</div>';
					$alert_title    = $route_circles . $alert_title;
				}

				// Add alert panel
				include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-panel.php' );
			}

			// Add alert footer
			include( plugin_dir_path( __FILE__ ) . 'inc/templates/alerts/alert-footer.php' );

			wp_reset_postdata();

			return $alert_query->post_count;
		}
		return false;
	}
}

/**
* Creates text for alert effective date range.
*
* @global WP_Post $post
*
* @param int $post_id Optionally specify post id for route outside loop.
*
* @return string Formatted date range text.
*/
function tcp_get_alert_dates( $post_id = null ) {

	if ( empty( $post_id ) ) {
		// Setup postdata
		global $post;
		$post_id = $post->ID;
	}

	// Get the effective date and format using global site settings
	$effective_date = mysql2date( get_option('date_format'), get_post_meta($post_id, 'effective_date', true) );

	// Get the end date and also format using global site settings
	$end_date = mysql2date( get_option('date_format'), get_post_meta($post_id, 'end_date', true) );

	// Logic for printing the date if start, end, or both are present
	$date_text = '';
	if ( ! empty( $effective_date ) ) {
		$date_text = 'Starting ' . $effective_date;
		if ( ! empty($end_date) ) {
			$date_text = 'Effective ' . $effective_date . ' - ' . $end_date;
		}
	} elseif ( !empty($end_date) ) {
		$date_text = 'Effective until: ' . $end_date;
	}
	return $date_text;
}

/**
* Outputs all timetables for a route from inside the loop.
*
* @global WP_Post $post
*
* @param array $args Not implemented.
*/
function the_timetables( $args = array() ) {
	$timetables    = get_timetables();
	$days          = array();
	$directions    = array();
	$timestables   = array();
	$na_day_button = false;
	$na_dir_button = false;

	if ( $timetables->have_posts() ) {

		while ( $timetables->have_posts() ) {

			$timetables->the_post();

			// Get timetable metadata
			$table_dir  = get_post_meta( get_the_ID(), 'direction_label', true );
			$table_days	= get_post_meta( get_the_ID(), 'days_of_week', true );

			// Check for days with no direction or directions with no days
			if ( ! $na_dir_button && ! empty ( $table_days ) && empty( $table_dir ) ) {
				$na_dir_button = true;
			}
			if ( ! $na_day_button && ! empty ( $table_dir ) && empty( $table_days ) ) {
				$na_day_button = true;
			}

			// Create a timetable div with data attributes for optional JS manipulation
			if ( array_key_exists( 'legend', $args ) && $args['legend'] ) {

				// Enqueue required jQuery and custom timetable scripts and css
				add_action( 'wp_footer', function() {
					if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
						wp_enqueue_script( 'jquery' );
					}
					wp_enqueue_script('tcp-timetable-scripts', plugins_url('/inc/js/timetables.js', __FILE__), array('jquery'),'1.0', true );
					wp_enqueue_style('tcp-timetable-styles', plugins_url('/inc/css/timetables.css', __FILE__), '', rand() );
				});

				// Pushing items into directions and days to array
				// to use in legend nav buttons.
				$days[]        = $table_days;
				$directions[]  = $table_dir;
				$table_content = get_the_content();

				$timestables[] = array(
					'day'       => $table_days,
					'direction' => $table_dir,
					'table'     => $table_content
				);

			} else {
				// Print tables without timetable legend.
				printf('<div class="timetable-holder" data-dir="%s" data-days="%s">', $table_dir, $table_days);
					// Should be HTML or an image
					the_content();
				echo '</div>';
			}
		}

		if ( array_key_exists( 'legend', $args ) && $args['legend'] ) {
			$days = array_unique( $days );  // Remove duplicates
			$days = ! in_array( 'Weekday', $days ) ? array_reverse( $days ) : $days;
			$days = array_filter( $days ); // Removing empty strings
			if ( $na_day_button ) {
				array_push( $days, 'no-day');
			}
			$directions = array_unique( $directions );
			$directions = array_reverse( $directions );
			$directions = array_filter( $directions );
			if ( $na_dir_button ) {
				array_push( $directions, 'no-direction');
			}
			include( plugin_dir_path( __FILE__ ) . '/inc/templates/timetables/timetables-legend.php' );
		}
		wp_reset_postdata();
	}
}

/**
* Returns a WP Query post object
*
* @global WP_Post $post
*
* @param array $args {
*     Optional. An array of arguments.
*
*     @type bool "upcoming" Return upcoming (not yet active) timetables
*         Default: false
*     @type bool "use_expired" Return expired timetables if none are current
*         Default: WP_Option $tcp_timetable_expire
*     @type string "upcoming_time" Time interval to fetch upcoming timetables
*         Default: 'P14D'
* }
* @return WP_Query timetable query object
*/
function get_timetables( $args = array() ) {
	global $post;

	if ( !post_type_exists( 'timetable' ) || $post->post_type != 'route') {
		// Fail silently.
		return;
	}

	$use_expired = get_option('tcp_timetable_expire') === 'never';

	$defaults = array(
		'upcoming'			=> false,
		'use_expired'		=> $use_expired,
		'upcoming_time'		=> 'P14D',
	);
	// Overwrite defaults with supplied $args
	$args = wp_parse_args( $args, $defaults );

	$route_id = get_post_meta($post->ID, 'route_id', true);
	$date = new DateTime();
	$today = intval($date->format('Ymd'));

	// Set a date in the future using upcoming_time $args
	$date->add(new DateInterval($args['upcoming_time']));
	$soon = intval($date->format('Ymd'));

	$start = array(
		'key' 		=> 'start_date',
		'type' 		=> 'NUMERIC',
		'compare'	=> '<=',
		'value'		=> $today,
	);

	$end = array(
		'key'		=> 'end_date',
		'type'		=> 'NUMERIC',
		'compare'	=> '>=',
		'value'		=> $today,
	);

	// Hacky looking little way of resetting args to get
	// timetables that are upcoming in an *upcoming_time* interval
	if ($args['upcoming']) {
		$start['compare'] = '>';
		$end['value'] = $soon;
		$end['key'] = 'start_date';
		$end['compare'] = '<=';
	}

	$timetable_args = array(
		'post_type'			=> 'timetable',
		'posts_per_page'	=> -1,
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'timetable_id',
		'order'				=> 'ASC',
		'meta_query'		=> array(
			'relation'	=> 'AND',
			array(
				'key' => 'route_id',
				'value' => $route_id,
			),
			array(
				'relation' => 'AND',
				$start,
				$end,
			),
		),
	);

	$timetables = new WP_Query( $timetable_args );
	if ( $timetables->have_posts() || !$use_expired ) {
		return $timetables;
	}

	// If here, there were no current timetables and
	// we are willing to use expired
	$expired_timetable_args = array(
		'post_type'			=> 'timetable',
		'posts_per_page'	=> -1,
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'timetable_id',
		'order'				=> 'ASC',
		'meta_query'		=> array(
			'relation'	=> 'AND',
			array(
				'key' => 'route_id',
				'value' => $route_id,
			),
			array(
				'relation' => 'AND',
				array(
					'key' => 'start_date',
					'value' => $today,
					'compare'=> '<=',
					'type' => 'NUMERIC',
				),
			),
		),
	);

	$expired_timetables = new WP_Query( $expired_timetable_args );
	return $expired_timetables;
}

/**
* Retrives and formats affected route postmeta from an alert.
*
* @global WP_Post $post
*
* @param int $post_id Optional post id if used outside the loop
* @param string $sep Separator to use between route names. Default: ", "
*
* @return string Formatted route names.
*/
function tcp_get_affected( $post_id = null, $sep = ', ') {

	if ( ! post_type_exists( 'route' ) || ! post_type_exists( 'alert' ) ) {
		// Fail silently.
		return;
	}

	if ( empty( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}

	$the_affected = get_post_meta( $post_id, 'affected_routes', true );

	if ( $the_affected == '' ) {
		return;
	}

	if ( get_option( 'tcp_alert_custom_display_affected' ) ) {
		/**
		* Filters the display text for an alert's affected routes.
		*
		* @param array $the_affected Array of route slug strings.
		*/
		$the_affected = apply_filters( 'tcp_display_affected', $the_affected );

	} else {
		$the_affected = array_map( 'tcp_route_name_from_tag', $the_affected );
	}

	return join($the_affected, $sep);
}

/**
* Gets the formatted route name using the route post slug.
*
* @param string $route_tag Route post slug.
*
* @return string Formatted route name.
*/
function tcp_route_name_from_tag( $route_tag ) {

	//get the id from the tag (slug)
	$r_post = get_page_by_path( $route_tag, OBJECT, 'route' );

	if ( empty( $r_post ) ) {

		if ( $route_tag == 'all_routes' ) {
     		return 'All Routes';
    	}
		// Page doesn't exist or filter was applied
		return $route_tag;
	}
	return get_route_name( $r_post->ID );
}
