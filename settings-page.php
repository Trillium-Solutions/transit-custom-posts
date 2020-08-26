<?php
/*
 * Creates the plugin settings page using Wordpress settings API
 */
require_once('gtfs-update.php');

function tcp_settings_pages() {
    // Root menu for Plugin
    $page_title = 'Transit Custom Posts';
    $menu_title = 'Transit Custom Posts';
    $capability = 'manage_options';
    $menu_slug  = 'tcp_settings_page';
    $callback   = 'tcp_custom_post_settings_content';
    $icon       = 'dashicons-location';
    $position   = 85;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon, $position );

    $parent_slug = 'tcp_settings_page';
    $page_title  = 'Custom Post Types';
    $menu_title  = 'Custom Post Types';
    $capability  = 'manage_options';
    $menu_slug   = 'tcp_settings_page';
    $callback    = 'tcp_custom_post_settings_content';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );

    $parent_slug = 'tcp_settings_page';
    $page_title  = 'GTFS Settings';
    $menu_title  = 'GTFS Settings';
    $capability  = 'manage_options';
    $menu_slug   = 'tcp_gtfs_settings';
    $callback    = 'tcp_gtfs_settings_content';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}
add_action( 'admin_menu', 'tcp_settings_pages');

function tcp_setup_settings_sections() {
	add_settings_section( 'cpt_fields', '', '', 'tcp_cpt_fields' );
    add_settings_section( 'gtfs_fields', '', '', 'tcp_gtfs_fields' );
    add_settings_section( 'gtfs_file', '', '', 'tcp_gtfs_files' );
}
add_action( 'admin_init', 'tcp_setup_settings_sections' );

