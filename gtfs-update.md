# GTFS Update

During GTFS update, the site will pull live feed data and update routes as well as timetables if the optional `timetables.txt` file is present in your feed. Because an agency may need to create custom routes (for shuttles or other services not in the GTFS), GTFS update will not delete any existing routes or timetables even if they are no longer present in the GTFS. You will need to manually delete any outdated content.

## Settings

From the WP Admin screen, you can access GTFS Update from Transit Custom Posts > GTFS Settings. 

* **GTFS Feed Url** : This should be set to the source for the exported ZIP of your feed and saved before attempting to perform an update, unless you are manually supplying a feed.
* **Verify Backup** : GTFS update will modify your site database. We recommend always backing up your site before performing an update.
* **GTFS Feed Source** : Default is to use feed from export location URL. If your feed is not available from an export URL or the exported feed does not contain `timetables.txt`, you may instead choose to upload a feed zip from you computer.
* **Upload Timetables** : Upload a zipped folder containing timetable HTML files. If the plugin has trouble finding the timetables during GTFS update, check that all timetables are in the same folder, and there are not nested folders.

## Performing the Update

When you are ready to import new data from your GTFS feed (or update existing routes with new information), make sure the feed is set correctly and you have backed up the site.

Click GTFS Update and routes will automatically update. You will see a status code at the top of the page to let you know if the update was successful. Even after a success, you should always double check that route data was correctly imported.

### Status Codes

##### Insufficient permissions

Error: performing GTFS Update requires permissions equivalent to being able to update the core Wordpress--on most sites this may be editor or administrator privileges. 

##### Please confirm you have backed up the site

Error: GTFS update will not run unless you have checked the box confirming site backup.

##### Routes not activated

Error: The plugin cannot update routes if they do not exist. Navigate to the the Custom Post Types setttings menu and activate 'Routes' to continue.

##### Error downloading feed

Error: The plugin could not download the feed from the supplied URL. Check to make sure the URL is correct, and make sure you press 'Save Changes' before attempting an update. This error could also appear if `routes.txt` is not correctly formatted. 

##### No routes.txt present

Error: GTFS Update relies on the `routes.txt` file for necessary information. Check your feed to make sure it exists.

##### GTFS Update Success

Success: Files and feed present and correctly formatted. Check routes to make sure information is correct

##### GTFS Update Success, timetables not updated

Success: However, `timetables.txt` not present; assumed to be intentional. Upload the file to update timetables as well.

## Timetables

In order to programmatically add timetables to your site, you will need to be able to run a Node app ([GTFS-to-HTML](https://github.com/BlinkTagInc/gtfs-to-html)). There are plans to automate this process in the future using a web service version of GTFS-to-HTML, but as it stands the process requires some comfort with development tools.

The timetable custom post type can still be used without either of these abilities, but will require manually adding timetable content from the WP Admin area.

### Process

GTFS Update will look for a file named `timetables.txt` in your feed directory. If it exists, it will use the file to create a custom post for each listed timetable. GTFS update will also check for the directory `plugins/transit-custom-posts/transit-data/timetables`; if it exists, the plugin will attach any HTML files named with the timetable_id to the appropriate timetable. Otherwise, you will need to add content to each timetable post manually.

### timetables.txt

This file should be a CSV with the following columns at minimum:

* `timetable_id` : a unique ID for the timetable
* `route_id` : the ID for the timetable's route from `routes.txt`
* `start_date` : The start date for this timetable in YYYY-MM-DD format
* `end_date` : The end date for this timetable in YYYY-MM-DD format (set a date far in the future if this timetable is not set to expire anytime soon)
* `direction_name` : A label for the timetable direction, such as "Northbound" or "Loop"

**Highly recommended fields***

* `monday` : binary value that indicates whether this timetable shows service for Mondays. Valid values are `0` and `1`.
* `tuesday` : binary value that indicates whether this timetable shows service for Tuesdays. Valid values are `0` and `1`.
* `wednesday` : binary value that indicates whether this timetable shows service for Wednesdays. Valid values are `0` and `1`.
* `thursday` : binary value that indicates whether this timetable shows service for Thursdays. Valid values are `0` and `1`.
* `friday` : binary value that indicates whether this timetable shows service for Fridays. Valid values are `0` and `1`.
* `saturday` : binary value that indicates whether this timetable shows service for Saturdays. Valid values are `0` and `1`.
* `sunday` : binary value that indicates whether this timetable shows service for Sundays. Valid values are `0` and `1`.

## Uploading Feeds and Timetables

If you would like to automatically generate timetable posts but you are not able to export `timetables.txt` with your feed or you would like to use HTML timetables generated with [GTFS-to-HTML](https://github.com/BlinkTagInc/gtfs-to-html), you can use the options in GTFS Settings for manually uploading these files.

* If the public feed specified in **GTFS Feed Url** does not contain `timetables.txt`, you can instead use the **Upload feed manually** option to upload a zip that contains `timetables.txt` and the GTFS you would like to use.
* If you have timetable HTML files, they must be uploaded via the **Upload Timetables** field. Timetable HTML files should be zipped together and not nested in multiple directories for best results. Previously created timetables will only be overwritten if a new timetable HTML file is uploaded; therefore, it is possible but not required to just upload the timetables you would like to add or update.
