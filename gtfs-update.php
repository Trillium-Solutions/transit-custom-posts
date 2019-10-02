<?php
/*
 * Automatic GTFS Update
 */

function the_gtfs_update_form() {
	?>
	<style type="text/css">#wpfooter {display: none;}</style>
	<h2>GTFS Site Update</h2>
	<p>
	GTFS update will automatically create and update route pages (if active) and timetables (if the optional <em>timetables.txt</em> file is included in the feed). Do not perform an update if you are not sure what you are doing.
	Performing GTFS update will automatically download the most recent version of your feed from the given feed URL.<br />
	For more information see the <a href="https://trilliumtransit.github.io/transit-custom-posts/gtfs-update.html">GTFS Update usage guide</a>.
	</p>
	<p><small></small></p>
	<form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>" enctype="multipart/form-data">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="backup">I verify that I have backed up the site before proceeding</label>
					</th>
					<td>
						<input type="checkbox" id="backup" name="backup" value="true" />
					</td>
				</tr>
				<tr>
					<th scope="row">GTFS Feed Source</th>
					<td id="feed-source">
						<fieldset>
							<legend class="screen-reader-text">
								<span>GTFS Feed Source</span>
							</legend>
							<p>
								<label>
									<input name="alternate_feed" type="radio" value="false" class="tog" checked="checked">
									Use feed from export location URL specified above
								</label>
							</p>
							<p>
								<label>
									<input name="alternate_feed" type="radio" value="true" class="tog">
									Upload feed manually (select below)
								</label>
							</p>
							<ul class="export-filters">
								<li>
									<label for="gtfs_zip_input">
									    Select a .zip
										<input type="file" id="gtfs_zip_input" name="gtfs_zip_input" accept="application/zip,application/x-zip,application/x-zip-compressed" />
									</label>
								</li>
							</ul>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">Upload Timetables</th>
					<td id="timetable-upload">
						<fieldset>
							<legend class="screen-reader-text">
								<span>Upload Timetables</span>
							</legend>
							<p>
								<label>
									Select a .zip file containing your HTML timetables <br />
									<input type="file" id="timetable_zip_input" name="timetable_zip_input" accept="application/zip,application/x-zip,application/x-zip-compressed" />
								</label>
							</p>
							<p class="description">If no timetables are provided, GTFS Update will still update routes as well as any changes to timetable fields present in <em>timetables.txt</em>.
							Previously uploaded timetable HTML will only be overwritten if new timetables are uploaded.</p>
						</fieldset>
					</td>
				</tr>
                <input type="hidden" name="gtfsupdate_noncename" id="gtfsupdate_noncename" value="<?php echo wp_create_nonce( 'gtfs-update' )?>">
				<input type="hidden" name="action" value="tcp_gtfs_update" />
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="GTFS Update" class="button button-primary"/>
		</p>
	</form>
	<?php
	// TODO: refactor this to be less repetitive, change error submission
	if(isset($_GET['submit_status'])) {  //
		$status_code = $_GET['submit_status'];
		if ( intval($status_code) < 200 ) {
			echo '<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible"><p>Submission Error: ';
			echo tcp_get_status_message( $status_code );
			echo '</p></div>';
		} else {
			echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p>';
			echo tcp_get_status_message( $status_code );
			echo '</p></div>';
		}
	}
	echo '</div>';
}
add_action( 'admin_action_tcp_gtfs_update','tcp_gtfs_update' );

function tcp_gtfs_update() {
    // Ensure request came from correct screen
    if ( !wp_verify_nonce( $_POST['gtfsupdate_noncename'], 'gtfs-update' )) {
		tcp_status_redirect('100');
    }
    // Ensure user has Admin capabilities
    if ( !current_user_can( 'update_core')) {
		tcp_status_redirect('101');
    }
    // Ensure backup was checked
	if(! isset($_POST['backup'])) {
		tcp_status_redirect('102');
	}
    // Ensure this theme is actually using custom Route types
	if ( !post_type_exists( 'route' ) ) {
		tcp_status_redirect('103');
	}

	$feed_path = tcp_download_feed();

    if ( !$feed_path ) {
		tcp_status_redirect('104');
    }

	$routes_txt = $feed_path . 'routes.txt';

	if ( !file_exists( $routes_txt) ) {
       tcp_status_redirect('104');
	}

	if ( !($res = tcp_update_routes($routes_txt)) ) {
		tcp_status_redirect('104');
	}
	if ( !post_type_exists( 'timetable' ) ) {
		tcp_status_redirect('200');
	}

	$timetables_txt = $feed_path . 'timetables.txt';

	if ( !file_exists( $timetables_txt ) ) {
		tcp_status_redirect('201');
	}
	if ( !($res = tcp_update_timetables($timetables_txt)) ) {
		tcp_status_redirect('201');
	}
	// We have passed the gauntlet of potential errors. Return success.
	tcp_status_redirect('200');
}

