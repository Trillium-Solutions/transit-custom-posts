<div id="timetable-nav" data-days="<?php echo strtolower( $days[0] ); ?>" data-direction="<?php echo strtolower( $directions[0] ); ?>">
    <div id="days">
        <h3>Days:</h3>
        <div class="button-group">
            <?php if ( ! empty( $days ) ) {
                $day_count = 0;
                foreach( $days as $day ) {
                    $day_pressed = $day_count > 0 ? false : true;
                    echo '<button id="' . strtolower( $day ) . '" aria-pressed="'. $day_pressed .'">' . $day . '</button>';
                    $day_count++;
                }
            } ?>
		</div>
	</div>
    <div id="direction">
        <h3>Direction:</h3>
        <div class="button-group">
            <?php if ( ! empty( $directions ) ) {
                $direction_count = 0;
                foreach( $directions as $direction ) {
                    $direction_pressed = $direction_count > 0 ? false : true;
                    echo '<button id="' . strtolower( $direction ) . '" aria-pressed="' . $direction_pressed . '">' . $direction . '</button>';
                    $direction_count++;
                }
            } ?>
		</div>
	</div>
</div>	
<?php if ( ! empty( $timestables ) ) {
    foreach( $timestables as $table ) {
        echo '<div class="timestable ' . strtolower( $table['day'] ) . ' ' .strtolower( $table['direction'] ) . '">';
            echo  $table['table'];
            echo '</div>';
        }
    }
?>