# Changelog
All notable changes to this project will be documented in this file.

## [2.0.2] - 2020-10-29
- Adjusted GTFS update, so that it does not update existing timetable content with empty content

## [2.0.1] - 2020-10-26
- Added option to not import empty timetables when running gtfs update
- Changed alerts to pull only the alerts whose end date is greater than or equal to current timezone in WordPress

## [2.0] - 2020-10-19
- Added ability to toggle the WordPress Editor for timetable and route custom post types
- Added option to override alerts custom post type with transit alerts if the WP Transit Alerts plugin is installed 
- Added additional filters and actions to GTFS update for better integration of legacy sites with Transit Custom Posts 
- New timetable template with optional legend

## [0.9.4] - 2020-08-19
- Added timestable legend option to the_timetables() api function

## [0.9.3] - 2019-10-16
- Allowing folders inside GTFS feed zip

## [0.9.1] - 2018-01-10
- Version bump to force update

## [0.9.0] - 2017-12-19
- Added admin settings pages
- Development of custom post backend 
- Improved comments and code style

## [0.1.0] - 2017-07-20
- Initial Commit
