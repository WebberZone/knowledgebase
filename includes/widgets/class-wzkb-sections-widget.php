<?php
/**
 * WZ Knowledge Base Sections Widget class.
 *
 * @package WZKB
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Create a WordPress Sections Widget for WZ Knowledge Base.
 *
 * @since 1.9.0
 *
 * @extends WP_Widget
 */
class WZKB_Sections_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_wzkb_sections',
			__( 'WZKB Sections', 'knowledgebase' ),
			array(
				'description'                 => __( 'Display the list of sections when browsing a knowledge base page', 'knowledgebase' ),
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
		$title          = isset( $instance['title'] ) ? $instance['title'] : '';
		$term_id        = isset( $instance['term_id'] ) ? $instance['term_id'] : '';
		$depth          = isset( $instance['depth'] ) ? $instance['depth'] : '';
		$before_li_item = isset( $instance['before_li_item'] ) ? $instance['before_li_item'] : '';
		$after_li_item  = isset( $instance['after_li_item'] ) ? $instance['after_li_item'] : '';

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
			<label for="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>">
			<?php esc_html_e( 'Depth', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'depth' ) ); ?>" type="text" value="<?php echo esc_attr( $depth ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'before_li_item' ) ); ?>">
			<?php esc_html_e( 'Before list item', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_li_item' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_li_item' ) ); ?>" type="text" value="<?php echo esc_attr( $before_li_item ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_li_item' ) ); ?>">
			<?php esc_html_e( 'After list item', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_li_item' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_li_item' ) ); ?>" type="text" value="<?php echo esc_attr( $after_li_item ); ?>" />
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
		$instance                   = $old_instance;
		$instance                   = array();
		$instance['title']          = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['term_id']        = ( ! empty( $new_instance['term_id'] ) ) ? intval( $new_instance['term_id'] ) : '';
		$instance['depth']          = ( ! empty( $new_instance['depth'] ) ) ? intval( $new_instance['depth'] ) : '';
		$instance['before_li_item'] = ( ! empty( $new_instance['before_li_item'] ) ) ? $new_instance['before_li_item'] : '';
		$instance['after_li_item']  = ( ! empty( $new_instance['after_li_item'] ) ) ? $new_instance['after_li_item'] : '';

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

		$term_id = ! empty( $instance['term_id'] ) ? $instance['term_id'] : 0;

		$arguments = array(
			'is_widget'      => 1,
			'instance_id'    => $this->number,
			'depth'          => ( ! empty( $instance['depth'] ) ) ? $instance['depth'] : 0,
			'before_li_item' => ( ! empty( $instance['before_li_item'] ) ) ? $instance['before_li_item'] : '',
			'after_li_item'  => ( ! empty( $instance['after_li_item'] ) ) ? $instance['after_li_item'] : '',
		);

		/**
		 * Filters arguments passed to wzkb_categories_list for the widget.
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

		$output .= wzkb_categories_list( $term_id, 0, $arguments );

		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} // Ending function widget.
}

/**
 * Get a hierarchical list of WZ Knowledge Base sections.
 *
 * @param  int   $term_id Term ID.
 * @param  int   $level   Level of the loop.
 * @param  array $args    Array or arguments.
 * @return string HTML output with the categories.
 */
function wzkb_categories_list( $term_id, $level = 0, $args = array() ) {

	$defaults = array(
		'depth'          => 0,  // Depth of nesting.
		'before_li_item' => '', // Before list item - just after <li>.
		'after_li_item'  => '', // Before list item - just before </li>.
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Get Knowledge Base Sections.
	$sections = get_terms(
		'wzkb_category',
		array(
			'orderby'    => 'slug',
			'hide_empty' => wzkb_get_option( 'show_empty_sections' ) ? 0 : 1,
			'parent'     => $term_id,
		)
	);

	$output = '';

	if ( ! empty( $sections ) && ! is_wp_error( $sections ) ) {

		$output .= '<ul class="wzkb_terms_widget wzkb_term_' . $term_id . ' wzkb_ul_level_' . $level . '">';

		++$level;

		foreach ( $sections as $term ) {

			$term_link = get_term_link( $term );

			// If there was an error, continue to the next term.
			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$output .= '<li class="wzkb_cat_' . $term->term_id . '">' . $args['before_li_item'];
			$output .= '<a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $term->name ) . '" >' . $term->name . '</a>';
			$output .= wzkb_categories_list( $term->term_id, $level, $args );
			$output .= $args['after_li_item'] . '</li>';

			// Exit the loop if we are at the depth.
			if ( 0 < $args['depth'] && $level >= $args['depth'] ) {
				break;
			}
		}
		$output .= '</ul>';
	}

	return $output;
}
