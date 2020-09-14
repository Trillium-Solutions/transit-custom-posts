<div class="tcp_panel <?php echo esc_attr( $alert_container_class ); ?>">
    <?php       
        // Alert panel heading
        echo '<div class="panel_heading">';
            
            // Alert button header
            if ( $collapsible === 'collapse' ) {
                $collapsible_attributes = 'data-toggle="' . $collapsible . '" data-target="' . '#' . $panel_class . '" aria-expanded="false" aria-controls="collapse-' . $panel_class . '" ' . $alert_title_class; 
            }  else {
                $collapsible_attributes = $alert_title_class; 
            }

            if ( $alert_button ) {
                echo '<h3><button class="btn-link"' . $collapsible_attributes . '>' . $alert_title . '</h>';
            }
            // Alert Link header 
            if ( ! $alert_button && ! empty( $alert_url ) ) {
                echo '<h3><a href="' . $alert_url . '" ' . $collapsible_attributes . '>' . $alert_title . '</a></h3>';
            } 
            // Alert no button and not link header
            if ( ! $alert_button && empty( $alert_url ) ) {
                echo '<h3 ' . $collapsible_attributes . '>' . $alert_title . '</h3>';
            }
        
        echo '</div>';
       
        // Alert panel body
        echo '<div class="panel_body ' . $collapsible . '" id="' . $panel_class . '">';
        
        // Alert Description 
        echo '<div class="panel_description ' . esc_attr( $alert_desc_class )  . '">' . $alert_desc .'</div>';

        // Alert Start/End dates 
        if ( ! empty( $alert_dates ) ) {
            echo '<div class="panel_subheading ' . ' ' . esc_attr( $alert_dates_class ) . '">';
            echo  $alert_dates;
            echo '</div>';
        }

        // Alert affected routes
        if ( ! empty( $affected_text ) ) {
            echo '<span class="tcp_affected_routes"> ' . $affected_text . '</span>';
        }

        // Alert Permalink/URL
        if ( ! empty( $alert_url )  && ! empty( $link_text ) ) {
            echo '<a href="' . $alert_url . '">' . $link_text  . '</a>';
        }
    
        echo '</div>';
    ?>    
</div>