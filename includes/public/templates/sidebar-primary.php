<?php
/**
 * The template for displaying the sidebar.
 *
 * @link  https://webberzone.com
 * @since 1.9.0
 *
 * @package WZKB
 */

?>

<div id="wzkb-sidebar-primary" class="sidebar">
	<?php if ( is_active_sidebar( 'wzkb-primary' ) ) : ?>
		<?php dynamic_sidebar( 'wzkb-primary' ); ?>
	<?php else : ?>
		<aside id="meta" class="widget">
			<h3 class="widget-title"><?php esc_html_e( 'Meta', 'knowledgebase' ); ?></h3>
			<ul>
				<?php wp_register(); ?>
				<li><?php wp_loginout(); ?></li>
				<?php wp_meta(); ?>
			</ul>
		</aside>
	<?php endif; ?>
</div>
