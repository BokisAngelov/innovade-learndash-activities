<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$activity_type = get_post_meta(get_the_ID(), 'inn-ld-activity-type', true);
$document_path = '';
$link_path = '';
$video_path = '';
$text_content = '';

if ($activity_type != 'Please select'){
    // $activity_type = get_post_meta(get_the_ID(), 'inn-ld-activity-type', true);

    if ($activity_type == 'Downloadable file' && get_post_meta(get_the_ID(), 'inn-ld-document-path', true))
        $document_path = get_post_meta(get_the_ID(), 'inn-ld-document-path', true);
    else if ($activity_type == 'Link' && get_post_meta(get_the_ID(), 'inn-ld-external-link', true))
        $link_path = get_post_meta(get_the_ID(), 'inn-ld-external-link', true);
    else if ($activity_type == 'Youtube Video' && get_post_meta(get_the_ID(), 'inn-ld-video-link', true))
        $video_path = get_post_meta(get_the_ID(), 'inn-ld-video-link', true);
    else if ($activity_type == 'Plain text' && get_post_meta(get_the_ID(), 'inn-ld-plain-text', true))
        $text_content = get_post_meta(get_the_ID(), 'inn-ld-plain-text', true);

}

$completion_criteria = get_post_meta($post->ID, 'completion-criteria', true);

if ($completion_criteria == 'On click'){
    $innovade_btn_class = ' on-click-complete';
    
}else    
    $innovade_btn_class = '';

