<?php
/**
 * Knowledge Base Tools Page.
 *
 * Provides a generic tools page with extensibility hooks for features to add their own tools.
 *
 * @package WebberZone\Knowledge_Base\Admin
 * @since 3.0.0
 */

namespace WebberZone\Knowledge_Base\Admin;

use WebberZone\Knowledge_Base\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Tools Page Class.
 *
 * @since 3.0.0
 */
class Tools_Page {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.0.0
	 */
	public function admin_menu() {
		$this->parent_id = add_submenu_page(
			'edit.php?post_type=wz_knowledgebase',
			esc_html__( 'Knowledge Base Tools', 'knowledgebase' ),
			esc_html__( 'Tools', 'knowledgebase' ),
			'manage_options',
			'wzkb_tools_page',
			array( $this, 'render_page' )
		);

		Hook_Registry::add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_style( 'wp-spinner' );
		}
	}

	/**
	 * Render the tools page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render_page() {
		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Knowledge Base Tools', 'knowledgebase' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php
			/**
			 * Action hook to add tools page content.
			 *
			 * Features can hook into this to add their own tool sections.
			 *
			 * @since 3.0.0
			 */
			do_action( 'wzkb_tools_page_content' );
			?>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php
				/**
				 * Action hook to add sidebar content before standard sidebar.
				 *
				 * @since 3.0.0
				 */
				do_action( 'wzkb_tools_page_sidebar' );

				include_once 'sidebar.php';
				?>
			</div><!-- /#side-sortables -->
		</div><!-- /#postbox-container-1 -->

		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add help tabs.
	 *
	 * @since 3.0.0
	 */
	public static function help_tabs() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$screen->set_help_sidebar(
			'<p>' . sprintf(
				/* translators: 1: Support link. */
				__( 'For more information visit the <a href="%1$s">WebberZone support site</a>.', 'knowledgebase' ),
				esc_url( 'https://webberzone.com/support/' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wzkb-tools-general',
				'title'   => __( 'General', 'knowledgebase' ),
				'content' =>
				'<p>' . __( 'This screen provides tools for managing the Knowledge Base.', 'knowledgebase' ) . '</p>' .
					'<p>' . __( 'Different features can add their own tool sections to this page.', 'knowledgebase' ) . '</p>',
			)
		);

		/**
		 * Action hook to add additional help tabs.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Screen $screen Current screen object.
		 */
		do_action( 'wzkb_tools_help_tabs', $screen );
	}
}
