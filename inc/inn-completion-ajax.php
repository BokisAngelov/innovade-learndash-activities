<?php 
/**
 *  Innovade Learndash Activities
 * 
 *  AJAX PHP Function to complete topic on click (applied for PDF/DOCX/PPT/LINK)
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function innovade_compelete_topic() {

    $topic_id = sanitize_text_field($_GET['topic_id']);

    learndash_process_mark_complete(get_current_user_id(), $topic_id);

    exit();
}
add_action( 'wp_ajax_innovade_complete_topic', 'innovade_compelete_topic' );
add_action( 'wp_ajax_nopriv_innovade_complete_topic', 'innovade_compelete_topic' );