function tcp_download_feed() {
	if ( !get_option('tcp_gtfs_url') && !($_POST['alternate_feed'] == 'true') ) {
		return null;
	}

	$feed_dir = plugin_dir_path( __FILE__ ) . 'transit-data/';


	// Erase all old files; will delete any custom uploaded files as well
	array_map('unlink', glob( $feed_dir . '*.txt' ) );

	if ( !file_exists( $feed_dir ) ) {
		mkdir( $feed_dir, 0777, true );
	}

	// If using .zip direct upload, extract the zip to the feed Directory
	if ($_POST['alternate_feed'] == 'true') {
		$tmp_path = $_FILES['gtfs_zip_input']['tmp_name'];

		if ( !( $feed_download = @file_get_contents($tmp_path, true) ) ) {
			return null;
		}
	} else {
		$gtfs_feed = esc_url( get_option('tcp_gtfs_url') );

		if (!filter_var($gtfs_feed, FILTER_VALIDATE_URL)) {
			return null;
		}

		if ( !( $feed_download = @file_get_contents($gtfs_feed, true) ) ) {
			return null;
		}
	}

	$download_path = $feed_dir . 'gtfs-feed.zip';
	file_put_contents( $download_path, $feed_download );
	$zip = new ZipArchive;
	$res = $zip->open( $download_path );
	if ( $res != TRUE )  {
		return null;
	}
	$zip->extractTo( $feed_dir );
	$zip->close();
	return $feed_dir;
}

function tcp_update_routes( $route_file ) {
    $gtfs_data = array_map('str_getcsv', file($route_file));
    $header = array_shift($gtfs_data);
    array_walk($gtfs_data, '_combine_array', $header);
	$route_ids = array_column($gtfs_data, 'route_id');
	// delete any existing route posts that are not in the new GTFS
	$args = array(
		'post_type'		=> 'route',
		'numberposts'	=> -1,
		'meta_key'		=> 'route_id',
		'meta_value'		=> $route_ids,
		'meta_compare'		=> 'NOT IN',
	);
	$expired_routes = get_posts( $args );
	foreach( $expired_routes as &$to_delete ) {
		wp_delete_post( $to_delete->ID, true );
	}
	wp_reset_postdata();

    foreach( $gtfs_data as $ind=>$route ) {
        // If route_long_name exists, use it as the default name for post title and name
        $default_name = ($route['route_short_name'] == "") ? $route['route_long_name'] : 'Route ' . $route['route_short_name'];
        $tag_name = tcp_route_url($default_name);
        $route_id = $route['route_id'];

		//Check if the route post already exists. If not, create new route
		$post_to_update_id = null;
		$args = array(
			'post_type'		=> 'route',
			'numberposts'	=> 1,
			'post_status'	=> 'publish',
			'meta_key'		=> 'route_id',
			'meta_value'	=> $route_id,
		);
		$route_exists = get_posts( $args );
		if ( $route_exists ) {
			$post_to_update_id = $route_exists[0]->ID;
			$updated = array(
				'ID'			=> $post_to_update_id,
				'post_title'	=> $default_name,
				'post_name'		=> $tag_name
			);
			wp_update_post( $updated );
		} else {
			$my_post = array(
			  'post_title'    	=> $default_name,
			  'post_name' 		=> $tag_name,
			  'post_status'  	=> 'publish',
			  'post_type'      	=> 'route',
			  'post_author'   	=> 1
			);
			// Insert the post into the database
			$post_to_update_id = wp_insert_post( $my_post );
		}
        // Update route meta fields from GTFS data
        foreach ( $route as $key=>$value ) {
            if ( $key != "" ) {
                update_post_meta($post_to_update_id, $key, $value);
            }
        }
	}
	return true;
}

