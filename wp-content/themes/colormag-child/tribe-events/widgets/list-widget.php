<?php
/**
 * Events List Widget Template
 * This is the template for the output of the events list widget.
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is needed.
 *
 * This view contains the filters required to create an effective events list widget view.
 *
 * You can recreate an ENTIRELY new events list widget view by doing a template override,
 * and placing a list-widget.php file in a tribe-events/widgets/ directory
 * within your theme directory, which will override the /views/widgets/list-widget.php.
 *
 * You can use any or all filters included in this file or create your own filters in
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @version 4.4
 * @return string
 *
 * @package TribeEventsCalendar
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_plural = tribe_get_event_label_plural();
$events_label_plural_lowercase = tribe_get_event_label_plural_lowercase();

$posts = tribe_get_list_widget_events();

// Check if any event posts are found.
if ( $posts ) : ?>

	<div class="clearfix">
		<?php
		// Setup the post data for each event.
		foreach ( $posts as $post ) :
			setup_postdata( $post );
			?>
			<div class="single-article clearfix">
			<?php
			if ( get_post_thumbnail_id( $post ) ) {
				/**
				 * Fire an action before the list widget featured image
				 */
				do_action( 'tribe_events_list_widget_before_the_event_image' );

				/**
				 * Allow the default post thumbnail size to be filtered
				 *
				 * @param $size
				 */
				$thumbnail_size = apply_filters( 'tribe_events_list_widget_thumbnail_size', 'thumbnail' );
			?>
				<figure style="float:left; margin: 10px">
					<?php the_post_thumbnail( $thumbnail_size ); ?>
				</figure>
			<?php
				/**
				 * Fire an action after the list widget featured image
				 */
				do_action( 'tribe_events_list_widget_after_the_event_image' );
			}
			?>

			<?php do_action( 'tribe_events_list_widget_before_the_event_title' ); ?>

			<div class="article-content">
				<!-- Event Title -->
				<h3 class="entry-title article-content" style="font-size: 18px">
					<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h3>

				<?php do_action( 'tribe_events_list_widget_after_the_event_title' ); ?>
                                <!-- Event Venue -->
                                <!-- Venue Display Info -->

                                <div class="tribe-events-venue-details" style="font-size: 12px">
                                        <?php echo tribe_get_venue() ?>
                                </div> <!-- .tribe-events-venue-details -->

				<!-- Event Time -->

				<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>

				<div class="tribe-event-duration" style="font-size: 12px">
 					<?php echo tribe_get_start_time(null, "M j, Y @ G:i" ) ?>
				</div>
			
				<?php do_action( 'tribe_events_list_widget_after_the_meta' ) ?>
			</div>
			</div>
		<?php
		endforeach;
		?>
	</div><!-- .tribe-list-widget -->

	<p class="tribe-events-widget-link">
		<a href="<?php echo esc_url( tribe_get_events_link() ); ?>" rel="bookmark"><?php printf( esc_html__( 'View All %s', 'the-events-calendar' ), $events_label_plural ); ?></a>
	</p>

<?php
// No events were found.
else : ?>
	<p><?php printf( esc_html__( 'There are no upcoming %s at this time.', 'the-events-calendar' ), $events_label_plural_lowercase ); ?></p>
<?php
endif;
