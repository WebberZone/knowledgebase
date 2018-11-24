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
		/*** General settings */
		'general' => apply_filters(
			'wzkb_settings_general',
			array(
				'slug_header'       => array(
					'id'   => 'slug_header',
					'name' => '<h3>' . esc_html__( 'Slug options', 'knowledgebase' ) . '</h3>',
					'desc' => '',
					'type' => 'header',
				),
				'kb_slug'           => array(
					'id'      => 'kb_slug',
					'name'    => esc_html__( 'Knowledgebase slug', 'knowledgebase' ),
					'desc'    => esc_html__( 'This will set the opening path of the URL of the knowledgebase and is set when registering the custom post type', 'knowledgebase' ),
					'type'    => 'text',
					'options' => 'knowledgebase',
				),
				'category_slug'     => array(
					'id'      => 'category_slug',
					'name'    => esc_html__( 'Category slug', 'knowledgebase' ),
					'desc'    => esc_html__( 'Each category is a section of the knowledgebase. This setting is used when registering the custom category and forms a part of the URL when browsing category archives', 'knowledgebase' ),
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
					'name'    => esc_html__( 'Delete all knowledgebase posts on uninstall', 'knowledgebase' ),
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
					'desc'    => esc_html__( 'Adds the knowledgebase articles to the main RSS feed for your site', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'disable_kb_feed'   => array(
					'id'      => 'disable_kb_feed',
					'name'    => esc_html__( 'Disable KB feed', 'knowledgebase' ),
					/* translators: 1: Opening link tag, 2: Closing link tag. */
					'desc'    => sprintf( esc_html__( 'The knowledgebase articles have a default feed. This option will disable the feed. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'knowledgebase' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
					'type'    => 'checkbox',
					'options' => false,
				),
			)
		),
		/*** Output settings */
		'output'  => apply_filters(
			'wzkb_settings_output',
			array(
				'category_level'     => array(
					'id'      => 'category_level',
					'name'    => esc_html__( 'First section level', 'knowledgebase' ),
					'desc'    => esc_html__( 'This option allows you to create multi-level knowledgebases. This works in conjunction with the inbuilt styles. Set to 1 to lay out the top level sections in a grid. Set to 2 to lay out the second level categories in the grid. This is great if you have multiple products and want to create separate knowledgebases for each of them. The default option is 2 and was the behaviour of this plugin before v1.5.0.', 'knowledgebase' ),
					'type'    => 'number',
					'options' => '2',
					'size'    => 'small',
					'min'     => '1',
					'max'     => '5',
				),
				'show_article_count' => array(
					'id'      => 'show_article_count',
					'name'    => esc_html__( 'Show article count', 'knowledgebase' ),
					'desc'    => esc_html__( 'If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling wzkb_section_count', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'show_excerpt'       => array(
					'id'      => 'show_excerpt',
					'name'    => esc_html__( 'Show excerpt', 'knowledgebase' ),
					'desc'    => esc_html__( 'Select to include the post excerpt after the article link', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'clickable_section'  => array(
					'id'      => 'clickable_section',
					'name'    => esc_html__( 'Link section title', 'knowledgebase' ),
					'desc'    => esc_html__( 'If selected, the title of each section of the knowledgebase will be linked to its own page', 'knowledgebase' ),
					'type'    => 'checkbox',
					'options' => true,
				),
			)
		),
		/*** Style settings */
		'styles'  => apply_filters(
			'wzkb_settings_styles',
			array(
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
					'desc'    => esc_html__( 'Set number of columns to display the knowledgebase archives. This is only works if the above option is selected.', 'knowledgebase' ),
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
			)
		),
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

