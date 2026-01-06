<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase.
/**
 * The template for displaying product (wzkb_product) archives
 *
 * Used to display KB product archives if no archive template is found in the theme folder.
 *
 * If you'd like to further customize the single views, you may create a
 * taxonomy-wzkb_product.php file in your theme's folder
 *
 * @package WebberZone\Knowledge_Base
 */

global $wp_query;
$wzkb_current_taxonomy = $wp_query->get_queried_object();

get_header();

if ( wzkb_get_option( 'include_styles' ) ) {
	wp_enqueue_style( 'wz-knowledgebase-styles' );
}
?>
<a href="#main" class="skip-link screen-reader-text"><?php esc_html_e( 'Skip to content', 'knowledgebase' ); ?></a>
<div class="wrap wzkb-wrap">
	<div id="wzkb-content-primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php wzkb_breadcrumb(); ?>
			<?php wzkb_search_form(); ?>
			<?php if ( $wzkb_current_taxonomy ) : ?>

				<header class="page-header">
					<h1 class="page-title"><?php echo esc_html( $wzkb_current_taxonomy->name ); ?></h1>
					<?php if ( ! empty( $wzkb_current_taxonomy->description ) ) : ?>
						<div class="taxonomy-description"><?php echo esc_html( $wzkb_current_taxonomy->description ); ?></div>
					<?php endif; ?>
				</header><!-- .page-header -->

				<?php
				// Display top-level sections for this product.
				$wzkb_knowledge_args = array(
					'product'     => $wzkb_current_taxonomy->term_id,
					'extra_class' => 'wzkb-product-archive',
				);
				echo wzkb_knowledge( $wzkb_knowledge_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>

			<?php else : ?>
				<?php esc_html_e( 'No product found', 'knowledgebase' ); ?>
			<?php endif; ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

	<?php
	if ( wzkb_get_option( 'show_sidebar' ) ) {
		include_once 'sidebar-primary.php';
	}
	?>
</div><!-- .wrap.wzkb-wrap -->

<?php
get_footer();