function tcp_setup_fields() {
	$fields = array(
		array(
			'uid'		=> 'tcp_custom_types',
			'label'		=> 'Custom Post Types',
			'section'	=> 'cpt_fields',
			'type'		=> 'multiple_checkbox',
			'options'	=> array(
				'tcp_use_routes'	 => 'Routes',
				'tcp_use_alerts'	 => 'Alerts',
				'tcp_use_timetables' => 'Timetables',
				'tcp_use_board'		 => 'Board Meetings',
			),
			'placeholder'	=> '',
			'helper'		=> '',
			'supplemental'	=> 'See <a href="https://trilliumtransit.github.io/transit-custom-posts/">plugin website</a> for more information',
			'default'		=> array(),
			'settings'		=> 'tcp_cpt_fields',
			'classes'		=> '',
		),
		array(
			'uid' 		=> 'tcp_route_display',
			'label' 	=> 'Route Display',
			'section'	=> 'tcp_routes_options',
			'type'		=> 'text',
			'options'	=> false,
			'placeholder'  => '',
			'helper'	   => '',
			'supplemental' => 'How to display route names on this site. Available keywords include %short_name%, %long_name%, and %route_circle%. This format can be overwritten by individual routes within their edit screens.',
			'default'  => '%short_name%: %long_name%',
			'settings' => 'tcp_cpt_fields',
			'classes'  => 'regular-text',
		),
		array(
			'uid' 		=> 'tcp_route_circle_custom_name',
			'label' 	=> 'Route Circle Custom Route Name',
			'section'	=> 'tcp_routes_options',
			'type'		=> 'text',
			'options'	=> false,
			'placeholder'  => '',
			'helper'	   => '',
			'supplemental' => 'Custom route name to display when a route short name is not available for a route circle.',
			'settings' => 'tcp_cpt_fields',
			'classes'  => 'regular-text',
	),
		array(
			'uid' 		=> 'tcp_route_sortorder',
			'label' 	=> 'Sort Order',
			'section'	=> 'tcp_routes_options',
			'type'		=> 'select',
			'options'	=> array(
				'route_sort_order' => 'Route Sort Order',
				'route_short_name' => 'Short Name',
				'route_long_name'  => 'Long Name',
			),
			'placeholder'  => '',
			'helper'	   => '',
			'supplemental' => '',
			'default'      => 'sort_order',
			'settings'     => 'tcp_cpt_fields',
			'classes'      => '',
		),
		array(
			'uid' 		   => 'tcp_route_editor',
			'label' 	   => 'Disable Route Post Editor',
			'section'	   => 'tcp_routes_options',
			'type'		   => 'checkbox',
			'options' 	   => false,
			'placeholder'  => '',
			'helper'	   => '<span style="color:#666666;"><em>Check to disable the WordPress editor for all route posts.</em></span>',
			'supplemental' => '',
			'settings'     => 'tcp_cpt_fields',
		),
		array(
			'uid' 		=> 'tcp_timetable_expire',
			'label' 	=> 'Timetable Expiration',
			'section'	=> 'tcp_timetable_options',
			'type'		=> 'select',
			'options'	=> array(
				'immediate' => 'Remove immediately on expiration date',
				'never'     => 'Show expired timetables if current unavailable',
			),
			'placeholder'  => '',
			'helper'	   => '',
			'supplemental' => 'For schedules that rarely change, it may be best to ignore the timetable end date.',
			'default'      => 'never',
			'settings'     => 'tcp_cpt_fields',
			'classes'      => '',
		),
		array(
			'uid' 		   => 'tcp_timetable_editor',
			'label'  	   => 'Disable Timetable Post Editor',
			'section'	   => 'tcp_timetable_options',
			'type'		   => 'checkbox',
			'options'	   => false,
			'placeholder'  => '',
			'helper'	   => '<span style="color:#666666;"><em>Check to disable the WordPress editor for all timetable posts.</em></span>',
			'supplemental' => '',
			'settings'     => 'tcp_cpt_fields',
		),
		array(
			'uid'		=> 'tcp_board_fields',
			'label'		=> 'Board Meeting Fields',
			'section'	=> 'tcp_board_options',
			'type'		=> 'multiple_checkbox',
			'options'	=> array(
				'tcp_minutes_field'		=> 'Minutes PDF',
				'tcp_agenda_field'		=> 'Agenda PDF',
				'tcp_location_field'	=> 'Location Field',
			),
			'placeholder'	=> '',
			'helper'		=> '',
			'supplemental'	=> '',
			'default'		=> array(),
			'settings'		=> 'tcp_cpt_fields',
			'classes'		=> '',
		),
		array(
			'uid' 		   => 'tcp_board_posts_per_page',
			'label' 	   => 'Board meetings page shows at most',
			'section'	   => 'tcp_board_options',
			'type'		   => 'number',
			'options'	   => false,
			'placeholder'  => '',
			'helper'	   => 'meetings per page',
			'supplemental' => '',
			'default'      => 20,
			'settings'     => 'tcp_cpt_fields',
			'classes'      => 'small-text',
		),
		array(
			'uid'			=> 'tcp_gtfs_url',
			'label'			=> 'GTFS Feed Url',
			'section'		=> 'gtfs_fields',
			'type'			=> 'text',
			'options'		=> false,
			'placeholder'	=> '',
			'helper'		=> '',
			'supplemental'	=> 'Should point to a ZIP of your GTFS feed',
			'default'		=> '',
			'settings'		=> 'tcp_gtfs_fields',
			'classes'		=> 'regular-text',
		),
        array(
            'uid'			=> 'tcp_gtfs_file',
            'label'			=> 'GTFS Feed File',
            'section'		=> 'gtfs_file',
            'type'			=> 'file',
            'options'		=> false,
            'placeholder'	=> '',
            'helper'		=> '',
            'supplemental'	=> 'Should point to a ZIP of your GTFS feed',
            'default'		=> '',
            'settings'		=> 'tcp_gtfs_files',
            'classes'		=> 'regular-text',
		),
	);

	// Adding display routes 
	if ( is_array( TCP_CUSTOM_TYPES ) && in_array( 'tcp_use_alerts', TCP_CUSTOM_TYPES ) ) {
		$alerts_affected_routes_field = array(
			'uid' 		   => 'tcp_alert_custom_display_affected',
			'label' 	   => 'Advanced: Custom display affected routes',
			'section'	   => 'tcp_alerts_options',
			'type'		   => 'checkbox',
			'options'	   => false,
			'placeholder'  => '',
			'helper'	   => '<span style="color:#666666;"><em>Check only if you hook into the "tcp_display_affected" filter in your theme.</em></span>',
			'supplemental' => '',
			'default'      => false,
			'settings'     => 'tcp_cpt_fields',
			'classes'      => '',
		);
		$fields[] = $alerts_affected_routes_field;

		$alerts_display_editor_field = array(
			'uid' 		   => 'tcp_alerts_editor',
			'label' 	   => 'Disable Alert Post Editor',
			'section'	   => 'tcp_alerts_options',
			'type'		   => 'checkbox',
			'options'	   => false,
			'placeholder'  => '',
			'helper'	   => '<span style="color:#666666;"><em>Check to disable the WordPress editor for all alert posts.</em></span>',
			'supplemental' => '',
			'settings'     => 'tcp_cpt_fields',
		);
		$fields[] = $alerts_display_editor_field;
	}

	// Adding transit alerts field if transit alerts are active
	if ( is_plugin_active( 'wp-transit-alerts/wp-transit-alerts.php' ) ) {
		$transit_alerts_field = array(
			'uid' 		   => 'tcp_alerts_transit_alerts',
			'label' 	   => 'Use WP Transit Alerts',
			'section'	   => 'tcp_alerts_options',
			'type'		   => 'checkbox',
			'options'	   => false,
			'placeholder'  => '',
			'helper'	   => '<span style="color:#666666;"><em>Overrides Transit Custom Posts Alerts and must have WP Transit Alerts plugin installed with a saved feed ID to work.</em></span>',
			'supplemental' => '',
			'default'      => false,
			'settings'     => 'tcp_cpt_fields',
			'classes'      => '',
		);
		$fields[] = $transit_alerts_field;
	}
	foreach ( $fields as $field ) {
		add_settings_field( $field['uid'], $field['label'], 'tcp_field_callback', $field['settings'], $field['section'], $field );
		register_setting( $field['settings'], $field['uid'] );
	}
}
add_action( 'admin_init', 'tcp_setup_fields' );

