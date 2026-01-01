<?php
/**
 * WZ Knowledge Base Sections Widget class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Widgets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Create a WordPress Sections Widget for WZ Knowledge Base.
 *
 * @since 2.3.0
 */
class Sections_Widget extends \WP_Widget {

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
		$title          = isset( $instance['title'] ) ? $instance['title'] : '';
		$term_id        = isset( $instance['term_id'] ) ? $instance['term_id'] : '';
		$product_id     = isset( $instance['product_id'] ) ? (int) $instance['product_id'] : 0;
		$products       = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
			)
		);
		$depth          = isset( $instance['depth'] ) ? $instance['depth'] : -1;
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
			<?php esc_html_e( 'Section ID (enter a number)', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'term_id' ) ); ?>" type="text" value="<?php echo esc_attr( $term_id ); ?>" />
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>">
				<?php esc_html_e( 'Product:', 'knowledgebase' ); ?>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_id' ) ); ?>">
					<option value="0"> <?php esc_html_e( 'Select a product (leave empty to organize by product)', 'knowledgebase' ); ?> </option>
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_attr( (string) $product->term_id ); ?>" <?php selected( $product_id, $product->term_id ); ?>><?php echo esc_html( $product->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>">
			<?php esc_html_e( 'Max Depth (-1 for unlimited)', 'knowledgebase' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'depth' ) ); ?>" type="number" value="<?php echo esc_attr( $depth ); ?>" />
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
		$instance                   = $old_instance;
		$instance['title']          = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['term_id']        = ( ! empty( $new_instance['term_id'] ) ) ? intval( $new_instance['term_id'] ) : '';
		$instance['product_id']     = isset( $new_instance['product_id'] ) ? (int) $new_instance['product_id'] : 0;
		$instance['depth']          = ( ! empty( $new_instance['depth'] ) ) ? (int) $new_instance['depth'] : -1;
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

		// Return if not a WZKB post type archive or single page.
		if ( ! is_post_type_archive( 'wz_knowledgebase' ) && ! is_singular( 'wz_knowledgebase' ) && ! is_tax( 'wzkb_category' ) && ! is_tax( 'wzkb_tag' ) && ! is_tax( 'wzkb_product' ) ) {
			return;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$product_id = isset( $instance['product_id'] ) ? (int) $instance['product_id'] : 0;
		$term_id    = ! empty( $instance['term_id'] ) ? (int) $instance['term_id'] : 0;

		$arguments = array(
			'is_widget'      => 1,
			'instance_id'    => $this->number,
			'depth'          => ( isset( $instance['depth'] ) ) ? (int) $instance['depth'] : -1,
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

		if ( $product_id > 0 ) {
			$output .= wzkb_get_product_sections_list( $product_id, $arguments );
		} else {
			$output .= \WebberZone\Knowledge_Base\Frontend\Display::get_sections_tree( $term_id, $arguments );
		}

		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
