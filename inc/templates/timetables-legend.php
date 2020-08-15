

<div id="timetable-nav" role="navigation" aria-label="Timetable Multiselect Tablist"  aria-activedescendant="<?php echo strtolower( $days[0] ) . ' ' . strtolower( $directions[0] ); ?>">  
    <?php if ( ! empty( $days ) && ( count( $days ) > 1 || ! empty( $directions ) ) ) { ?>
        <div id="days">
            <h3>Days:</h3>
            <div class="button-group" role="tablist" aria-label="Timetable Days">
                <?php 
                $day_count = 0;
                foreach( $days as $day ) {
                    $day_selected = $day_count > 0 ? false : true;
                    echo '<button role="tab" aria-selected="' . $day_selected .'"  aria-label="' .  strtolower( $day ) . '" aria-controls="' . strtolower( $day ) . '">' . $day . '</button>';
                    $day_count++;   
                }
            ?>    
        </div>
    <?php } ?>    
    </div>
    <?php if ( ! empty( $directions ) ) { ?>
        <div id="direction">
            <h3>Direction:</h3>
            <div class="button-group" role="tablist" aria-label="Timetable Directions">
                <?php 
                    $direction_count = 0;
                    foreach( $directions as $direction ) {
                        $direction_selected = $direction_count > 0 ? false : true;
                        echo '<button role="tab" aria-selected="' . $direction_selected .'" aria-label="' . strtolower( $direction ) . '" aria-controls="' . strtolower( $direction ) . '">' . $direction . '</button>';
                        $direction_count++;
                    } 
                ?>
		    </div>
	    </div>
    <?php } ?>   
</div>	
<?php if ( ! empty( $timestables ) ) {
    $timetables_by_day_dir = array();
    foreach( $timestables as $table ) {
        $timetable_key = strtolower( $table['day'] ) . ' ' . strtolower( $table['direction'] );
        if ( ! array_key_exists( $timetable_key, $timetables_by_day_dir ) ) {
            $timetables_by_day_dir[ $timetable_key ] = array();
        }
        array_push( $timetables_by_day_dir[ $timetable_key ], $table['table'] );
    }
    foreach( $timetables_by_day_dir as $key => $value ) {
        echo '<div  id="' . $key  . '" class="timetable-panel">';
        foreach ( $value as $timetable ) {
            echo $timetable;
        }
        echo '</div>';
    }
} ?>