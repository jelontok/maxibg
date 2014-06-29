<?php
/**
 * Plugin Name: Maxi BG
 * Plugin URI: http://jelontok.com/maxibg
 * Description: Add fullscreen slideshow backgrounds to any post or page using MaxImage 2.0.
 * Version: 0.5
 * Author: Jose Erlino M. Lontok
 * Author URI: http://jelontok.com
 * License: 
 * Copyright 2014  Jose Erlino M. Lontok  (email : jelontok@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

 	// REGISTER THE POST TYPE
	add_action( 'init', 'create_post_type' );
	function create_post_type() {
		register_post_type( 'maxibgslideshow',
			array(
				'labels' => array(
					'name' => __( 'Maxi BG' ),
					'singular_name' => __( 'Slideshow Background' ),
					'add_new' => __('Add Slideshow Background' ),
					'add_new_item' => __('Add new Slideshow Background' ),
					'edit_item' => __('Edit Slideshow Background' ),
					'new_item' => __('New Slideshow Background' ),
					'view_item' => __('View Slideshow Background' ),
					'search_items' => __('Search Slideshow Backgrounds' ),
					'not_found' => __('No slidershows found' ),
					'not_found_in_trash' => __('No slideshows found in Trash' )					
				),
			'supports' => array('title'),
			'public' => false,
			'show_ui' => true,
			'has_archive' => true
			)
		);
	}
    
    
    // LOAD SCRIPTS
    
    /**
     * Proper way to enqueue scripts and styles
     */
    
    function plugin_styles_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'cycle', plugins_url('/lib/js/jquery.cycle.all.min.js', __FILE__), '1.0.0', true );                
        wp_enqueue_style( 'maximage', plugins_url('/lib/css/jquery.maximage.min.css', __FILE__), '1.0.0', true );
        wp_enqueue_script( 'maximage', plugins_url('' , __FILE__) . '/lib/js/jquery.maximage.min.js', array(), '1.0.0', true );
    }
    add_action( 'wp_enqueue_scripts', 'plugin_styles_scripts' );    
	
	
    // METABOX STUFF
    add_filter( 'cmb_meta_boxes', 'cmb_maxibg_metaboxes' );
    /**
     * Define the metabox and field configurations.
     *
     * @param  array $meta_boxes
     * @return array
     */
    function cmb_maxibg_metaboxes( array $meta_boxes ) {
    
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_cmb_';
    
        /**
         * Sample metabox to demonstrate each field type included
         */
        $meta_boxes['maxibg_main_metabox'] = array(
            'id'         => 'maxibg_main_metabox',
            'title'      => __( 'Fullscreen Slider Images', 'maxibg' ),
            'pages'      => array( 'maxibgslideshow', ), // Post type
            'context'    => 'normal',
            'priority'   => 'high',
            'show_names' => true, // Show field names on the left
            'fields'     => array(
                array(
                    'name'         => __( 'Slides', 'maxibg' ),
                    'desc'         => __( 'Upload or add multiple images.', 'maxibg' ),
                    'id'           => $prefix . 'slides_file_list',
                    'type'         => 'file_list',
                    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
                ),
                
                array(
                    'name' => __( 'Timeout', 'maxibg' ),
                    'desc' => __( 'milliseconds between slide transitions', 'cmb' ),
                    'id'   => $prefix . 'slide_timeout',
                    'type' => 'text_small',
                    'std'  => '2000'
                ),
                
                array(
                    'name' => __( 'Speed', 'maxibg' ),
                    'desc' => __( 'speed of the transition', 'cmb' ),
                    'id'   => $prefix . 'slide_speed',
                    'type' => 'text_small',
                    'std'  => '800'
                ),                
                
            ),
            
        );
    
        // Add other metaboxes as needed
    
        return $meta_boxes;
    }
    
    add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );
    /**
     * Initialize the metabox class.
     */
    function cmb_initialize_cmb_meta_boxes() {
    
        if ( ! class_exists( 'cmb_Meta_Box' ) )
            require_once 'lib/cmb/init.php';
    
    }
    
    
    // SHORTCODE
    function maxibg_shortcode($atts) {
    
       // EXAMPLE USAGE:
       // [maxibg id="29"]
       
       // Defaults
       extract(shortcode_atts(array(
          "id" => ''
       ), $atts));
    
    
        $my_query = new WP_Query(
            array(
                'post_type' => 'maxibgslideshow',
                'order'=>'ASC',
                'posts_per_page' => 1,
                'p' => $id
            )
        );
        
    
        if($my_query->have_posts()):
            while ( $my_query->have_posts() ) : $my_query->the_post();
                
                $images = get_post_meta( $id, '_cmb_slides_file_list', true ); 
                //print_r($images);            
                                
                foreach($images as $image){                
                    $slideimage .= '<img src="'.$image.'" />'; 
                }
                
                
                $slide_timeout = get_post_meta( $id, '_cmb_slide_timeout', true );
                $slide_speed = get_post_meta( $id, '_cmb_slide_speed', true );                
 
            endwhile;
             
            $slideimages = '<div class="maxibg" style="position:fixed; z-index: -1; top: 0; left: 0">'.$slideimage.'</div>';                    
            
            $slideimages .= '<script type="text/javascript" charset="utf-8">jQuery(window).load(function(){jQuery(".maxibg").maximage({cycleOptions: {fx:"fade",speed: '.$slide_speed.',timeout: '.$slide_timeout.',},onFirstImageLoaded: function(){jQuery("#cycle-loader").hide();jQuery(".maxibg").fadeIn("fast");}});});</script>';
            
            return $slideimages;        
    
        else:
            
        endif; 
       
       wp_reset_query();
       
    }
    add_shortcode("maxibg", "maxibg_shortcode");
    
    
    
    // COLUMNS
    add_action('manage_posts_custom_column', 'posts_custom_id_columns', 5, 2);
    add_filter('manage_edit-maxibgslideshow_columns', 'posts_columns_id', 5);
    add_filter('widget_text', 'do_shortcode');
    function posts_columns_id($defaults){
        $defaults['wps_post_id'] = __('Shortcode');
        return $defaults;
    }
    function posts_custom_id_columns($column_name, $id){
            if($column_name === 'wps_post_id'){
                    echo '[maxibg id="'.$id.'"]';
        }
    }
    
    
    // UPDATE MESSAGES
    function custom_post_type_update_messages( $messages )
    {
            global $post;
    
            $post_ID = $post->ID;
            $post_type = get_post_type( $post_ID );
    
            $obj = get_post_type_object( $post_type );
            $singular = $obj->labels->singular_name;
    
            $messages[$post_type] = array(
                    0 => '', // Unused. Messages start at index 1.
                    1  => sprintf( __( 'Slideshow background updated. Shortcode is [maxibg id="'.$post_ID.'"]', 'maxibg' ), $post->post_name),
                    2 => __( 'Custom field updated.', 'maxibg' ),
                    3 => __( 'Custom field deleted.', 'maxibg' ),
                    4 => sprintf( __( '%s updated.', 'maxibg' ), esc_attr( $singular ) ),
                    5 => isset( $_GET['revision']) ? sprintf( __('%2$s restored to revision from %1$s', 'maxibg' ), wp_post_revision_title( (int) $_GET['revision'], false ), esc_attr( $singular ) ) : false,
                    6 => sprintf( __( 'Slideshow background saved. Shortcode is [maxibg id="'.$post_ID.'"]', 'maxibg' ), $post->post_name),
                    7 => sprintf( __( 'saved. Shortcode is [maxibg id="'.$post_ID.'"]', 'maxibg' ), $post->post_name),
                    8 => sprintf( __( '%s submitted. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) ),
                    9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a href="%s" target="_blank">Preview %s</a>' ), $singular, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), strtolower( $singular ) ),
                    10 => sprintf( __( '%s draft updated. <a href="%s" target="_blank">Preview %s</a>'), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) )
            );
    
            return $messages;
    }
    add_filter( 'post_updated_messages', 'custom_post_type_update_messages' );    
