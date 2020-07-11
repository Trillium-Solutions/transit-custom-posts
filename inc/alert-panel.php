<div class="tcp_panel">
    <?php       
        
        // Alert panel heading
        echo '<div class="panel_heading">';
            
            // Alert button header
            if ( $alert_button ) {
                echo '<h4><button class="btn-link" data-toggle="' . $collapsible . '" data-target="' . '#' . $panel_class . '" aria-expanded="false" aria-controls="collapse-' . $panel_class . '">' . $alert_title . '</h4>';
            }
            // Alert Link header 
            if ( ! $alert_button && ! empty( $alert_url ) ) {
                echo '<h4><a href="' . $alert_url . '" data-toggle="' . $collapsible . '" data-target="' . '#'.$panel_class . '" aria-expanded="false" aria-controls="collapse-' . $panel_class . '">' . $alert_title . '</a></h4>';
            } 
            // Alert no button and not link header
            if ( ! $alert_button && empty( $alert_url ) ) {
                echo '<h4 data-toggle="' . $collapsible . '" data-target="' . '#'.$panel_class . '" aria-expanded="false" aria-controls="collapse-' . $panel_class . '"">' . $alert_title . '</h4>';
            }
        
        echo '</div>';
        
        // Alert panel body
        echo '<div class="panel_body ' . $collapsible . '" id="' . $panel_class . '"><div class="panel_description"><div class="panel_subheading">' . $alert_dates . '<span class="tcp_affected_routes"> ' . $affected_text . '</span></div>' . $alert_desc .'</div>';

        if ( ! empty( $alert_url )  && ! empty( $link_text ) ) {
            echo '<a href="' . $alert_url . '">' . $link_text  . '</a>';
        }
    
        echo '</div>';
    ?>    
</div>