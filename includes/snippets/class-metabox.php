<?php
/**
 * Register ATA Metabox.
 *
 * @link  https://webberzone.com
 * @since 1.7.0
 *
 * @package WebberZone\Snippetz
 */

namespace WebberZone\Snippetz\Snippets;

use WebberZone\Snippetz\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ATA Metabox class to register the metabox for ata_snippets post type.
 *
 * @since 1.7.0
 */
class Metabox {

	/**
	 * Metabox API.
	 *
	 * @var object Metabox API.
	 */
	public $metabox_api;

	/**
	 * Settings Key.
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @var string Prefix.
	 */
	public $prefix;

	/**
	 * Main constructor class.
	 */
	public function __construct() {
		$this->settings_key = 'ata_meta';
		$this->prefix       = 'ata';

		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_metabox_api' ) );
		Hook_Registry::add_action( 'ata_meta_box', array( $this, 'display_snippet_stats' ) );
	}

	/**
	 * Initialise the metabox API.
	 *
	 * @since 3.3.0
	 */
	public function initialise_metabox_api() {
		$this->metabox_api = new \WebberZone\Snippetz\Admin\Settings\Metabox_API(
			array(
				'settings_key'        => $this->settings_key,
				'prefix'              => $this->prefix,
				'post_type'           => 'ata_snippets',
				'title'               => __( 'WebberZone Snippetz', 'add-to-all' ),
				'registered_settings' => $this->get_registered_settings(),
				'translation_strings' => array(
					'checkbox_modified'     => __( 'Modified from default', 'add-to-all' ),
					/* translators: %s: Search term */
					'tom_select_no_results' => __( 'No results found for "%s"', 'add-to-all' ),
				),
			)
		);
	}

