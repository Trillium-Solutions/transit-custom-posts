
<?php $default_tab = strtolower( $days[0] ) . '-' . strtolower( $directions[0] ) . '-tab'; ?>
<div id="timetable-nav" role="tablist" aria-multiselectable="true" aria-label="Timetable Options"  aria-activedescendant="<?php echo $default_tab; ?>" tabindex="0">  
    <?php if ( ! empty( $days ) && ( count( $days ) > 1 || ! empty( $directions ) ) ) { ?>
        <div role="group" id="days" class="button-group" aria-labelledby="days-title">
            <h3 id="days-title">Days:</h3>
            <?php $day_count = 0;
                foreach( $days as $day ) {
                    $day_selected = $day_count > 0 ? "false" : "true";
                    echo '<button role="tab" aria-selected="' . $day_selected .'"  aria-label="' .  strtolower( $day ) . '" aria-controls="' . strtolower( $day ) . '">' . $day . '</button>';
                    $day_count++;   
                }  
            ?>
        </div>    
    <?php } ?>    
    <?php if ( ! empty( $directions ) ) { ?>
        <div role="group" id="direction" class="button-group" aria-labelledby="direction-title">
            <h3 id="direction-title">Direction:</h3>
                <?php 
                    $direction_count = 0;
                    foreach( $directions as $direction ) {
                        $direction_selected = $direction_count > 0 ? "false" : "true";
                        echo '<button role="tab" aria-selected="' . $direction_selected .'" aria-label="' . strtolower( $direction ) . '" aria-controls="' . strtolower( $direction ) . '">' . $direction . '</button>';
                        $direction_count++;
                    } 
                ?>
	    </div>
    <?php } ?> 
</div>	
<?php if ( ! empty( $timestables ) ) {
    $timetables_by_day_dir = array();
    foreach( $timestables as $table ) {
        $timetable_key = '';
        if ( ! empty( $table['day'] ) ) {
            $timetable_key .= strtolower( $table['day'] ) . '-';
        }
        if ( ! empty( $table['direction'] ) ) {
            $timetable_key .= strtolower( $table['direction'] ) . '-tab';
        }
        if ( ! array_key_exists( $timetable_key, $timetables_by_day_dir ) ) {
            $timetables_by_day_dir[ $timetable_key ] = array();
        }
        array_push( $timetables_by_day_dir[ $timetable_key ], $table['table'] );
    }
    foreach( $timetables_by_day_dir as $key => $value ) {
        $aria_label = str_replace( '-', ' ', $key );
        $aria_label = trim( str_replace( 'tab', ' ', $aria_label ) );
        echo '<div  role="tabpanel"  id="' . $key  . '" class="timetable-panel" aria-labelledby="' . $aria_label . '" aria-expanded="false" >';
        foreach ( $value as $timetable ) {
            echo $timetable;
        }
        echo '</div>';
    }
} ?>