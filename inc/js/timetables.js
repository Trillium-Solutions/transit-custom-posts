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

				// Home button - focus on first tab
				if ( 36 === e.which ) {
					allTabs[0].focus();
				}	
				
				// End Button - focus on last tab
				if ( 35 === e.which ) {
					allTabs[ allTabs.length -1 ].focus();
				}	

				// Right Arrow Tab Navigation - focus on next tab
				if ( 39 === e.which ) {
					if ( allTabs.length -1 != currentTabIndex ) {
						allTabs[currentTabIndex + 1 ].focus();
					} 
					if ( allTabs.length - 1 === currentTabIndex ) {
						allTabs[0].focus();
					}
				}

				// Left Arrow Tab Navigation - focus on previous tab
				if ( 37 === e.which ) {
					if ( 0 != currentTabIndex ) {
						allTabs[currentTabIndex -1 ].focus();
					}
					if ( 0 === currentTabIndex ) {		
						allTabs[ allTabs.length -1 ].focus();
					}		
				}

				// Click or enter change selection
				if ( 13 === e.which || 'click' === e.type ) {

					// Update aria selected attribute for tab.
					var selectedContainer = jQuery(this).parent().attr('id');
					jQuery('#' + selectedContainer + ' button' ).attr('aria-selected', 'false');
				   
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
					jQuery('#timetable-nav').attr('aria-activedescendant', currentActiveDescendant );

					// Expand the selected panel by using currentActiveDescendant as panel ID
					jQuery('.timetable-panel').attr('aria-expanded', 'false');
					jQuery('.timetable-panel').attr('tabindex', -1 );
					jQuery('#' + currentActiveDescendant ).attr('aria-expanded', 'true');
					jQuery('#' + currentActiveDescendant ).attr('tabindex', 0 );
					//jQuery('#' + currentActiveDescendant ).focus();
				}		
			});
            jQuery('#days button').first().trigger('click');
        });
});             