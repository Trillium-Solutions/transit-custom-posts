/*
 * Timestable.js 
 * Custom script for hiding and showing timetables
 * that are using the timestables-legend template.
 * Version 1.01
 */ 

jQuery( document ).ready(function() {
    var tabs = jQuery('#timetable-nav input');
	jQuery.each( tabs, function() {
		// Update selected panel aria attributes on timetable table option change.
		jQuery(this).on('change', function(e) {
			// Get the active descendant.
			var selectedDay       = jQuery('#days input:checked').val();
			var selectedDirection = jQuery('#direction input:checked').val()
			var activeDescendant  =  selectedDay + '-' + selectedDirection + '-tab';

			// Hide/Show direction options according to days.
			jQuery('#direction label').show();
			jQuery('#direction input').each( function() {
				// Pull tab panel for direction option.
				var tabPanelID = '#' + selectedDay + '-' + jQuery( this ).val() + '-tab';
				// Check if tab panel exists and if not hide direction option.
				if ( 0 === jQuery( tabPanelID ).length ) {
					var directionTab = jQuery( 'label[for="tab-' + jQuery( this ).val() + '"]');
					directionTab.hide();
					jQuery( this ).hide();
				} 
			});

			// Set the active descendant.
			jQuery('#timetable-nav').attr('aria-activedescendant', activeDescendant );

			// Expand the selected panel using active descendant as panel ID
			jQuery('.timetable-panel').attr('aria-expanded', 'false');
			jQuery('.timetable-panel').attr('tabindex', -1 );
			jQuery('#' + activeDescendant ).attr('aria-expanded', 'true');
			jQuery('#' + activeDescendant ).attr('tabindex', 0 );
		});
		// Trigger change for default selection.
        jQuery('#days input').first().trigger('change');
    });
});             