function tcp_route_url( $route_name ) {
	$default_name = str_replace("-", " ", str_replace(" - ", " ", $route_name));
	return trim(str_replace(" ", "-", strtolower($default_name)));
}

function tcp_update_timetables( $timetable_file ) {

	// Check to see if we have timetables to upload
	$upload_status = $_FILES['timetable_zip_input']['error'];
	if ($upload_status == 0) {
		tcp_upload_timetables();
	}

	// If files were attached but there is an error
	if (($upload_status != 0) && ($upload_status != 4)) {
		return null;
	}

    $gtfs_data = array_map('str_getcsv', file($timetable_file));
    $header = array_shift($gtfs_data);
    array_walk($gtfs_data, '_combine_array', $header);
	$timetable_ids = array_column($gtfs_data, 'timetable_id');
	// delete any existing timetables that are not in the new GTFS
	$args = array(
		'post_type'		=> 'timetable',
		'numberposts'	=> -1,
		'meta_key'		=> 'timetable_id',
		'meta_value'		=> $timetable_ids,
		'meta_compare'		=> 'NOT IN',
	);
	$expired_timetables = get_posts( $args );
	foreach( $expired_timetables as &$to_delete ) {
		wp_delete_post( $to_delete->ID, true );
	}
	wp_reset_postdata();

	foreach( $gtfs_data as $ind=>$timetable ) {
		// Figure out days of week for timetable
		$days_of_week = tcp_timetable_days( $timetable );
		unset(
			$timetable['monday'], $timetable['tuesday'], $timetable['wednesday'],
			$timetable['thursday'], $timetable['friday'], $timetable['saturday'], $timetable['sunday']
		);
		$timetable['days_of_week'] = $days_of_week;
		$timetable_name = tcp_timetable_name( $timetable );
		$tag_name = str_replace(" ", "_", strtolower($timetable_name));

		// Find out if content exists in timetables folder
		// TODO replace with more robust, easy to use utility
		$timetable_dir = plugin_dir_path( __FILE__ ) . 'transit-data/timetables/';
		$content = '';
		if ( file_exists( $timetable_dir ) ) {
			// Locate by timetable ID, hypothetically there should never be more than 1
			foreach( glob( $timetable_dir . $timetable['timetable_id'] . "_*.html") as $timetable_file ) {
				$content = file_get_contents($timetable_file, true);
			}
		}

		// Check if the timetable post already exists. If not, create new timetable
		$post_to_update_id = null;
		$args = array(
			'post_type'		=> 'timetable',
			'numberposts'	=> 1,
			'post_status'	=> 'publish',
			'meta_key'		=> 'timetable_id',
			'meta_value'	=> $timetable['timetable_id'],
		);
		$timetable_exists = get_posts( $args );
		if ( $timetable_exists ) {
			$post_to_update_id = $timetable_exists[0]->ID;
			$updated = array(
				'ID'			=> $post_to_update_id,
				'post_title'	=> $timetable_name,
				'post_name'		=> $tag_name,
				'post_content'	=> $content,
			);
			wp_update_post( $updated );
		} else {
			$my_post = array(
			  'post_title'    	=> $timetable_name,
			  'post_name' 		=> $tag_name,
			  'post_status'  	=> 'publish',
			  'post_type'      	=> 'timetable',
			  'post_content'	=> $content,
			  'post_author'   	=> 1
			);
			// Insert the post into the database
			$post_to_update_id = wp_insert_post( $my_post );
		}
        // Update route meta fields from GTFS data
        foreach ( $timetable as $key=>$value ) {
            if ( $key != "" ) {
                update_post_meta($post_to_update_id, $key, $value);
            }
        }
	}
	return true;
}