?>
<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">
	<?php
	/**
	 * Fires before the topic
	 *
	 * @since 3.0.0
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-topic-before', get_the_ID(), $course_id, $user_id );

	if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
		$shown_content_key = 'learndash-shortcode-wrap-ld_infobar-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
		if ( false === strstr( $content, $shown_content_key ) ) {
			$shortcode_out = do_shortcode( '[ld_infobar course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
			if ( ! empty( $shortcode_out ) ) {
				echo esc_html($shortcode_out);
			}
		}
	} else {
		learndash_get_template_part(
			'modules/infobar.php',
			array(
				'context'   => 'topic',
				'course_id' => $course_id,
				'user_id'   => $user_id,
			),
			true
		);
	}

	/**
	 * If the user needs to complete the previous lesson AND topic display an alert
	 */

	$sub_context = '';
	if ( ( $lesson_progression_enabled ) && ( ! learndash_user_progress_is_step_complete( $user_id, $course_id, $post->ID ) ) ) {
		$previous_item = learndash_get_previous( $post );
		if ( ( ! $previous_topic_completed ) || ( empty( $previous_item ) ) ) {
			if ( 'on' === learndash_get_setting( $lesson_post->ID, 'lesson_video_enabled' ) ) {
				if ( ! empty( learndash_get_setting( $lesson_post->ID, 'lesson_video_url' ) ) ) {
					if ( 'BEFORE' === learndash_get_setting( $lesson_post->ID, 'lesson_video_shown' ) ) {
						if ( ! learndash_video_complete_for_step( $lesson_post->ID, $course_id, $user_id ) ) {
							$sub_context = 'video_progression';
						}
					}
				}
			}
		}
	}

	if ( ( $lesson_progression_enabled ) && ( ! empty( $sub_context ) || ! $previous_topic_completed || ! $previous_lesson_completed ) && ! learndash_can_user_bypass() ) {

		if ( ( ! learndash_is_sample( $post ) ) /* || ( learndash_is_sample( $post ) && true === (bool) $has_access ) */ ) {

			if ( 'video_progression' === $sub_context ) {
				$previous_item = $lesson_post;
			} else {
				$previous_item_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $post->ID );
				if ( ( ! empty( $previous_item_id ) ) && ( $previous_item_id !== $post->ID ) ) {
					$previous_item = get_post( $previous_item_id );
				}
			}

			if ( ( isset( $previous_item ) ) && ( ! empty( $previous_item ) ) ) {
				$show_content = false;
				learndash_get_template_part(
					'modules/messages/lesson-progression.php',
					array(
						'previous_item' => $previous_item,
						'course_id'     => $course_id,
						'context'       => 'topic',
						'sub_context'   => $sub_context,
					),
					true
				);
			}
		}
	}

	if ( $show_content ) :

		learndash_get_template_part(
			'modules/tabs.php',
			array(
				'course_id' => $course_id,
				'post_id'   => get_the_ID(),
				'user_id'   => $user_id,
				'content'   => $content,
				'materials' => $materials,
				'context'   => 'topic',
			),
			true
		);


        /**
         *  Innovade Learndash activities layout
         * */ 
        ?>
        <div class="ld-content-actions">
            <?php    if ($document_path){ // Downloadable files?>
                <a class="ld-button<?php echo esc_attr($innovade_btn_class); ?>" href="<?php echo esc_url($document_path); ?>" target="_blank" data-id="<?php echo esc_attr(get_the_ID()); ?>"><?php _e('Download document'); ?></a>
            <?php } ?>
			<?php    if ($link_path){ // External link

						$url = parse_url($link_path);
						if (empty($url['scheme'])) // Check if link starts with http or https
							$link_path = 'https://' . $link_path;
			?>
                <a class="ld-button<?php echo esc_attr($innovade_btn_class); ?>" href="<?php echo esc_url($link_path); ?>" target="_blank" data-id="<?php echo esc_attr(get_the_ID()); ?>"><?php _e('View link'); ?></a>
            <?php } ?>
			<?php    if ($text_content){ // Text content ?>
				<p>
					<?php echo esc_html($text_content); ?>
				</p>
            <?php } ?>
			<?php    if ($video_path){ // Youtube video 
				$videoUrl = substr($video_path, strrpos($video_path, '=') + 1);	
				?>
				<iframe class="ld-iframe" width="560" height="315" src="https://www.youtube.com/embed/<?php echo esc_url($videoUrl); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen>

				</iframe>
            <?php } ?>
        </div>
        <?php
        

		if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
			$shown_content_key = 'learndash-shortcode-wrap-course_content-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
			if ( false === strstr( $content, $shown_content_key ) ) {
				$shortcode_out = do_shortcode( '[course_content course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
				if ( ! empty( $shortcode_out ) ) {
					echo esc_html($shortcode_out);
				}
			}
		} else {

			/**
			 * Display Lesson Assignments
			 */
			if ( learndash_lesson_hasassignments( $post ) && ! empty( $user_id ) ) : // cspell:disable-line.
				$bypass_course_limits_admin_users = learndash_can_user_bypass( $user_id, 'learndash_lesson_assignment' );
				$course_children_steps_completed  = learndash_user_is_course_children_progress_complete( $user_id, $course_id, $post->ID );
				if ( ( learndash_lesson_progression_enabled() && $course_children_steps_completed ) || ! learndash_lesson_progression_enabled() || $bypass_course_limits_admin_users ) :

					/**
					 * Fires before the lesson assignment.
					 *
					 * @since 3.0.0
					 *
					 * @param int $post_id   Post ID.
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-lesson-assignment-before', get_the_ID(), $course_id, $user_id );

					learndash_get_template_part(
						'assignment/listing.php',
						array(
							'user_id'          => $user_id,
							'course_step_post' => $post,
							'course_id'        => $course_id,
							'context'          => 'topic',
						),
						true
					);

					/**
					 * Fires after the lesson assignment.
					 *
					 * @since 3.0.0
					 *
					 * @param int $post_id   Post ID.
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-lesson-assignment-after', get_the_ID(), $course_id, $user_id );

				endif;
			endif;

			if ( ! empty( $quizzes ) ) :

				learndash_get_template_part(
					'quiz/listing.php',
					array(
						'user_id'   => $user_id,
						'course_id' => $course_id,
						'lesson_id' => $lesson_id,
						'quizzes'   => $quizzes,
						'context'   => 'topic',
					),
					true
				);

			endif;
		}
	endif; // $show_content

	if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
		$shown_content_key = 'learndash-shortcode-wrap-ld_navigation-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
		if ( false === strstr( $content, $shown_content_key ) ) {
			$shortcode_out = do_shortcode( '[ld_navigation course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
			if ( ! empty( $shortcode_out ) ) {
				echo esc_html($shortcode_out);
			}
		}
	} else {

		$can_complete = false;

		if ( $all_quizzes_completed && $logged_in && ! empty( $course_id ) ) :
			/** This filter is documented in themes/ld40/templates/lesson.php */
			$can_complete = apply_filters( 'learndash-lesson-can-complete', true, get_the_ID(), $course_id, $user_id );
		endif;

		learndash_get_template_part(
			'modules/course-steps.php',
			array(
				'course_id'             => $course_id,
				'course_step_post'      => $post,
				'all_quizzes_completed' => $all_quizzes_completed,
				'user_id'               => $user_id,
				'course_settings'       => isset( $course_settings ) ? $course_settings : array(),
				'context'               => 'topic',
				'can_complete'          => $can_complete,
			),
			true
		);
	}

	/**
	 * Fires after the topic.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id   Current Post ID.
	 * @param int $course_id Course ID.
	 * @param int $user_id   User ID.
	 */
	do_action( 'learndash-topic-after', get_the_ID(), $course_id, $user_id );
	learndash_load_login_modal_html();
	?>
</div> <!--/.learndash-wrapper-->
