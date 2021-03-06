# Transit Custom Posts Plugin

This Wordpress plugin is designed to make creating and maintaining transit websites as simple as possible. It supplies Route, Timetable, Alert, and Board Meeting custom post types, pulls in route and timetable information programmatically from GTFS data, and features and API with many convenience functions for common transit site problems.

[Read more about the API](https://trillium-solutions.github.io/transit-custom-posts/) and how to use the plugin.

## Installation

If you download a compatible theme such as [Transit Base Template](https://github.com/trilliumtransit/transit-base-template), it will direct you to automatically install this plugin. Otherwise, if you would like to use it on its own to develop your own theme or integrate it with an existing theme:

* [Download ZIP](https://github.com/trilliumtransit/transit-custom-posts/archive/master.zip)
* Install from WP Admin area in Plugins > Add New > Upload Plugin

Afterward, you can configure the plugin from the WP Admin screen in the Transit Custom Posts submenus. More documentation is available on [the Github site for this project](https://trillium-solutions.github.io/transit-custom-posts/).

## Timetables ## 

- Upcoming timetables are not visible until the current date is within their date range.
- Expired timetables will disappear if there are still timetables within the current date range.
- Expired timetables will be shown if all current timetables are expired.
- If there are expired timetables, upcoming timetables, and no current timetables, then the expired timetables will be shown until the upcoming timetables become current.

### Timetables options ###

Timetable options are controlled from the Timetable options section of the TCP settings page. 

There are 3 display options for timetables. 
- Default: Timetables are displayed vertically, and ordered by the 'timetable id'.
- Legend: Timetables are displayed with a legend to toggle between days and directions.
- Accordion: Timetables are displayed in collapsed accordions, and ordered by the 'timetable id'.

To enable the Legend or Accordion displays, use the checkboxes on the settings page.

#### Vertically reorder timetables ####

Timetables are sorted by the 'timetable id’ by default. To reorder timetables vertically, set the route to 'Order by': 'Timetable Order Field' and set the timetables 'Timetable Order' field in the numerical order you want the timetables to appear.

#### Preview upcoming timetables ####

To preview upcoming timetables, enable ‘Preview Upcoming Timetables’ from the settings page and below that designate the amount of days ahead you would like to preview in the ‘Preview Duration’ field.

#### Timetable legend options ####

Timetable legend options are controlled from the route page editor.
- Reverse order of directions in legend
- Reverse selected direction on page load

## ACF JSON ##

TCP has ACF fields stored in /acf-json. These fields must be imported to function correctly. To import the ACF fields in TCP, from WP admin go to ‘Custom Fields’  and select ‘sync available’.

If you have new ACF JSON for the TCP plugin, create the fields in ACF and the JSON files will be placed in your base theme. Take those JSON files and move them to the /acf-json directory of TCP.

## Developer Notes ## 

### Shortcodes ### 

These shortcodes can be used in the WP editor.

- tcp_list_routes - Outputs all route names with formatting. Use anywhere.
- tcp_list_routes_with_colors - List of all routes with colors. Use anywhere.
- the_route_title - Displays the route title with formatting from plugin options. Single routes.
- the_route_description - Outputs route description from post meta. Single routes.
- the_timetables - Outputs all timetables for a route from inside the loop. Single routes.

### Gutenberg Blocks ### 

These blocks can be used in the Gutenberg editor.

- List Routes - Outputs all route names with formatting. Use anywhere.
- Route Title - Displays the route title with formatting from plugin options. Single routes.
- Route Description - Outputs route description from post meta. Single routes.
- Timetables - Outputs all timetables for a route from inside the loop. Single routes.

### Hooks and Filters ### 

The Transit Custom Posts plugin offers several hooks and filters for easily extending and customizing core plugin functions. These include: 

- Route Title - add_filter('tcp_filter_route_title', function( $html ){}, 10, 1);
- Route Name - add_filter('tcp_route_name', function( $format ){}, 10, 1);
- Route Circle - add_filter('get_route_circle', function( $post_id ){}, 10, 1);
- Route URL - add_filter('tcp_filter_route_url', function( $post_id ){}, 10, 1);

- Timetable days of the week - add_filter('tcp_timetable_filter_days_of_week', function( $days_of_week ){}, 10, 1);
- Routes text file path - add_filter('tcp_filter_route_txt_path', function( $routes_txt ){}, 10, 1);
- Timetable text file path - add_filter('tcp_filter_timetables_txt_path', function( $timetables_txt ){}, 10, 1);
- GTFS download feed directory - add_filter('tcp_gtfs_download_feed_dir', function( $feed_dir ){}, 10, 1);
* Above filter overrides where GTFS update data is being pulled from when applicable.
- GTFS update route pages - add_action( 'tcp_gtfs_update_routes’, function( $routes_txt ){}, 10, 1);
- GTFS update timetable pages - add_action( 'tcp_gtfs_update_timetables’, function( $timetables_txt ){}, 10, 1);
- GTFS Timetable directory - add_filter( 'tcp_filter_timetable_directory', function( $timetable_dir ), {}, 10, 1);
- After route update - add_action( 'after_tcp_route_update’, function( $post_to_update_id, $route ){}, 10, 2);
- After timetable update - add_action( 'after_tcp_timetable_update’, function( $post_to_update_id, $timetable ){}, 10, 2);

### Use with WP Transit Alerts Plugin ###

You can use Transit Custom Posts with the WP Transit Alerts Plugin by checking the override Transit Custom Posts alerts option in the Transit Custom Posts settings. A few additional alerts api options are provided to help make the transition easier. These additional alerts arguments or options include:

 - alerts-title: Default 'Current Alerts '
 - alerts-id: This is the id for the alerts container
 - custom-classes: This is an array of preset keys to help customize a single alert. They include options for: 
    - alert-container - Allows addition of custom class for the alert container
    - alert-title - Allows addition of a custom class for the alert title
    - alert-desc - Allows addition of custom class for the alert description
    - alert-dates - Allows addition of custom class for the alert dates or meta

## File Summary ##
- api.php -  API custom functions
- gtfs-update.php - Imports GTFS data
- settings-page.php - Interface to control the plugin
- transit-custom-posts.php - The base file
- cpts/ - Location of the custom post types
- Inc/ - CSS, JS, and media
- acf-json/ - Advanced Custom Fields JSON to be imported

## How to Contribute

Issues/bugs, pull-requests, feedback, and documentation are always appreciated. Please see the issue log for currently open and unassigned tasks.

The Wordpress ecosystem could use more themes compatible with this plugin! Feel free to hack away at our [starter template](https://github.com/trilliumtransit/transit-base-template) as a working example and help us make public transit more accessible and easy-to-use with beautiful websites and quality software. It's all GPLv2, so take what you want to make your own awesome implementation.

## Credits

Created by Trillium Solutions, inc for the Northwest Oregon Transit Alliance

Authors: NomeQ 2017 & ChanceCorbeil 2021