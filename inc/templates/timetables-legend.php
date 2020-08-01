<div id="timetable-nav">
    <div id="days">
        <h3>Days:</h3>
        <div class="button-group">
            <?php if ( ! empty( $days ) ) {
                foreach( $days as $day ) {
                    echo '<button id="' . strtolower( $day ) . '">' . $day . '</button>';
                }
            } ?>
		</div>
	</div>
    <div id="direction">
        <h3>Direction:</h3>
        <div class="button-group">
            <?php if ( ! empty( $directions ) ) {
                foreach( $directions as $direction ) {
                    echo '<button id="' . strtolower( $direction ) . '">' . $direction . '</button>';
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