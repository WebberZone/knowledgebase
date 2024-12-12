<?php
/**
 * WZ Knowledge Base Articles Widget class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Widgets;

use WebberZone\Knowledge_Base\Frontend\Display;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Create a WordPress Articles Widget for WZ Knowledge Base.
 *
 * @since 2.3.0
 */
class Articles_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_wzkb_articles',
			__( 'Knowledge Base Articles', 'knowledgebase' ),
			array(
				'classname'                   => 'widget_wzkb_articles',
				'description'                 => __( 'Display the list of Knowledge Base articles for a section', 'knowledgebase' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
			)
		);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title        = isset( $instance['title'] ) ? $instance['title'] : '';
		$term_id      = isset( $instance['term_id'] ) ? $instance['term_id'] : '';
		$limit        = isset( $instance['limit'] ) ? $instance['limit'] : '';
		$show_excerpt = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : '';

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_html_e( 'Title', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>">
			<?php esc_html_e( 'Term ID (enter a number)', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'term_id' ) ); ?>" type="text" value="<?php echo esc_attr( $term_id ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
			<?php esc_html_e( 'No. of posts', 'contextual-related-posts' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>">
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" type="checkbox" <?php checked( true, $show_excerpt, true ); ?> /> <?php esc_html_e( ' Show excerpt?', 'contextual-related-posts' ); ?>
			</label>
		</p>

		<?php
			/**
			 * Fires after WZKB Knowledge Base widget options.
			 *
			 * @since 1.9.0
			 *
			 * @param array $instance Widget options array.
			 * @param mixed $id_base  The widget ID.
			 * @param mixed $number   Unique widget number.
			 */
			do_action( 'wzkb_widget_options_after', $instance, $this->id_base, $this->number );
		?>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param  array $new_instance Values just sent to be saved.
	 * @param  array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['term_id']      = ( ! empty( $new_instance['term_id'] ) ) ? intval( $new_instance['term_id'] ) : '';
		$instance['limit']        = ( ! empty( $new_instance['limit'] ) ) ? absint( $new_instance['limit'] ) : '';
		$instance['show_excerpt'] = isset( $new_instance['show_excerpt'] ) ? true : false;

		/**
		 * Filters Update widget options array.
		 *
		 * @since 1.9.0
		 *
		 * @param array $instance     Widget options array
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 * @param mixed $id_base      The widget ID.
		 * @param mixed $number       Unique widget number.
		 */
		return apply_filters( 'wzkb_widget_options_update', $instance, $new_instance, $old_instance, $this->id_base, $this->number );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$default_title = __( 'Knowledge Base Articles', 'knowledgebase' );
		$title         = ( ! empty( $instance['title'] ) ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$limit = (int) ( ! empty( $instance['limit'] ) ? $instance['limit'] : wzkb_get_option( 'limit', 5 ) );

		$show_excerpt = isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : false;

		if ( empty( $instance['term_id'] ) ) {
			return;
		}

		$term = get_term( $instance['term_id'], 'wzkb_category' );

		if ( empty( $term ) || is_wp_error( $term ) ) {
			return;
		}

		$list_of_posts = Display::get_posts_by_term(
			$term,
			0,
			array(
				'show_excerpt' => $show_excerpt,
				'limit'        => $limit,
			)
		);

		if ( empty( $list_of_posts ) ) {
			return;
		}

		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$output .= $list_of_posts;

		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
