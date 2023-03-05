<?php
/*
 * Plugin Name: Innovade Learndash Activities
 * Description: Extend Learndash topic types and completion criteria
 * Version: 1.0.0
 * Requires at least: 4.8
 * Tested up to: 6.1
 * Author: Innovade LI
 * Author URI: https://innovade.eu
 * Text Domain: innovade-learndash-activites
 */

include( plugin_dir_path( __FILE__ ) . 'inc/inn-admin-fields.php');
include( plugin_dir_path( __FILE__ ) . 'inc/inn-completion-ajax.php');

/**
 *   Check if Learndash is activated
 * 
 */
function innovade_require_learndash(){
        
     if( !is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) { ?>
          <div class="notice notice-error" >
               <p> Please activate Learndash LMS plugin. </p>
          </div>
          <?php
               @trigger_error(__('<p> Please activate Learndash LMS plugin. </p>', 'cln'), E_USER_ERROR);
     }
     
}
register_activation_hook(__FILE__, 'innovade_require_learndash');

/**
 * Override topic template
 * 
 */
function innovade_replace_learndash_templates( $filepath, $name, $args, $echo, $return_file_path){
     
     if ( 'topic' == $name ){
          if (str_contains($filepath, 'legacy'))
               $filepath = plugin_dir_path(__FILE__ ) . 'templates/legacy/topic.php';
          else
               $filepath = plugin_dir_path(__FILE__ ) . 'templates/topic.php';
     }     
     return $filepath;

}
add_filter('learndash_template','innovade_replace_learndash_templates', 90, 5);

/**
 * Override Learndash mark complete button
 * 
 */
function innovade_hide_manual_completion( $return, $post ) {

     $activity_type = get_post_meta($post->ID, 'inn-ld-activity-type', true);

     if ($activity_type == 'Downloadable file' || $activity_type == 'Link'){
          $completion_criteria = get_post_meta($post->ID, 'completion-criteria', true);

          // Hide the default button to mark topic as complete
          if ($completion_criteria == 'On click')
               $return = '';
     }          
     
     return $return;     
     
}
add_filter('learndash_mark_complete', 'innovade_hide_manual_completion', 10, 2);

/**
 * Include js file for AJAX calls
 * 
 */
function innovade_ajax_enqueue() {

     wp_enqueue_script( 'ajax-script', plugin_dir_url(__FILE__ ) . 'inc/js/inn-custom-js.js', array('jquery') );
     wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
 
 }
 add_action( 'wp_enqueue_scripts', 'innovade_ajax_enqueue' );