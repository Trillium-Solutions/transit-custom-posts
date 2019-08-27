<?php
/*
 * Automatic GTFS Update
 */

function the_timetable_upload_form() {
	?>
	<style type="text/css">#wpfooter {display: none;}</style>
	<p>This utility allows you to upload a .zip file created by <a href="https://github.com/BlinkTagInc/gtfs-to-html">gtfs-to-html</a> directly to your site. In order to use this utility, you must run the <a href="https://github.com/BlinkTagInc/gtfs-to-html">gtfs-to-html</a> utility locally.
	<p>Make sure that you have created the correct timetables, and also that you have backed up your site before proceeding. It is recommeneded you use this function in tandem with the manual GTFS feed upload.</p>
	<form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
				<tr>
					<label for="timetable_input">Select a .zip</label>
					<input type="file" id="timetable_input" name="timetable_input" accept="application/zip,application/x-zip,application/x-zip-compressed" />
					</tr>
				<tr>
					<th scope="row">
						<label for="backup">I verify that I have backed up the site before proceeding</label>
					</th>
					<td>
						<input type="checkbox" id="backup" name="backup" value="true" />
					</td>
				</tr>
                <input type="hidden" name="timetable_upload_noncename" id="timetable_upload_noncename" value="<?php echo wp_create_nonce( 'timetable-upload' )?>">
				<input type="hidden" name="action" value="tcp_timetable_upload" />
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="Upload Timetables" class="button button-primary"/>
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
add_action( 'admin_action_tcp_timetable_upload','tcp_timetable_upload' );

function tcp_timetable_upload() {

    // Ensure request came from correct screen
    if ( !wp_verify_nonce( $_POST['timetable_upload_noncename'], 'timetable-upload' )) {
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

	$feed_path = tcp_upload_timetable();

    if ( !$feed_path ) {
		tcp_status_redirect('104');
    }
	//
	// $routes_txt = $feed_path . 'routes.txt';
	//
	// if ( !file_exists( $routes_txt) ) {
    //    tcp_status_redirect('104');
	// }

	// if ( !($res = tcp_update_routes($routes_txt)) ) {
	// 	tcp_status_redirect('104');
	// }
	// if ( !post_type_exists( 'timetable' ) ) {
	// 	tcp_status_redirect('200');
	// }

	// $timetables_txt = $feed_path . 'timetables.txt';

	// if ( !file_exists( $timetables_txt ) ) {
	// 	tcp_status_redirect('201');
	// }
	// if ( !($res = tcp_update_timetables($timetables_txt)) ) {
	// 	tcp_status_redirect('201');
	// }
	// We have passed the gauntlet of potential errors. Return success.
	tcp_status_redirect('200');
}

function tcp_upload_timetable() {
	error_log(isset($_POST['alternate_feed']));

	if ( !get_option('tcp_gtfs_url') && !isset($_POST['alternate_feed']) ) {
		error_log('returning null');
		return null;
	}

	$feed_dir = plugin_dir_path( __FILE__ ) . 'transit-data/';

	// If using a manually uploaded feed, continue to next step
	// if ( isset($_POST['alternate_feed']) && file_exists($feed_dir) ) {
	// 	return $feed_dir;
	// }


	// Erase all old files; will delete any custom uploaded files as well
	if ( file_exists($feed_dir . 'timetables/') ) {
		rrmdir($feed_dir . 'timetables/');
	};

	if ( !file_exists( $feed_dir ) ) {
		mkdir( $feed_dir . 'timetables/', 0777, true );
	};

	// copy new folder to /timetables
	var_error_log($_FILES['timetable_input']);
	$tmp_path = $_FILES['timetable_input']['tmp_name'];
	$download_path = $feed_dir . 'timetables.zip';
	$feed_download = @file_get_contents($tmp_path, true);
	file_put_contents( $download_path, $feed_download );
	$zip = new ZipArchive;
	$res = $zip->open( $download_path );
	if ( $res != TRUE )  {
		return null;
	}
	$zip->extractTo( $feed_dir );
	$zip->close();
	$unzippered_files = scandir($feed_dir . '/html');
	var_error_log($unzippered_files);
	recurse_copy($feed_dir . '/html/' . $unzippered_files[2], $feed_dir . '/timetables');
	return $feed_dir;

}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}
