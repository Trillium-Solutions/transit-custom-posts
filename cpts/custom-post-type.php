<?php
/*
 * Parent class for custom post types
 * Borrows heavily from https://code.tutsplus.com/articles/custom-post-type-helper-class--wp-25104
 */

class TCP_CustomPostType {
    protected $slug;
    protected $args;
    protected $labels;
    protected $fields;
    
    /* Create new custom post type with optional $args and $labels */
    protected function __construct( $name, $args = array(), $labels = array() ) {
        $this->slug = $this->slugify( $name );
        $this->args = $args;
        $this->labels = $labels;
        $this->fields = array();
        
        if( ! post_type_exists( $this->slug ) ) {
            add_action( 'init', array(&$this, 'register_post_type' ) );
        }
        $this->save();
		
		add_action( 'widgets_init', array(&$this, 'register_widgets') );
    }
    
    /* Register a new custom post type */
    public function register_post_type() {
        $name = ucwords( str_replace( '_', ' ', $this->slug ) );
        $plural = $name . 's';
        
        /* Merge given array of labels and args with default settings */
        $labels = array_merge(array(
            'name'              => _x( $plural, 'post type general name'),
            'singular_name'     => _x( $name, 'post type singular name'),
            'add_new'           => _x( 'Add New', $name ),
            'add_new_item'      => __( 'Add New ' . $name ),
            'edit_item'         => __( 'Edit ' . $name ),
            'new_item'          => __( 'New ' . $name ),
            'all_items'         => __( 'All ' . $plural ),
            'view_item'         => __( 'View ' . $name ),
            'search_items'      => __( 'Search ' . $plural),
            'not_found'         => __( 'No ' . $plural . ' found.' ),
            'not_found_in_trash'=> __( 'No ' . $plural . ' found in trash.' ),
            'parent_item_colon' => '',
            'menu_name'         => $plural,
        ), $this->labels);

        $args = array_merge(array(
            'label'             => $plural,
            'labels'            => $labels,
            'public'            => true,
            'supports'          => array_key_exists( 'supports', $this->args ) ? $this->args['supports'] :
             array( 'title', 'editor' ),
            '_builtin'          => false,
			
        ), $this->args);
        
        register_post_type( $this->slug, $args ); 
    }
    
    protected function add_taxonomy( $name, $args = array(), $labels = array() ) {
        if( empty( $name ) ) {
            // TODO add an error msg? 
            // Fail silently
            return;
        }
        // We need to know the post type name, so the new taxonomy can be attached to it.
        $post_type_name = $this->slug;

        // Taxonomy properties
        $taxonomy_name      = $this->slugify( $name );
        $taxonomy_labels    = $labels;
        $taxonomy_args      = $args;

        if( ! taxonomy_exists( $taxonomy_name ) ) {
            $plural = $name . 's';

            // Overwrite default labels with provided
            $labels = array_merge(array(
                'name'                  => _x( $plural, 'taxonomy general name' ),
                'singular_name'         => _x( $name, 'taxonomy singular name' ),
                'search_items'          => __( 'Search ' . $plural ),
                'all_items'             => __( 'All ' . $plural ),
                'parent_item'           => __( 'Parent ' . $name ),
                'parent_item_colon'     => __( 'Parent ' . $name . ':' ),
                'edit_item'             => __( 'Edit ' . $name ),
                'update_item'           => __( 'Update ' . $name ),
                'add_new_item'          => __( 'Add New ' . $name ),
                'new_item_name'         => __( 'New ' . $name . ' Name' ),
                'menu_name'             => __( $name ),
                ),
                // Given labels
                $taxonomy_labels
            );

            // Default arguments, overwritten with the given arguments
            $args = array_merge(array(
                'label'                 => $plural,
                'labels'                => $labels,
                'public'                => true,
                'show_ui'               => true,
                'show_in_nav_menus'     => true,
                '_builtin'              => false,
                ),
                // Given arguments
                $taxonomy_args
            );

            // Add the taxonomy to the post type
            add_action( 'init', function() use( $taxonomy_name, $post_type_name, $args ) {
                register_taxonomy( $taxonomy_name, $post_type_name, $args );
            });
        }
        else {
            /* The taxonomy already exists. Attach the existing taxonomy 
             * to the object type (post type) */
            add_action( 'init', function() use( $taxonomy_name, $post_type_name ) {
                register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
            });
        }
    }
    
    /* Add a metabox to the custom post type page */
    protected function add_meta_box( $title, $fields = array(), $context = 'normal', $priority = 'default') {
        if( empty( $title ) ) {
            //fail silently
            return;
        }
        // Get post slug
        $post_type_name = $this->slug;

        // Meta variables
        $box_id         = $this->slugify( $title );
        $box_title      = $title;
        $box_context    = $context;
        $box_priority   = $priority;
        
        foreach($fields as $label => $args) {
            $fields[$label] = array_merge(array(
                'placeholder'   => '',
                'type'          => 'text',
                'classes'       => 'widefat',
                'helper'        => '',
				'default'		=> '',
				'options'		=> false,
            ),
            $args
            );
        }

        // Save fields as part of post-type
        $this->fields[$title] = $fields;
        
        add_action( 'admin_init', function() use( $box_id, $box_title, $post_type_name, $box_context, $box_priority, $fields ) {
            add_meta_box( $box_id, $box_title, array(&$this, 'custom_metabox' ), $post_type_name, $box_context, $box_priority, array( $fields ));
        });
    }
    