function tcp_timetable_name( $timetable ) {
	if ( array_key_exists('timetable_label', $timetable) ) {
		return $timetable['timetable_label'] . ' ' . $timetable['direction_label'] . ' ' . $timetable['days_of_week'];
	} else if ( array_key_exists('route_label', $timetable) ) {
		return $timetable['route_label'] . ' ' . $timetable['direction_label'] . ' ' . $timetable['days_of_week'];
	} else {
		return $timetable['timetable_id'] . ' ' . $timetable['direction_label'] . ' ' . $timetable['days_of_week'];
	}
}

function tcp_upload_timetables() {
	$timetable_dir = plugin_dir_path( __FILE__ ) . 'transit-data/timetables/';

	if ( !file_exists( $timetable_dir ) ) {
		mkdir( $timetable_dir, 0777, true );
	} else {
		array_map('unlink', glob( $timetable_dir . '*.html' ) );
	}
	$tmp_path = $_FILES['timetable_zip_input']['tmp_name'];
	$download_path = $timetable_dir . 'timetables.zip';
	$file_download = @file_get_contents( $tmp_path, true );
	file_put_contents( $download_path, $file_download );
	$zip = new ZipArchive;
	$res = $zip->open( $download_path );
	if ( $res != TRUE )  {
		return null;
	}
	// Only copy over HTML files and flatten directory structure
	for($i = 0; $i < $zip->numFiles; $i++) {
		$entry = $zip->getNameIndex($i);
		if ( preg_match('#\.(html)$#i', $entry) ) {
			$fileinfo = pathinfo($entry);
			copy( "zip://" . $download_path . "#" . $entry, $timetable_dir . $fileinfo['basename'] );
		}
	}
	$zip->close();
}

function tcp_status_redirect( $code ) {
	wp_redirect( $_SERVER['HTTP_REFERER'] . '&submit_status=' . $code );
	exit();
}

function tcp_get_status_message( $code ) {
	$codes = array(
		'100' => 'Illegal request.',
		'101' => 'Insufficient permissions. Please contact your admin.',
		'102' => 'Please confirm you have backed up site.',
		'103' => 'Routes not activated.',
		'104' => 'Error downloading feed. Please set GTFS feed correctly in GTFS settings first.',
		'105' => 'No routes.txt present. Unable to perform update.',
		'200' => 'GTFS Update Success. Please ensure <strong>Routes</strong> contain correct information.',
		'201' => 'GTFS Update Success. Please ensure <strong>Routes</strong> contain correct information. No timetables.txt present (timetables not updated).',
	);
	if ($codes[$code]) {
		return $codes[$code];
	} else {
		return $code;
	}
}

// TODO create a more robust day function
function tcp_timetable_days( $timetable ) {
	$days_of_week = '';
	$days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
	if ($timetable['monday'] && $timetable['tuesday'] && $timetable['wednesday'] && $timetable['thursday'] && $timetable['friday'] && $timetable['saturday'] && $timetable['sunday']) {
		$days_of_week = 'Daily';
	} else if ( $timetable['monday'] && $timetable['tuesday'] && $timetable['wednesday'] && $timetable['thursday'] && $timetable['friday'] && $timetable['saturday'] ) {
		$days_of_week = 'Mon-Sat';
	} else if ($timetable['monday'] && $timetable['tuesday'] && $timetable['wednesday'] && $timetable['thursday'] && $timetable['friday']) {
		$days_of_week = 'Weekday';
	} else if ( $timetable['saturday'] && $timetable['sunday'] ) {
		$days_of_week = 'Weekend';
	} else if ( $timetable['monday'] && $timetable['tuesday'] && $timetable['wednesday'] && $timetable['thursday']){
		$days_of_week = 'Mon-Thurs';
	} else {
		$timetable_days = array();
		foreach ($days as $day) {
			if ($timetable[$day]) {
				array_push($timetable_days, ucfirst($day));
			}
		}
		if (count($timetable_days) == 1) {
			$days_of_week = $timetable_days[0];
		} else {
			$idx = 0;
			while ($idx < count($timetable_days)) {
				$days_of_week .= $timetable_days[$idx] . ', ';
				$idx++;
			}
			$days_of_week .= $timetable_days[$idx];
		}
	}
	return $days_of_week;
}

// Array combine solution from dejiakala@gmail.com
function _combine_array(&$row, $key, $header) {
  $row = array_combine($header, $row);
}
