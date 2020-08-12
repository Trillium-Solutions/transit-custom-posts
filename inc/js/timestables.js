/*
 * Timestable.js 
 * Custom script for hiding and showing timetables
 * that are using the timestables-legend template.
 * Version 1.0
 */ 

jQuery( document ).ready(function() {
    var button_groups = jQuery('.button-group button');
		jQuery.each( button_groups, function() {
			jQuery(this).on('click', function() {
				// Get selected button and button type.
				var buttonClass = jQuery(this).attr('id');
				var buttonType  = jQuery(this).parent().parent().attr('id');

				// Update timetable nav data attributes with new selections.
				jQuery('#timetable-nav').attr( 'data-' + buttonType, buttonClass );

				// Get timetable nav selections via data attributes.
				var days                = jQuery('#timetable-nav').attr('data-days');
				var direction           = jQuery('#timetable-nav').attr('data-direction');
				var selectedTimestables = '.' + days + '.' + direction;

				// Remove show class from timestables and re-add it to 
				// the selectedTimestables.
				jQuery( '.timestable' ).removeClass('show');
                jQuery( selectedTimestables ).toggleClass('show');
            });
            jQuery('#days .button-group button').trigger('click');
        });
});             