	/**
	 * Get registered settings for metabox.
	 *
	 * @return array Registered settings.
	 */
	public function get_registered_settings() {

		$settings = array(
			'disable_snippet'      => array(
				'id'      => 'disable_snippet',
				'name'    => __( 'Disable Snippet', 'add-to-all' ),
				'desc'    => __( 'When enabled the snippet will not be displayed.', 'add-to-all' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'snippet_type'         => array(
				'id'      => 'snippet_type',
				'name'    => __( 'Snippet Type', 'add-to-all' ),
				'desc'    => __( 'Select the type of snippet you want to add. You will need to update/save this page in order to update the editor format above.', 'add-to-all' ),
				'type'    => 'select',
				'default' => 'html',
				'options' => array(
					'html' => __( 'HTML', 'add-to-all' ),
					'js'   => __( 'Javascript', 'add-to-all' ),
					'css'  => __( 'CSS', 'add-to-all' ),
				),
			),
			'step1_header'         => array(
				'id'   => 'step1_header',
				'name' => '<h3>' . esc_html__( 'Step 1: Where to display this', 'add-to-all' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'add_to_header'        => array(
				'id'      => 'add_to_header',
				'name'    => __( 'Add to Header', 'add-to-all' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'add_to_footer'        => array(
				'id'      => 'add_to_footer',
				'name'    => __( 'Add to Footer', 'add-to-all' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_before'       => array(
				'id'      => 'content_before',
				'name'    => __( 'Add before Content', 'add-to-all' ),
				'desc'    => __( 'When enabled the contents of this snippet are automatically added before the content of posts based on the selection below.', 'add-to-all' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_after'        => array(
				'id'      => 'content_after',
				'name'    => esc_html__( 'Add after Content', 'add-to-all' ),
				'desc'    => esc_html__( 'When enabled the contents of this snippet are automatically added after the content of posts based on the selection below.', 'add-to-all' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'step2_header'         => array(
				'id'   => 'step2_header',
				'name' => '<h3>' . esc_html__( 'Step 2: Conditions', 'add-to-all' ) . '</h3>',
				'desc' => esc_html__( 'Select at least one condition below to display the contents of this snippet. Leaving any of the conditions blank will ignore it. Leaving all blank will ignore the snippet. If you want to include the snippet on all posts, then you can use the Global Settings.', 'add-to-all' ),
				'type' => 'header',
			),
			'include_relation'     => array(
				'id'      => 'include_relation',
				'name'    => esc_html__( 'The logical relationship between each condition below', 'add-to-all' ),
				'desc'    => esc_html__( 'Selecting OR would match any of the condition below and selecting AND would match all the conditions below.', 'add-to-all' ),
				'type'    => 'radio',
				'default' => 'or',
				'options' => array(
					'or'  => esc_html__( 'OR', 'add-to-all' ),
					'and' => esc_html__( 'AND', 'add-to-all' ),
				),
			),
			'include_on_posttypes' => array(
				'id'      => 'include_on_posttypes',
				'name'    => esc_html__( 'Include on these post types', 'add-to-all' ),
				'desc'    => esc_html__( 'Select on which post types to display the contents of this snippet.', 'add-to-all' ),
				'type'    => 'posttypes',
				'default' => '',
			),
			'include_on_posts'     => array(
				'id'      => 'include_on_posts',
				'name'    => esc_html__( 'Include on these Post IDs', 'add-to-all' ),
				'desc'    => esc_html__( 'Enter a comma-separated list of post, page or custom post type IDs on which to include the code. Any incorrect ids will be removed when saving.', 'add-to-all' ),
				'size'    => 'large',
				'type'    => 'postids',
				'default' => '',
			),
			'include_on_category'  => array(
				'id'               => 'include_on_category',
				'name'             => esc_html__( 'Include on these Categories', 'add-to-all' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'add-to-all' ),
				'type'             => 'csv',
				'default'          => '',
				'size'             => 'large',
				'field_class'      => 'category_autocomplete',
				'field_attributes' => array(
					'data-wp-taxonomy' => 'category',
				),
			),
			'include_on_post_tag'  => array(
				'id'               => 'include_on_post_tag',
				'name'             => esc_html__( 'Include on these Tags', 'add-to-all' ),
				'desc'             => esc_html__( 'Comma separated list of tag slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'add-to-all' ),
				'type'             => 'csv',
				'default'          => '',
				'size'             => 'large',
				'field_class'      => 'category_autocomplete',
				'field_attributes' => array(
					'data-wp-taxonomy' => 'post_tag',
				),
			),
			'step3_header'         => array(
				'id'   => 'step3_header',
				'name' => '<h3>' . esc_html__( 'Step 3: Priority', 'add-to-all' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'include_priority'     => array(
				'id'      => 'include_priority',
				'name'    => esc_html__( 'Priority', 'add-to-all' ),
				'desc'    => esc_html__( 'Used to specify the order in which the code snippets are added to the content. Lower numbers correspond with earlier addition, and functions with the same priority are added in the order in which they were added, typically by post ID.', 'add-to-all' ),
				'type'    => 'number',
				'size'    => 'small',
				'min'     => 0,
				'default' => 10,
			),
		);

		/**
		 * Filter array of registered settings for metabox.
		 *
		 * @param array $settings Registered settings.
		 */
		$settings = apply_filters( $this->prefix . '_metabox_settings', $settings );

		return $settings;
	}

	/**
	 * Display stats about the current snippet.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function display_snippet_stats( $post ) {
		$snippet_type = get_post_meta( $post->ID, '_ata_snippet_type', true );

		if ( ! in_array( $snippet_type, array( 'css', 'js' ), true ) ) {
			return;
		}

		$file_url = get_post_meta( $post->ID, '_ata_snippet_file', true );

		if ( ! $file_url ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$size  = filesize( $file_path );
		$mtime = filemtime( $file_path );
		?>
		<div class="ata-snippet-stats" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border: 1px solid #c3c4c7;">
			<p>
				<strong><?php esc_html_e( 'Snippet File Stats:', 'add-to-all' ); ?></strong>
				<br />
				<?php
				/* translators: %s: File URL */
				printf( esc_html__( 'URL: %s', 'add-to-all' ), '<a href="' . esc_url( $file_url ) . '" target="_blank">' . esc_html( $file_url ) . '</a>' );
				echo '<br />';
				/* translators: %s: File size */
				printf( esc_html__( 'Size: %s', 'add-to-all' ), esc_html( size_format( $size, 2 ) ) );
				echo '<br />';
				/* translators: %s: Last Modified */
				printf( esc_html__( 'Last Modified: %s', 'add-to-all' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $mtime ) ) );
				?>
			</p>
		</div>
		<?php
	}
}