    // Display custom metabox on custom post page
    public function custom_metabox( $post, $data ) {
        global $post;
        
        wp_nonce_field( plugin_basename( __FILE__ ), $this->slug );
        
        // All field arguments
        $custom_fields = $data['args'][0];
        
        if( empty( $custom_fields ) ) {
            //fail silently
            return;
        }    
        
        foreach( $custom_fields as $label => $args ) {
            $field_id_name  = $this->slugify( $label );
            $field_type = $args['type'];
            $field_val = get_post_meta($post->ID, $field_id_name, true);
			if (! $field_val ) {
				$field_val = $args['default'];
			}
            
            // Choose display based on field type
            switch( $field_type ) {
                // Basic text boxes
                case 'text':
                case 'number':
                case 'email':
				case 'date':
                    printf( '<label for="%1$s">%2$s</label>', $field_id_name, $label );
                    printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="%5$s"/>', $field_id_name, $field_type, $args['placeholder'], esc_attr($field_val), $args['classes'] );
                    break;
                    
                // Text areas
                case 'textarea':
                    printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $field_id_name, $args['placeholder'], esc_textarea($field_val) );
                    break;
                    
                // Select boxes, require options array to display    
                case 'select':
                case 'multiselect':
                    if ( empty( $args['options']) || ! is_array( $args['options']) ) {
                        // Fail silently
                        break;
                    }
                    $attributes = '';
                    $options_markup = '';
                    foreach( $args['options'] as $key => $option_label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $field_val, $key, false ), $option_label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s" id="%1$s" %2$s>%3$s</select>', $field_id_name, $attributes, $options_markup );
                    break;
					
				case 'multiple_checkbox':
					if (! empty ($args['options']) && is_array( $args['options'] ) ) {
						$options_markup = '';
						$iterator = 0;
						foreach( $args['options'] as $key => $option_label){
							$iterator++;
							$options_markup .= sprintf( '<label for="%1$s_%5$s"><input id="%1$s_%5$s" name="%1$s[%2$s]" type="checkbox" value="%2$s" %3$s> %4$s</label><br/>', $field_id_name, $key, in_array($key, $field_val) ? 'checked' : '', $option_label, $iterator);
						}
						printf( '<fieldset>%s</fieldset>', $options_markup );
					}
					break;
                    
                // Radio and checkbox
                case 'radio':
                case 'checkbox':
                    if ( empty( $args['options']) || ! is_array( $args['options']) ) {
                        // Fail silently
                        break;
                    }
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $args['options'] as $key => $option_label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $field_id_name, $args['type'], $key, checked( $value, $key, false ), $option_label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                    break;
					
				case 'file':
					printf( '<label for="%1$s">%2$s</label>', $field_id_name, $label );
					printf( '<input name="%1$s" id="%1$s" type="%2$s"  value="%3$s" class="%4$s"/>', $field_id_name, $field_type, esc_attr($field_val), $args['classes'] );
					break;
            }
            if( $helper = $args['helper'] ){
                printf( '<p class="description">%s</p>', $helper ); 
            }
        } // End foreach
    }        
    
    protected function save() {
        $post_type_name = $this->slug;
        $obj_copy = &$this;
        add_action( 'save_post', function($post_id, $post) use( $post_type_name, $obj_copy ) {
            /* Error checking--prevent intentionally or accidentally saving
             * custom post type meta-data
             */
            // If we haven't posted from form with nonce set, do nothing
            if ( !isset( $_POST[$post_type_name] ) ) {
                return $post_id;
            }
            // Don't include form fields in wordpress autosave
            if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                return $post_id;
            }
            // If nonce field doesn't originate from this post
            if ( ! wp_verify_nonce( $_POST[$post_type_name], plugin_basename(__FILE__) ) ) {
                return $post_id;
            }
            // Hopefully they aren't in the edit post screen to begin with, but prevent forged
            // forms from users without appropriate capabilities
            if ( !current_user_can( 'edit_post', $post->ID ) ) {
                return $post_id;
            }
            $post_type_fields = $obj_copy->fields;
            // Loop through all meta-boxes and all meta-box fields
            foreach( $post_type_fields as $title => $fields ) {
                foreach( $fields as $label => $args ) {
                    $field_id_name  = $this->slugify( $label );
					if ( $args['type'] == "file" ) {
						var_dump($_FILES);
					} else {
	                    $old_value = get_post_meta($post_id, $field_id_name, true);
	                    $new_value = $_POST[$field_id_name];
	                    // If the value has been created or changed
	                    if ($new_value && $new_value != $old_value) {
	                        update_post_meta($post_id, $field_id_name, $new_value);
	                    } elseif ('' == $new_value && $old_value) {
	                        // If value has been deleted
	                        delete_post_meta($post_id, $field_id_name, $old_value);
	                    }
					}
                }
            }
        }, 1, 2);
    }
	
	public function register_widgets() {
		// Override in child in order to add widgets
		// Do nothing.
	}
	
	protected function slugify( $name_str ) {
	    return strtolower( str_replace( ' ', '_', $name_str ) );
	}
}