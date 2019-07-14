<?php
/**
 * Default settings.
 *
 * Functions to get the default settings for the plugin.
 *
 * @link  https://webberzone.com
 * @since 1.6.0
 *
 * @package WZKB
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.2.0
 *
 * @return array Settings array
 */
function wzkb_get_registered_settings() {

	$wzkb_settings = array(
		'general' => wzkb_settings_general(),
		'output'  => wzkb_settings_output(),
		'styles'  => wzkb_settings_styles(),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 1.2.0
	 *
	 * @param array $wzkb_setings Settings array
	 */
	return apply_filters( 'wzkb_registered_settings', $wzkb_settings );

}

/**
 * Returns the General settings.
 *
 * @since 1.8.0
 *
 * @return array General settings.
 */
function wzkb_settings_general() {

	$settings = array(
		'slug_header'       => array(
			'id'   => 'slug_header',
			'name' => '<h3>' . esc_html__( 'Slug options', 'knowledgebase' ) . '</h3>',
			'desc' => '',
			'type' => 'header',
		),
		'kb_slug'           => array(
			'id'      => 'kb_slug',
			'name'    => esc_html__( 'Knowledge Base slug', 'knowledgebase' ),
			'desc'    => esc_html__( 'This will set the opening path of the URL of the knowledge base and is set when registering the custom post type', 'knowledgebase' ),
			'type'    => 'text',
			'options' => 'knowledgebase',
		),
		'category_slug'     => array(
			'id'      => 'category_slug',
			'name'    => esc_html__( 'Category slug', 'knowledgebase' ),
			'desc'    => esc_html__( 'Each category is a section of the knowledge base. This setting is used when registering the custom category and forms a part of the URL when browsing category archives', 'knowledgebase' ),
			'type'    => 'text',
			'options' => 'section',
		),
		'tag_slug'          => array(
			'id'      => 'tag_slug',
			'name'    => esc_html__( 'Tag slug', 'knowledgebase' ),
			'desc'    => esc_html__( 'Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives', 'knowledgebase' ),
			'type'    => 'text',
			'options' => 'kb-tags',
		),
		'uninstall_header'  => array(
			'id'      => 'uninstall_header',
			'name'    => '<h3>' . esc_html__( 'Uninstall options', 'knowledgebase' ) . '</h3>',
			'desc'    => '',
			'type'    => 'header',
			'options' => '',
		),
		'uninstall_options' => array(
			'id'      => 'uninstall_options',
			'name'    => esc_html__( 'Delete options on uninstall', 'knowledgebase' ),
			'desc'    => esc_html__( 'Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'uninstall_data'    => array(
			'id'      => 'uninstall_data',
			'name'    => esc_html__( 'Delete all knowledge base posts on uninstall', 'knowledgebase' ),
			'desc'    => esc_html__( 'Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'feed_header'       => array(
			'id'      => 'feed_header',
			'name'    => '<h3>' . esc_html__( 'Feed options', 'knowledgebase' ) . '</h3>',
			'desc'    => '',
			'type'    => 'header',
			'options' => '',
		),
		'include_in_feed'   => array(
			'id'      => 'include_in_feed',
			'name'    => esc_html__( 'Include in feed', 'knowledgebase' ),
			'desc'    => esc_html__( 'Adds the knowledge base articles to the main RSS feed for your site', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'disable_kb_feed'   => array(
			'id'      => 'disable_kb_feed',
			'name'    => esc_html__( 'Disable KB feed', 'knowledgebase' ),
			/* translators: 1: Opening link tag, 2: Closing link tag. */
			'desc'    => sprintf( esc_html__( 'The knowledge base articles have a default feed. This option will disable the feed. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'knowledgebase' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
			'type'    => 'checkbox',
			'options' => false,
		),
	);

	/**
	 * Filters the General settings array
	 *
	 * @since 1.8.0
	 *
	 * @param array $settings General Settings array
	 */
	return apply_filters( 'wzkb_settings_general', $settings );
}

/**
 * Returns the Output settings.
 *
 * @since 1.8.0
 *
 * @return array Output settings.
 */
function wzkb_settings_output() {

	$settings = array(
		'kb_title'            => array(
			'id'          => 'kb_title',
			'name'        => esc_html__( 'Knowledge base title', 'knowledgebase' ),
			'desc'        => esc_html__( 'This will be displayed as the title of the archive title as well as on other relevant places.', 'knowledgebase' ),
			'type'        => 'text',
			'options'     => 'Knowledge Base',
			'field_class' => 'large-text',
		),
		'category_level'      => array(
			'id'      => 'category_level',
			'name'    => esc_html__( 'First section level', 'knowledgebase' ),
			'desc'    => esc_html__( 'This option allows you to create multi-level knowledge bases. This works in conjunction with the inbuilt styles. Set to 1 to lay out the top level sections in a grid. Set to 2 to lay out the second level categories in the grid. This is great if you have multiple products and want to create separate knowledge bases for each of them. The default option is 2 and was the behaviour of this plugin before v1.5.0.', 'knowledgebase' ),
			'type'    => 'number',
			'options' => '2',
			'size'    => 'small',
			'min'     => '1',
			'max'     => '5',
		),
		'show_article_count'  => array(
			'id'      => 'show_article_count',
			'name'    => esc_html__( 'Show article count', 'knowledgebase' ),
			'desc'    => esc_html__( 'If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling wzkb_section_count', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'show_excerpt'        => array(
			'id'      => 'show_excerpt',
			'name'    => esc_html__( 'Show excerpt', 'knowledgebase' ),
			'desc'    => esc_html__( 'Select to include the post excerpt after the article link', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'clickable_section'   => array(
			'id'      => 'clickable_section',
			'name'    => esc_html__( 'Link section title', 'knowledgebase' ),
			'desc'    => esc_html__( 'If selected, the title of each section of the knowledgebase will be linked to its own page', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'show_empty_sections' => array(
			'id'      => 'show_empty_sections',
			'name'    => esc_html__( 'Show empty sections', 'knowledgebase' ),
			'desc'    => esc_html__( 'If selected, sections with no articles will also be displayed', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'show_sidebar'        => array(
			'id'      => 'show_sidebar',
			'name'    => esc_html__( 'Show sidebar', 'knowledgebase' ),
			'desc'    => esc_html__( 'Add the sidebar of your theme into the inbuilt templates for archive, sections and search. Activate this option if your theme does not already include this.', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => false,
		),
	);

	/**
	 * Filters the Output settings array
	 *
	 * @since 1.8.0
	 *
	 * @param array $settings Output Settings array
	 */
	return apply_filters( 'wzkb_settings_output', $settings );
}

/**
 * Returns the Styles settings.
 *
 * @since 1.8.0
 *
 * @return array Styles settings.
 */
function wzkb_settings_styles() {

	$settings = array(
		'include_styles' => array(
			'id'      => 'include_styles',
			'name'    => esc_html__( 'Include inbuilt styles', 'knowledgebase' ),
			'desc'    => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'knowledgebase' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'columns'        => array(
			'id'      => 'columns',
			'name'    => esc_html__( 'Number of columns', 'knowledgebase' ),
			'desc'    => esc_html__( 'Set number of columns to display the knowledge base archives. This is only works if the above option is selected.', 'knowledgebase' ),
			'type'    => 'number',
			'options' => '2',
			'size'    => 'small',
			'min'     => '1',
			'max'     => '5',
		),
		'custom_css'     => array(
			'id'      => 'custom_css',
			'name'    => esc_html__( 'Custom CSS', 'knowledgebase' ),
			'desc'    => esc_html__( 'Enter any custom valid CSS without any wrapping &lt;style&gt; tags', 'knowledgebase' ),
			'type'    => 'css',
			'options' => '',
		),
	);

	/**
	 * Filters the Styles settings array
	 *
	 * @since 1.8.0
	 *
	 * @param array $settings Styles Settings array
	 */
	return apply_filters( 'wzkb_settings_styles', $settings );
}
