<?php
/**
 * Help tab.
 *
 * Functions to generated the help tab on the Settings page.
 *
 * @link  https://webberzone.com
 * @since 1.2.0
 *
 * @package	WZKB
 * @subpackage Admin/Help
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


function wzkb_settings_help() {
	global $wzkb_settings_page;

	$screen = get_current_screen();

	if ( $screen->id != $wzkb_settings_page ) {
		return;
	}

	$screen->set_help_sidebar(
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">WebberZone support site</a>.', 'knowledgebase' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
		'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'knowledgebase' ), esc_url( 'https://wordpress.org/support/plugin/knowledgebase' ) ) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a> (bug reports only).', 'knowledgebase' ),
			esc_url( 'https://github.com/WebberZone/knowledgebase/issues' ),
			esc_url( 'https://github.com/WebberZone/knowledgebase' )
		) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'wzkb-settings-general',
		'title'	    => __( 'General', 'knowledgebase' ),
		'content'	=>
		'<p>' . __( 'This screen provides the basic settings for configuring your knowledgebase.', 'knowledgebase' ) . '</p>' .
			'<p>' . __( 'Set the knowledgebase slugs which drive what the urls are for the knowledgebase homepage, articles, categories and tags.', 'knowledgebase' ) . '</p>',
	) );

	$screen->add_help_tab( array(
		'id'	    => 'wzkb-settings-styles',
		'title'	    => __( 'Styles', 'knowledgebase' ),
		'content'	=>
		'<p>' . __( 'This screen provides the look and feel of the knowledgebase with CSS.', 'knowledgebase' ) . '</p>' .
			'<p>' . __( 'Disable the styles included within the plugin and/or add your own CSS styles to customize this.', 'knowledgebase' ) . '</p>',
	) );

	do_action( 'wzkb_settings_help', $screen );

}
