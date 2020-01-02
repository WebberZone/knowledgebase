<?php
/**
 * WZ Knowledge Base Breadcrumb Widget class.
 *
 * @package WZKB
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Create a WordPress Widget for WZ Knowledge Base.
 *
 * @since 1.9.0
 *
 * @extends WP_Widget
 */
class WZKB_Breadcrumb_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_wzkb_breadcrumb',
			__( 'WZ Knowledgebase Breadcrumb', 'knowledgebase' ),
			array(
				'description'                 => __( 'Display the breadcrumb when viewing a knowledge base article or category', 'knowledgebase' ),
				'customize_selective_refresh' => true,
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
		$title     = isset( $instance['title'] ) ? $instance['title'] : '';
		$separator = isset( $instance['separator'] ) ? $instance['separator'] : '';

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_html_e( 'Title', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'separator' ) ); ?>">
			<?php esc_html_e( 'Separator', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'separator' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'separator' ) ); ?>" type="text" value="<?php echo esc_attr( $separator ); ?>" />
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
	} //ending form creation

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
		$instance              = $old_instance;
		$instance              = array();
		$instance['title']     = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['separator'] = ( ! empty( $new_instance['separator'] ) ) ? $new_instance['separator'] : '';

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
	} //ending update

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;

		// Return if not a WZKB post type archive or single page.
		if ( ! is_post_type_archive( 'wz_knowledgebase' ) && ! is_singular( 'wz_knowledgebase' ) && ! is_tax( 'wzkb_category' ) && ! is_tax( 'wzkb_tag' ) ) {
			return;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';

		/**
		 * Filters the widget title.
		 *
		 * @since 1.9.0
		 *
		 * @param string $title    The widget title. Default 'Pages'.
		 * @param array  $instance Array of settings for the current widget.
		 * @param mixed  $id_base  The widget ID.
		 */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$separator = ! empty( $instance['separator'] ) ? $instance['separator'] : ' &raquo; ';

		$arguments = array(
			'is_widget'   => 1,
			'instance_id' => $this->number,
			'separator'   => $separator,
		);

		/**
		 * Filters arguments passed to wzkb_get_breadcrumb for the widget.
		 *
		 * @since 1.9.0
		 *
		 * @param array $arguments WZ Knowledge Base widget options array.
		 * @param array $args      Widget arguments.
		 * @param array $instance  Saved values from database.
		 * @param mixed $id_base   The widget ID.
		 * @param mixed $number    Unique widget number.
		 */
		$arguments = apply_filters( 'wzkb_widget_options', $arguments, $args, $instance, $this->id_base, $this->number );

		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];
		$output .= wzkb_get_breadcrumb( $arguments );

		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	} // Ending function widget.
}


/**
 * Initialise the widget.
 *
 * @since 1.9.0
 */
function register_wzkb_widgets() {
	register_widget( 'WZKB_Breadcrumb_Widget' );
}
add_action( 'widgets_init', 'register_wzkb_widgets' );
