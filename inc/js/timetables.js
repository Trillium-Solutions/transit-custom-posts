/*
 * Timestable.js 
 * Custom script for hiding and showing timetables
 * that are using the timestables-legend template.
 * Version 1.0
 */ 

jQuery( document ).ready(function() {
    var tabs = jQuery('#timetable-nav button');
		jQuery.each( tabs, function() {
			jQuery(this).on('click keyup', function(e) {
			
				var allTabs         = jQuery('#timetable-nav button');
				var currentTabIndex = allTabs.index( jQuery(this) );

				// Home button 
				if ( 36 === e.which ) {
					allTabs[0].focus();
				}	
				
				// End Button 
				if ( 35 === e.which ) {
					allTabs[ allTabs.length -1 ].focus();
				}	

				// Right Arrow Tab Navigation 
				if ( 39 === e.which ) {
					// Set focus on next tab.
					if ( allTabs.length -1 != currentTabIndex ) {
						allTabs[currentTabIndex + 1 ].focus();
					} 
					if ( allTabs.length - 1 === currentTabIndex ) {
						allTabs[0].focus();
					}
				}

				// Left Arrow Tab Navigation
				if ( 37 === e.which ) {
					// Set focus on previous tab.
					if ( 0 != currentTabIndex ) {
						allTabs[currentTabIndex -1 ].focus();
					}
					if ( 0 === currentTabIndex ) {		
						allTabs[ allTabs.length -1 ].focus();
					}		
				}

				// Click or enter change selection
				if ( 13 === e.which || 'click' === e.type ) {

					// find the container of selected item 
					// mark the aria false for those items... 
					var selectedContainer = jQuery(this).parent().attr('id');
					jQuery('#' + selectedContainer + ' button' ).attr('aria-selected', 'false');
			   	
					// Set selected tab aria-selected.
					jQuery(this).attr('aria-selected', 'true' );

					// Update the active descendant.
					var daysSelection      = jQuery('#days button[aria-selected="true"]').attr('aria-controls');
					var directionSelection = jQuery('#direction button[aria-selected="true"]').attr('aria-controls');
					var currentActiveDescendant =  '';

					if ( daysSelection ) {
						currentActiveDescendant = daysSelection;
						if ( directionSelection ) {
							currentActiveDescendant += '-' + directionSelection;
						}
					}
					if ( ! daysSelection && directionSelection ) {
						currentActiveDescendant = directionSelection;
					}
					currentActiveDescendant += '-tab';
					console.log( currentActiveDescendant );
					jQuery('#timetable-nav').attr('aria-activedescendant', currentActiveDescendant );

					// Expand the selected panel by using currentActiveDescendant as panel ID
					jQuery('.timetable-panel').attr('aria-expanded', 'false');
					jQuery('.timetable-panel').attr('tabindex', -1);
					jQuery('#' + currentActiveDescendant ).attr('aria-expanded', 'true');
					jQuery('#' + currentActiveDescendant ).attr('tabindex', 0);
				}		
			});
            jQuery('#days button').first().trigger('click');
        });
});             