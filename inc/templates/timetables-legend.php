
<?php 
    // Setting default tab.
    $default_tab = strtolower( $days[0] ) . '-' . strtolower( $directions[0] ) . '-tab';
    if ( $na_dir_button ) {
        $default_tab = strtolower( $days[0] ) . '-no-direction-tab';
    } else if ( $na_day_button ) {
        $default_tab = strtolower( $directions[0] ) . '-no-day-tab';
    }
    if ( count( $days ) > 1 || count( $directions ) > 1 ) { ?>
        <div id="timetable-nav" role="tablist" aria-multiselectable="true" aria-label="Timetable Options"  aria-activedescendant="<?php echo $default_tab; ?>" tabindex="0">  
        <?php if ( ! empty( $days ) && ( count( $days ) > 1 || ! empty( $directions ) ) ) { ?>
            <fieldset role="radiogroup" id="days" aria-labelledby="days-title">
                <legend id="days-title">Days:</legend>
                <?php 
                    $day_count = 0;
                    foreach( $days as $day ) {
                        $day_selected = $day_count > 0 ? "" : 'checked="checked"';
                        $day_text     = $day;

                        if ( $na_day_button ) {
                            if ( 'no-day' === $day ) { 
                                $day_selected = 'checked="checked"';
                                $day_text     = 'N/A';
                            } else {
                                $day_selected = "";
                            }
                        }
                        echo '<input role="tab" id="tab-' .  strtolower( $day ) . '" type="radio" name="days" value="' . strtolower( str_replace(', ', '', $day ) ) .'"  aria-controls="' . strtolower( str_replace( ', ', '', $day ) ) . '" ' . $day_selected . '"/>';
                        echo '<label for="tab-' .  strtolower( $day ) . '">' . $day_text . '</label>';
                        $day_count++;   
                    } 
                ?>
            </fieldset>    
        <?php } ?>    
        <?php if ( ! empty( $directions ) ) { ?>
            <fieldset role="radiogroup" id="direction" aria-labelledby="direction-title">
                <legend id="direction-title">Directions:</legend>
                <?php 
                    $direction_count = 0;
                    foreach( $directions as $direction ) {
                        $direction_selected = $direction_count > 0 ? "" : 'checked="checked"';
                        $direction_text     = $direction;
                        if ( $na_dir_button ) {
                            if ( 'no-direction' === $direction ) { 
                                $direction_selected = 'checked="checked"';
                                $direction_text     = 'N/A';
                            } else {
                                $direction_selected = "";
                            }
                        }
                        echo '<input role="tab" id="tab-' .  strtolower( $direction ) . '" type="radio" name="directions" value="' . strtolower( str_replace( ', ', '', $direction ) ) .'"  aria-controls="' . strtolower( str_replace(', ', '', $direction ) ) . ' "' . $direction_selected . '"/>';
                        echo '<label for="tab-' .  strtolower( $direction ) . '">' . $direction_text . '</label>';
                        $direction_count++;
                    } 
                ?>
	        </fieldset>
        <?php } ?> 
    </div>	
    <?php if ( ! empty( $timestables ) ) {
        $timetables_by_day_dir = array();
        foreach( $timestables as $table ) {
            $timetable_key = '';
            // Day and direction key.
            if ( ! empty( $table['day'] ) && ! empty( $table['direction'] ) ) {
                $timetable_key .= strtolower( $table['day'] ) . '-' . strtolower( $table['direction'] ) . '-tab';
            }
            // Only day key.
            if ( ! empty( $table['day'] ) && empty( $table['direction'] ) ) {
                $timetable_key .= strtolower( $table['day'] ) . '-no-direction-tab';
            }
            // Only direction key.
            if ( empty( $table['day'] ) && ! empty( $table['direction'] ) ) {
                $timetable_key .= strtolower( $table['direction'] ) . '-no-day-tab';
            }   
            if ( ! array_key_exists( $timetable_key, $timetables_by_day_dir ) ) {
                $timetables_by_day_dir[ $timetable_key ] = array();
            }
            array_push( $timetables_by_day_dir[ $timetable_key ], $table['table'] );
        }
        foreach( $timetables_by_day_dir as $key => $value ) {
            $aria_label = str_replace( '-', ' ', $key );
            $aria_label = trim( str_replace( 'tab', ' ', $aria_label ) );
            echo '<div role="tabpanel"  id="' . str_replace(', ','', $key )  . '" class="timetable-panel" aria-label="' . $aria_label . '" aria-expanded="false" tabindex="-1">';
            foreach ( $value as $timetable ) {
                echo $timetable;
            }
            echo '</div>';
        }
    } 
} else {
    foreach( $timestables as $table ) {
        echo $table['table'];
    }    
} ?>