function tcp_field_callback( $arguments ) {
    $value = get_option( $arguments['uid'] );
    if ( ! $value ) {
       $value = $arguments['default'];
   }
// Check which type of field we want
switch( $arguments['type'] ) {
            case 'text':
            case 'password':
            case 'number':
			case 'email' :
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="%5$s"/>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], esc_attr($value), $arguments['classes'] );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], esc_textarea($value) );
                break;
            case 'select':
            case 'multiselect':
                if ( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
			case 'checkbox':
	            printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" %4$s class="%5$s"/>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], checked( $value, 'on', false ), $arguments['classes'] );
	            break;
			case 'multiple_checkbox':
				if (! empty ($arguments['options']) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator = 0;
					foreach( $arguments['options'] as $key => $label ) {
						$iterator++;
						$options_markup .= sprintf( '<label for="%1$s_%5$s"><input id="%1$s_%5$s" name="%1$s[%2$s]" type="checkbox" value="%2$s" %3$s> %4$s</label><br/>', $arguments['uid'], $key, in_array($key, $value) ? 'checked' : '', $label, $iterator);
					}
					printf( '<fieldset>%s</fieldset>', $options_markup );
				}
				break;
            case 'radio':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value, $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
            case 'file':
                printf('<label for="gtfs_zip_input">Select a .zip</label><input type="file" accept="application/zip,application/x-zip,application/x-zip-compressed" name="gtfs_zip_input"/>');
                break;
        }

   // If there is help text
   if ( $helper = $arguments['helper'] ) {
       printf( '<span class="helper"> %s</span>', $helper );
   }

   // If there is supplemental text
   if ( $supplemental = $arguments['supplemental'] ) {
       printf( '<p class="description">%s</p>', $supplemental );
   }
}

function tcp_custom_post_settings_content() {
    ?>
	<div class="wrap">
		<h1>Custom Posts and Settings</h1>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'tcp_cpt_fields' );
				do_settings_sections( 'tcp_cpt_fields' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

function tcp_gtfs_settings_content() { ?>
	<div class="wrap">
		<h1>GTFS Feed and Options</h1>
		<?php settings_errors(); ?>
		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<p> Your GTFS feed is used to create routes, update route fields,
					and link routes and timetables. In order to automatically generate
					timetables, please download GTFS-to-HTML.
				<form method="post" action="options.php">
					<?php
					settings_fields( 'tcp_gtfs_fields' );
					do_settings_sections( 'tcp_gtfs_fields' );
					submit_button();
					?>
				</form>
			</div>
		</div>
		<?php the_gtfs_update_form(); ?>
	</div>
	<?php
}

if ( TCP_CUSTOM_TYPES ) {

	if ( in_array('tcp_use_routes', TCP_CUSTOM_TYPES ) ) {
		add_action( 'admin_init', 'tcp_setup_route_options' );
	}
	if ( in_array('tcp_use_alerts', TCP_CUSTOM_TYPES ) ) {
		add_action( 'admin_init', 'tcp_setup_alert_options' );
	}
	if ( in_array('tcp_use_timetables', TCP_CUSTOM_TYPES ) ) {
		add_action( 'admin_init', 'tcp_setup_timetable_options' );
	}
	if ( in_array('tcp_use_board', TCP_CUSTOM_TYPES ) ) {
		add_action( 'admin_init', 'tcp_setup_board_options' );
	}
}

function tcp_setup_route_options() {
	add_settings_section( 'tcp_routes_options', '<hr/>Route Options', '', 'tcp_cpt_fields'  );
}

function tcp_setup_alert_options() {
	add_settings_section( 'tcp_alerts_options', '<hr/>Alert Options', '', 'tcp_cpt_fields' );
}

function tcp_setup_timetable_options() {
	add_settings_section( 'tcp_timetable_options', '<hr/>Timetable Options', '', 'tcp_cpt_fields' );
}

function tcp_setup_board_options() {
	add_settings_section( 'tcp_board_options', '<hr/>Board Meeting Options', '', 'tcp_cpt_fields' );
}
