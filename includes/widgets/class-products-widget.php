<?php
/**
 * WZ Knowledge Base Products Widget class.
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Widgets;

use WebberZone\Knowledge_Base\Frontend\Display;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Create a WordPress Products Widget for WZ Knowledge Base.
 *
 * @since 2.3.0
 */
class Products_Widget extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_wzkb_products',
			__( 'WZKB Products', 'knowledgebase' ),
			array(
				'description'                 => __( 'Display sections under a selected product for the Knowledge Base.', 'knowledgebase' ),
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
		$title      = isset( $instance['title'] ) ? $instance['title'] : '';
		$product_id = isset( $instance['product_id'] ) ? (int) $instance['product_id'] : 0;
		$depth      = isset( $instance['depth'] ) ? (int) $instance['depth'] : 0;

		$products = get_terms(
			array(
				'taxonomy'   => 'wzkb_product',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'knowledgebase' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>">
				<?php esc_html_e( 'Product:', 'knowledgebase' ); ?>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_id' ) ); ?>">
					<option value="0"><?php esc_html_e( 'Select a product', 'knowledgebase' ); ?></option>
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_attr( (string) $product->term_id ); ?>" <?php selected( $product_id, $product->term_id ); ?>><?php echo esc_html( $product->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>">
				<?php esc_html_e( 'Max Depth (0 for unlimited):', 'knowledgebase' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'depth' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'depth' ) ); ?>" type="number" value="<?php echo esc_attr( (string) $depth ); ?>" min="0" />
			</label>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance               = array();
		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['product_id'] = isset( $new_instance['product_id'] ) ? (int) $new_instance['product_id'] : 0;
		$instance['depth']      = isset( $new_instance['depth'] ) ? (int) $new_instance['depth'] : 0;
		return $instance;
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
		if ( ! is_post_type_archive( 'wz_knowledgebase' ) && ! is_singular( 'wz_knowledgebase' ) && ! is_tax( 'wzkb_category' ) && ! is_tax( 'wzkb_tag' ) ) {
			return;
		}

		$title      = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title      = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$product_id = ! empty( $instance['product_id'] ) ? (int) $instance['product_id'] : 0;
		$depth      = ! empty( $instance['depth'] ) ? (int) $instance['depth'] : 0;

		if ( ! $product_id ) {
			return;
		}

		$output  = '';
		$output .= $args['before_widget'];
		if ( ! empty( $title ) ) {
			$output .= $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		$output .= Display::get_product_sections_list( $product_id, array( 'depth' => $depth ) );
		$output .= $args['after_widget'];

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
