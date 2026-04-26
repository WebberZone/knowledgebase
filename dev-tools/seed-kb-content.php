<?php
/**
 * WebberZone Knowledge Base — Demo Content Seeder (Part 1: taxonomy setup).
 *
 * Run via: wp eval-file dev-tools/seed-kb-content.php --path=/your/wp
 *
 * @package WebberZone\Knowledge_Base
 */

// ---------------------------------------------------------------------------
// 1. CATEGORIES
// ---------------------------------------------------------------------------

/**
 * Create a wzkb_category term, skipping if it already exists.
 *
 * @param string $name   Term name.
 * @param string $slug   Term slug.
 * @param int    $term_parent Parent term ID (0 for top-level).
 * @param string $desc   Description.
 * @return int Term ID.
 */
function wzkb_seed_category( $name, $slug, $term_parent = 0, $desc = '' ) {
	$existing = get_term_by( 'slug', $slug, 'wzkb_category' );
	if ( $existing ) {
		WP_CLI::log( "[skip] Category already exists: {$name} (#{$existing->term_id})" );
		return (int) $existing->term_id;
	}
	$result = wp_insert_term(
		$name,
		'wzkb_category',
		array(
			'slug'        => $slug,
			'parent'      => $term_parent,
			'description' => $desc,
		)
	);
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "Category failed: {$name} — " . $result->get_error_message() );
		return 0;
	}
	WP_CLI::log( "[ok] Category: {$name} (#{$result['term_id']})" );
	return (int) $result['term_id'];
}

/**
 * Create a wzkb_tag term, skipping if it already exists.
 *
 * @param string $slug Tag slug (used as name too).
 * @return int Term ID.
 */
function wzkb_seed_tag( $slug ) {
	$existing = get_term_by( 'slug', $slug, 'wzkb_tag' );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$result = wp_insert_term( $slug, 'wzkb_tag', array( 'slug' => $slug ) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "Tag failed: {$slug} — " . $result->get_error_message() );
		return 0;
	}
	return (int) $result['term_id'];
}

WP_CLI::log( '' );
WP_CLI::log( '=== Creating categories ===' );

// Top-level.
$cat_gs  = wzkb_seed_category( 'Getting Started', 'getting-started', 0, 'Begin your WordPress journey here.' );
$cat_wpc = wzkb_seed_category( 'WordPress Core', 'wordpress-core', 0, 'Everything about WordPress core features.' );
$cat_wc  = wzkb_seed_category( 'WooCommerce', 'woocommerce-kb', 0, 'WooCommerce store management guides.' );
$cat_plg = wzkb_seed_category( 'Plugins', 'plugins-kb', 0, 'Plugin setup, configuration and troubleshooting.' );
$cat_thd = wzkb_seed_category( 'Themes & Design', 'themes-design', 0, 'Theme customisation and block editor guides.' );
$cat_ps  = wzkb_seed_category( 'Performance & Security', 'performance-security', 0, 'Speed optimisation and site security.' );

// Getting Started sub-cats.
$s_install = wzkb_seed_category( 'Installation', 'installation', $cat_gs );
$s_dash    = wzkb_seed_category( 'Dashboard Overview', 'dashboard-overview', $cat_gs );
$s_users   = wzkb_seed_category( 'User Roles & Permissions', 'user-roles', $cat_gs );

// WordPress Core sub-cats.
$s_posts = wzkb_seed_category( 'Posts & Pages', 'posts-pages', $cat_wpc );
$s_media = wzkb_seed_category( 'Media Library', 'media-library', $cat_wpc );
$s_menus = wzkb_seed_category( 'Menus & Widgets', 'menus-widgets', $cat_wpc );

// WooCommerce sub-cats.
$s_wcprod = wzkb_seed_category( 'Products', 'wc-products', $cat_wc );
$s_orders = wzkb_seed_category( 'Orders & Refunds', 'orders-refunds', $cat_wc );
$s_pay    = wzkb_seed_category( 'Payments & Gateways', 'payments-gateways', $cat_wc );
$s_ship   = wzkb_seed_category( 'Shipping & Tax', 'shipping-tax', $cat_wc );
$s_coup   = wzkb_seed_category( 'Coupons & Discounts', 'coupons-discounts', $cat_wc );

// Plugins sub-cats.
$s_seo = wzkb_seed_category( 'SEO Plugins', 'seo-plugins', $cat_plg );
$s_pb  = wzkb_seed_category( 'Page Builders', 'page-builders', $cat_plg );
$s_wbz = wzkb_seed_category( 'WebberZone Plugins', 'webberzone-plugins', $cat_plg );

// Themes sub-cats.
$s_block   = wzkb_seed_category( 'Block Editor (Gutenberg)', 'block-editor', $cat_thd );
$s_classic = wzkb_seed_category( 'Classic Themes', 'classic-themes', $cat_thd );
$s_css     = wzkb_seed_category( 'CSS Customisation', 'css-customisation', $cat_thd );

// Performance sub-cats.
$s_cache = wzkb_seed_category( 'Caching & CDN', 'caching-cdn', $cat_ps );
$s_back  = wzkb_seed_category( 'Backups', 'backups', $cat_ps );
$s_ssl   = wzkb_seed_category( 'SSL & HTTPS', 'ssl-https', $cat_ps );
$s_mal   = wzkb_seed_category( 'Malware & Hardening', 'malware-hardening', $cat_ps );

WP_CLI::log( '' );
WP_CLI::log( '=== Creating tags ===' );

$tag_slugs = array(
	'woocommerce',
	'gutenberg',
	'block-editor',
	'security',
	'performance',
	'caching',
	'seo',
	'payments',
	'shipping',
	'orders',
	'plugins',
	'themes',
	'php',
	'mysql',
	'rest-api',
	'hooks',
	'shortcodes',
	'multisite',
	'cron',
	'debugging',
);
$tag_ids   = array();
foreach ( $tag_slugs as $slug ) {
	$tag_ids[ $slug ] = wzkb_seed_tag( $slug );
}
WP_CLI::success( 'Tags ready: ' . count( $tag_ids ) );

// ---------------------------------------------------------------------------
// 2. HELPER: insert one article
// ---------------------------------------------------------------------------

/**
 * Insert a KB article with category and tag assignments.
 *
 * @param string $title   Post title.
 * @param string $content Post content (block markup).
 * @param int[]  $cats    wzkb_category term IDs.
 * @param int[]  $tids    wzkb_tag term IDs.
 * @return int Post ID.
 */
function wzkb_seed_article( $title, $content, array $cats, array $tids ) {
	$pid = wp_insert_post(
		array(
			'post_type'    => 'wz_knowledgebase',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
		),
		true
	);

	if ( is_wp_error( $pid ) ) {
		WP_CLI::warning( "Failed: {$title} — " . $pid->get_error_message() );
		return 0;
	}
	wp_set_post_terms( $pid, $cats, 'wzkb_category' );
	wp_set_post_terms( $pid, $tids, 'wzkb_tag' );
	WP_CLI::log( "[ok] #{$pid}: {$title}" );
	return $pid;
}


$img     = 'https://picsum.photos/seed';
$created = 0;

WP_CLI::log( '' );
WP_CLI::log( '=== Creating articles ===' );

// Helper shorthand — resolves tag slugs to IDs.
$tag_resolver = function ( ...$slugs ) use ( $tag_ids ) {
	return array_values(
		array_filter(
			array_map(
				function ( $s ) use ( $tag_ids ) {
					return isset( $tag_ids[ $s ] ) ? $tag_ids[ $s ] : null;
				},
				$slugs
			)
		)
	);
};

// ---- Getting Started / Installation ----
$created += (int) (bool) wzkb_seed_article(
	'How to Install WordPress on a Web Host',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wp-install-1/1200/630\" alt=\"Install WordPress\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Installing WordPress is straightforward. Download the latest version from wordpress.org, upload it to your server via FTP, create a MySQL database, and run the installer by visiting your domain.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Step 1: Download WordPress</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Visit wordpress.org and download the latest stable ZIP. Extract it locally before uploading.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Step 2: Create a Database</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Log in to cPanel or your host's database tool and create a new MySQL database plus a dedicated user with full privileges.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Step 3: Run the Installer</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Visit your domain and follow the on-screen prompts to complete setup.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_install ),
	$tag_resolver( 'plugins', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'Installing WordPress Locally with LocalWP',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/localwp-2/1200/630\" alt=\"LocalWP\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>LocalWP is the fastest way to run WordPress on your Mac or PC without a web server. Download it from localwp.com, create a new site, and click WP Admin to open the dashboard instantly.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Choosing PHP Version</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>LocalWP lets you select the PHP and MySQL version per site — useful for testing compatibility before deploying to production.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_install ),
	$tag_resolver( 'php', 'debugging' )
);
$created += (int) (bool) wzkb_seed_article(
	'WordPress Minimum Requirements Explained',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/requirements-3/1200/630\" alt=\"Requirements\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WordPress requires PHP 7.4+ (8.0+ recommended), MySQL 5.7+ or MariaDB 10.4+, and HTTPS support. Check your version in <strong>Tools &rarr; Site Health</strong>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Always use the latest PHP 8.x branch for performance and security improvements. Most modern hosts meet these requirements out of the box.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_install ),
	$tag_resolver( 'php', 'performance' )
);

// ---- Getting Started / Dashboard ----
$created += (int) (bool) wzkb_seed_article(
	'A Tour of the WordPress Dashboard',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/dashboard-4/1200/630\" alt=\"Dashboard\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>The WordPress dashboard is the control centre of your site. It consists of the admin bar, main navigation menu, and the central work area.</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li>Posts - manage blog posts</li><li>Pages - manage static pages</li><li>Media - manage uploads</li><li>Appearance - themes and menus</li><li>Plugins - install and manage plugins</li><li>Settings - site configuration</li></ul><!-- /wp:list -->",
	array( $cat_gs, $s_dash ),
	$tag_resolver( 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'Understanding the WordPress Admin Bar',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/adminbar-5/1200/630\" alt=\"Admin Bar\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>The admin bar appears at the top of every page when you are logged in. It gives quick access to New Post, Comments, and site settings.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Hide it for specific roles using <code>show_admin_bar( false )</code> in your theme's <code>functions.php</code>.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_dash ),
	$tag_resolver( 'hooks', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use the WordPress Quick Draft Widget',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/quickdraft-6/1200/630\" alt=\"Quick Draft\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>The Quick Draft widget on the dashboard homepage lets you jot down a post idea and save it as a draft without navigating away. Simply type a title and some content, then click <strong>Save Draft</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_dash ),
	$tag_resolver( 'plugins' )
);

// ---- Getting Started / User Roles ----
$created += (int) (bool) wzkb_seed_article(
	'WordPress User Roles and Capabilities Explained',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/userroles-7/1200/630\" alt=\"User Roles\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WordPress ships with five default roles: Subscriber, Contributor, Author, Editor, and Administrator. Each role has a distinct set of capabilities.</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li><strong>Subscriber</strong> - can only manage their profile</li><li><strong>Contributor</strong> - can write posts but not publish</li><li><strong>Author</strong> - can publish their own posts</li><li><strong>Editor</strong> - can manage all posts</li><li><strong>Administrator</strong> - full control</li></ul><!-- /wp:list -->",
	array( $cat_gs, $s_users ),
	$tag_resolver( 'php', 'hooks' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Add a New User to WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/adduser-8/1200/630\" alt=\"Add User\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Users &rarr; Add New</strong>, fill in the username, email, and password, then choose an appropriate role. The user receives a welcome email with login details.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Always follow the principle of least privilege: assign the lowest role that allows the user to do their job.</p><!-- /wp:paragraph -->",
	array( $cat_gs, $s_users ),
	$tag_resolver( 'security' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Restrict Content by User Role in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/restrictcontent-9/1200/630\" alt=\"Restrict Content\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Use <code>current_user_can()</code> to check capabilities before displaying content. Plugins like Members or PublishPress Capabilities provide a UI for managing roles without code.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>if ( current_user_can( 'edit_posts' ) ) {\n    // Show editor-only content.\n}</code></pre><!-- /wp:code -->",
	array( $cat_gs, $s_users ),
	$tag_resolver( 'php', 'hooks', 'security' )
);

// ---- WordPress Core / Posts & Pages ----
$created += (int) (bool) wzkb_seed_article(
	'How to Create and Publish a Blog Post in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/blogpost-10/1200/630\" alt=\"Blog Post\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Posts &rarr; Add New</strong>. Add a title, write your content using the block editor, set a featured image, choose a category and tags, then click <strong>Publish</strong>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Use the document settings panel on the right to control the publish date, author, and permalink before publishing.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'gutenberg', 'block-editor' )
);
$created += (int) (bool) wzkb_seed_article(
	'Difference Between WordPress Posts and Pages',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/postsvspages-11/1200/630\" alt=\"Posts vs Pages\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Posts are time-stamped entries that appear in reverse chronological order on your blog. Pages are static, hierarchical content like About or Contact that live outside the blog feed.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Pages do not support categories or tags; posts do. Use posts for news and updates, pages for evergreen content.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'gutenberg' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Schedule Posts in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/scheduleposts-12/1200/630\" alt=\"Schedule Posts\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>In the block editor, open the <strong>Post</strong> panel, click the Publish date, and choose a future date and time. Click <strong>Schedule</strong> instead of Publish. WordPress will automatically publish the post at that time using WP-Cron.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'gutenberg', 'cron' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use Post Revisions in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/revisions-13/1200/630\" alt=\"Revisions\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WordPress automatically saves revisions every time you save a post. Click <strong>Revisions</strong> in the block editor sidebar to compare and restore any previous version.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Limit revisions to prevent database bloat: <code>define( 'WP_POST_REVISIONS', 5 );</code> in <code>wp-config.php</code>.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'php', 'performance' )
);
$created += (int) (bool) wzkb_seed_article(
	'Creating a Custom Page Template in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/pagetemplate-14/1200/630\" alt=\"Page Template\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Add a PHP file to your theme folder with the header comment <code>/* Template Name: My Template */</code>. It will appear in the Page Attributes panel when editing a page.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>&lt;?php\n/* Template Name: Full Width */\nget_header();\n// your custom layout\nget_footer();</code></pre><!-- /wp:code -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'php', 'themes' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Add Categories and Tags to WordPress Posts',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/catstags-15/1200/630\" alt=\"Categories and Tags\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Categories are hierarchical and required for posts. Tags are non-hierarchical keywords. Both help visitors and search engines navigate your content.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Assign them in the block editor's right sidebar under <strong>Categories</strong> and <strong>Tags</strong> panels, or manage them at <strong>Posts &rarr; Categories</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_posts ),
	$tag_resolver( 'seo', 'gutenberg' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Manage the WordPress Media Library',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/medialibrary-16/1200/630\" alt=\"Media Library\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>The Media Library stores all images, videos, audio files, and documents uploaded to your site. Access it via <strong>Media &rarr; Library</strong> and switch between List and Grid views.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>WordPress automatically generates multiple image sizes on upload. Configure them under <strong>Settings &rarr; Media</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_media ),
	$tag_resolver( 'performance' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Optimise Images Before Uploading to WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/imageoptim-17/1200/630\" alt=\"Image Optimisation\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Large images slow down page load. Compress them before uploading using Squoosh, TinyPNG, or ImageOptim. Aim for under 150 KB for content images and under 500 KB for hero images.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Use WebP format where possible — WordPress 5.8+ natively supports WebP uploads.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_media ),
	$tag_resolver( 'performance', 'caching' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Regenerate Thumbnails in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/regenthumb-18/1200/630\" alt=\"Regenerate Thumbnails\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>When you change your theme or add new image sizes, existing uploads will not have the new thumbnails. Install the <em>Regenerate Thumbnails</em> plugin and run it from <strong>Tools &rarr; Regenerate Thumbnails</strong>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Alternatively use WP-CLI: <code>wp media regenerate --yes</code></p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_media ),
	$tag_resolver( 'plugins', 'performance' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Navigation Menu in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/navmenu-19/1200/630\" alt=\"Navigation Menu\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Appearance &rarr; Menus</strong>, create a new menu, add pages, posts, or custom links, and assign it to a theme location such as Primary or Footer.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Block themes manage menus via the Site Editor using the Navigation block instead.</p><!-- /wp:paragraph -->",
	array( $cat_wpc, $s_menus ),
	$tag_resolver( 'block-editor', 'themes' )
);

// ---- WooCommerce / Products ----
$created += (int) (bool) wzkb_seed_article(
	'How to Add a Simple Product in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/simpleprod-20/1200/630\" alt=\"Simple Product\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Products &rarr; Add New</strong>. Enter a product name, description, and price. Set the product type to <em>Simple product</em>. Add images and a category, then publish.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Fill in the <strong>Product data</strong> metabox: set the regular price, sale price, SKU, and stock status.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_wcprod ),
	$tag_resolver( 'woocommerce' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Variable Product in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/variableprod-21/1200/630\" alt=\"Variable Product\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Variable products let customers choose options like size or colour. Set the product type to <em>Variable product</em>, add attributes (e.g. Size: S, M, L), then create variations and set a price for each.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_wcprod ),
	$tag_resolver( 'woocommerce' )
);
$created += (int) (bool) wzkb_seed_article(
	'Setting Up Product Categories in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-prodcats-22/1200/630\" alt=\"Product Categories\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Products &rarr; Categories</strong> to create a hierarchy of categories. Assign them when editing a product. Categories appear in the shop sidebar and can be used to build landing pages.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_wcprod ),
	$tag_resolver( 'woocommerce', 'seo' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Manage WooCommerce Product Stock',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-stock-23/1200/630\" alt=\"Stock Management\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Enable stock management globally at <strong>WooCommerce &rarr; Settings &rarr; Products &rarr; Inventory</strong>. Then set stock quantities per product under the <strong>Inventory</strong> tab in the Product data metabox.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Configure low-stock and out-of-stock thresholds so WooCommerce emails you when stock runs low.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_wcprod ),
	$tag_resolver( 'woocommerce' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Digital / Downloadable Product in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-digital-24/1200/630\" alt=\"Downloadable Product\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Tick both <strong>Virtual</strong> and <strong>Downloadable</strong> checkboxes in the Product data panel. Upload your file under the <strong>Downloadable files</strong> section and set a download limit and expiry if needed.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_wcprod ),
	$tag_resolver( 'woocommerce' )
);

// ---- WooCommerce / Orders & Refunds ----
$created += (int) (bool) wzkb_seed_article(
	'How to Process a WooCommerce Order',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-orders-25/1200/630\" alt=\"Process Order\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>New orders appear under <strong>WooCommerce &rarr; Orders</strong>. Open an order to see customer details, line items, and shipping address. Change the status to <em>Processing</em> once payment is confirmed, and to <em>Completed</em> after fulfilment.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_orders ),
	$tag_resolver( 'woocommerce', 'orders' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Issue a Refund in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-refund-26/1200/630\" alt=\"Refund\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Open the order, scroll to the <strong>Order items</strong> section, and click <strong>Refund</strong>. Enter the quantity and amount to refund per line item, then click <strong>Refund via [Gateway]</strong> for an automatic gateway refund or <strong>Refund manually</strong> to record it without triggering the gateway.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_orders ),
	$tag_resolver( 'woocommerce', 'orders', 'payments' )
);
$created += (int) (bool) wzkb_seed_article(
	'Understanding WooCommerce Order Statuses',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-orderstatus-27/1200/630\" alt=\"Order Statuses\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WooCommerce includes seven default order statuses: Pending payment, Failed, Processing, Completed, On hold, Cancelled, and Refunded. Each triggers different emails and stock actions.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Add custom statuses using the <code>register_post_status()</code> function or a plugin like WooCommerce Order Status Manager.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_orders ),
	$tag_resolver( 'woocommerce', 'orders' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Export WooCommerce Orders to CSV',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-exportorders-28/1200/630\" alt=\"Export Orders\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WooCommerce does not include a built-in CSV export for orders. Use the free <em>WooCommerce Customer / Order / Coupon Export</em> plugin or a premium option like <em>Order Export &amp; Order Import for WooCommerce</em>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Alternatively, WP-CLI can generate exports: <code>wp wc shop_order list --format=csv &gt; orders.csv</code></p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_orders ),
	$tag_resolver( 'woocommerce', 'orders', 'plugins' )
);

// ---- WooCommerce / Payments ----
$created += (int) (bool) wzkb_seed_article(
	'How to Set Up Stripe in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-stripe-29/1200/630\" alt=\"Stripe\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Install the official <em>WooCommerce Stripe Payment Gateway</em> plugin. Go to <strong>WooCommerce &rarr; Settings &rarr; Payments</strong>, enable Stripe, and enter your Publishable and Secret keys from the Stripe dashboard.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Enable webhooks in Stripe so that payment events are reliably synced back to WooCommerce order statuses.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_pay ),
	$tag_resolver( 'woocommerce', 'payments' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Configure PayPal in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-paypal-30/1200/630\" alt=\"PayPal\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WooCommerce includes PayPal Standard out of the box. For the modern PayPal Commerce Platform, install the <em>WooPayments</em> or official PayPal plugin. Enter your Client ID and Secret from the PayPal developer portal.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_pay ),
	$tag_resolver( 'woocommerce', 'payments' )
);
$created += (int) (bool) wzkb_seed_article(
	'Enabling Buy Now Pay Later in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-bnpl-31/1200/630\" alt=\"Buy Now Pay Later\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Services like Klarna, Afterpay, and Affirm integrate with WooCommerce via their official plugins. Enabling BNPL can increase average order value and reduce cart abandonment.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Check eligibility requirements for each provider before enabling — many require a minimum monthly sales volume.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_pay ),
	$tag_resolver( 'woocommerce', 'payments' )
);

// ---- WooCommerce / Shipping ----
$created += (int) (bool) wzkb_seed_article(
	'Setting Up Shipping Zones in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-shipzones-32/1200/630\" alt=\"Shipping Zones\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>WooCommerce &rarr; Settings &rarr; Shipping &rarr; Shipping zones</strong>. Add a zone for each geographic area, then add shipping methods (Flat rate, Free shipping, Local pickup) to each zone.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_ship ),
	$tag_resolver( 'woocommerce', 'shipping' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Offer Free Shipping in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-freeship-33/1200/630\" alt=\"Free Shipping\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Add a <em>Free shipping</em> method to a shipping zone and set a minimum order amount. Alternatively create a free shipping coupon for targeted promotions.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Display a free shipping progress bar in the cart to incentivise higher spend.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_ship ),
	$tag_resolver( 'woocommerce', 'shipping' )
);
$created += (int) (bool) wzkb_seed_article(
	'Configuring WooCommerce Tax Settings',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-tax-34/1200/630\" alt=\"Tax Settings\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Enable taxes at <strong>WooCommerce &rarr; Settings &rarr; General</strong>. Then configure rates under the <strong>Tax</strong> tab. You can import tax rate tables via CSV or use the <em>WooCommerce Tax</em> (Jetpack) plugin for automated rates.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_ship ),
	$tag_resolver( 'woocommerce', 'shipping' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Print Shipping Labels in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-labels-35/1200/630\" alt=\"Shipping Labels\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Install <em>WooCommerce Shipping</em> (powered by Shippo or EasyPost) to print USPS, UPS, or DHL labels directly from the order edit screen at discounted rates.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>For UK stores, Royal Mail Click and Drop integrates with WooCommerce via API.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_ship ),
	$tag_resolver( 'woocommerce', 'shipping', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'Using WooCommerce Table Rate Shipping',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-tablerate-36/1200/630\" alt=\"Table Rate Shipping\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Table Rate Shipping lets you define shipping costs based on weight, order total, item count, or destination. Install the <em>WooCommerce Table Rate Shipping</em> extension and add rules to each shipping zone.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_ship ),
	$tag_resolver( 'woocommerce', 'shipping' )
);

// ---- WooCommerce / Coupons ----
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Coupon in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-coupon-37/1200/630\" alt=\"Coupon\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Go to <strong>Marketing &rarr; Coupons &rarr; Add coupon</strong>. Enter a code, choose the discount type (percentage, fixed cart, or fixed product), set the amount, and optionally add usage restrictions or an expiry date.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_coup ),
	$tag_resolver( 'woocommerce' )
);
$created += (int) (bool) wzkb_seed_article(
	'WooCommerce Coupon Restrictions and Usage Limits Explained',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-couponlimits-38/1200/630\" alt=\"Coupon Limits\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Under the <strong>Usage restriction</strong> tab you can limit a coupon to specific products, categories, or a minimum spend. Under <strong>Usage limits</strong> set how many times a coupon can be used in total or per customer.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_coup ),
	$tag_resolver( 'woocommerce' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a BOGO Coupon in WooCommerce',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wc-bogo-39/1200/630\" alt=\"BOGO Coupon\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WooCommerce does not support Buy One Get One natively. Use the free <em>Smart Coupons</em> plugin or <em>WooCommerce BOGO</em> extension to set up buy-one-get-one or gift-with-purchase promotions.</p><!-- /wp:paragraph -->",
	array( $cat_wc, $s_coup ),
	$tag_resolver( 'woocommerce', 'plugins' )
);

// ---- Plugins / SEO ----
$created += (int) (bool) wzkb_seed_article(
	'Getting Started with Yoast SEO',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/yoast-40/1200/630\" alt=\"Yoast SEO\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Install Yoast SEO and run the configuration wizard. Set your site type, organisation details, and social profiles. The plugin adds a Yoast metabox to every post and page for writing SEO titles, meta descriptions, and analysing readability.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_seo ),
	$tag_resolver( 'seo', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create an XML Sitemap with Yoast SEO',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/yoast-sitemap-41/1200/630\" alt=\"XML Sitemap\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Yoast SEO generates an XML sitemap automatically. Find it at <code>yourdomain.com/sitemap_index.xml</code>. Submit it to Google Search Console and Bing Webmaster Tools to accelerate indexing.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Customise which post types and taxonomies are included under <strong>Yoast SEO &rarr; Settings &rarr; Content types</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_seo ),
	$tag_resolver( 'seo', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'Getting Started with Rank Math SEO',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/rankmath-42/1200/630\" alt=\"Rank Math\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Rank Math is a feature-rich SEO plugin with a free tier that includes schema markup, keyword tracking, and a built-in 404 monitor. Run the Setup Wizard and connect your Google Search Console account.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_seo ),
	$tag_resolver( 'seo', 'plugins' )
);

// ---- Plugins / Page Builders ----
$created += (int) (bool) wzkb_seed_article(
	'Getting Started with Elementor',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/elementor-43/1200/630\" alt=\"Elementor\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Elementor is a drag-and-drop page builder. Install it, then click <strong>Edit with Elementor</strong> on any page. Choose a template or start from scratch by dragging widgets onto the canvas.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Use Elementor's global fonts and colours settings to keep your design consistent across all pages.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_pb ),
	$tag_resolver( 'plugins', 'themes' )
);
$created += (int) (bool) wzkb_seed_article(
	'Getting Started with Beaver Builder',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/beaverbuilder-44/1200/630\" alt=\"Beaver Builder\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Beaver Builder is a developer-friendly page builder with clean, semantic output and a strong template library. Activate it and click <strong>Launch Beaver Builder</strong> on any page or post.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_pb ),
	$tag_resolver( 'plugins', 'themes' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use Divi Builder in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/divi-45/1200/630\" alt=\"Divi Builder\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Divi Builder (by Elegant Themes) works as a standalone plugin or bundled with the Divi theme. Click <strong>Use Divi Builder</strong> on any post or page to launch the visual editor with rows, columns, and modules.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_pb ),
	$tag_resolver( 'plugins', 'themes' )
);

// ---- Plugins / WebberZone ----
$created += (int) (bool) wzkb_seed_article(
	'Getting Started with Contextual Related Posts',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/crp-46/1200/630\" alt=\"Contextual Related Posts\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Contextual Related Posts by WebberZone displays related posts based on the content of the current post. Install and activate it, then configure the display settings under <strong>Related Posts &rarr; Settings</strong>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Use the included widget, shortcode, or block to place related posts anywhere on your site.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_wbz ),
	$tag_resolver( 'plugins', 'hooks' )
);
$created += (int) (bool) wzkb_seed_article(
	'Setting Up Top 10 Popular Posts Plugin',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/top10-47/1200/630\" alt=\"Top 10\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Top 10 by WebberZone tracks post views and displays popular posts lists. After activation, configure the tracker type (JavaScript or PHP) under <strong>Top 10 &rarr; Settings &rarr; General</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_wbz ),
	$tag_resolver( 'plugins', 'caching' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use the Better Search Plugin',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/bettersearch-48/1200/630\" alt=\"Better Search\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Better Search by WebberZone replaces WordPress's default search with a relevance-based engine using MySQL FULLTEXT indexes. Configure searchable post types and weight factors under <strong>Better Search &rarr; Settings</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_wbz ),
	$tag_resolver( 'plugins', 'mysql', 'seo' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Display a Knowledge Base with WebberZone Knowledgebase',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wzkb-49/1200/630\" alt=\"WebberZone Knowledgebase\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WebberZone Knowledgebase creates a fully searchable help centre on your WordPress site. After activation, add articles as <em>Knowledge Base</em> posts, organise them with sections, and embed the index with the <code>[knowledgebase]</code> shortcode or block.</p><!-- /wp:paragraph -->",
	array( $cat_plg, $s_wbz ),
	$tag_resolver( 'plugins', 'shortcodes' )
);

// ---- Themes / Block Editor ----
$created += (int) (bool) wzkb_seed_article(
	'How to Use the WordPress Block Editor (Gutenberg)',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/gutenberg-50/1200/630\" alt=\"Block Editor\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>The block editor (Gutenberg) replaces the classic TinyMCE editor. Every piece of content is a block — paragraphs, headings, images, lists, and embeds. Click the <strong>+</strong> button to insert a new block.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Use <strong>/</strong> to quickly search for a block by name without leaving the keyboard.</p><!-- /wp:paragraph -->",
	array( $cat_thd, $s_block ),
	$tag_resolver( 'gutenberg', 'block-editor' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Reusable Block in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/reusableblock-51/1200/630\" alt=\"Reusable Block\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Select any block, click the three-dot menu, and choose <strong>Create reusable block</strong>. Name it and save. You can then insert it anywhere on your site — changes sync everywhere it is used.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Manage all reusable blocks at <strong>Appearance &rarr; Patterns</strong> (WordPress 6.3+) or via the block inserter.</p><!-- /wp:paragraph -->",
	array( $cat_thd, $s_block ),
	$tag_resolver( 'gutenberg', 'block-editor' )
);
$created += (int) (bool) wzkb_seed_article(
	'Using the WordPress Site Editor for Full Site Editing',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/siteeditor-52/1200/630\" alt=\"Site Editor\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Full Site Editing (FSE) allows you to design every part of your site — headers, footers, archive templates — using the block editor. Requires a block theme. Access it via <strong>Appearance &rarr; Editor</strong>.</p><!-- /wp:paragraph -->",
	array( $cat_thd, $s_block ),
	$tag_resolver( 'gutenberg', 'block-editor', 'themes' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Edit a Classic Theme in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/classictheme-53/1200/630\" alt=\"Classic Theme\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Classic themes are customised via <strong>Appearance &rarr; Customize</strong> (the Customizer). Edit colours, fonts, header images, and widget areas. For code changes, always use a child theme to preserve modifications through updates.</p><!-- /wp:paragraph -->",
	array( $cat_thd, $s_classic ),
	$tag_resolver( 'themes', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Create a Child Theme in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/childtheme-54/1200/630\" alt=\"Child Theme\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Create a new folder in <code>wp-content/themes/</code> and add a <code>style.css</code> with the <code>Template:</code> header pointing to the parent theme. Add a <code>functions.php</code> that enqueues the parent stylesheet.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>add_action( 'wp_enqueue_scripts', function() {\n    wp_enqueue_style(\n        'parent-style',\n        get_template_directory_uri() . '/style.css'\n    );\n} );</code></pre><!-- /wp:code -->",
	array( $cat_thd, $s_classic ),
	$tag_resolver( 'themes', 'php', 'hooks' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Add Custom CSS in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/customcss-55/1200/630\" alt=\"Custom CSS\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Add custom CSS via <strong>Appearance &rarr; Customize &rarr; Additional CSS</strong> for classic themes, or via the Site Editor's style panel for block themes. For production use, enqueue a custom stylesheet in your child theme's <code>functions.php</code>.</p><!-- /wp:paragraph -->",
	array( $cat_thd, $s_css ),
	$tag_resolver( 'themes', 'php' )
);

// ---- Performance & Security / Caching ----
$created += (int) (bool) wzkb_seed_article(
	'How to Set Up WP Rocket Caching Plugin',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wprocket-56/1200/630\" alt=\"WP Rocket\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>WP Rocket is a premium caching plugin that enables page caching, GZIP compression, browser caching, and lazy loading out of the box with minimal configuration.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>After activation, configure the cache lifespan, enable minification of CSS and JS, and activate the CDN integration if you use one.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Configure W3 Total Cache',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/w3tc-57/1200/630\" alt=\"W3 Total Cache\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>W3 Total Cache is a free, feature-rich caching plugin. Enable Page Cache, Browser Cache, and Object Cache. Use Disk Enhanced for page caching on shared hosting, or Memcached/Redis on VPS environments.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'Setting Up a CDN with WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/cdn-58/1200/630\" alt=\"CDN\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>A Content Delivery Network (CDN) serves static assets from servers close to your visitors, reducing latency. Cloudflare, BunnyCDN, and KeyCDN all work well with WordPress.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Integrate a CDN URL in your caching plugin's CDN settings or use the free Cloudflare plugin to proxy your entire site.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Enable Object Caching with Redis in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/redis-59/1200/630\" alt=\"Redis\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Object caching stores database query results in memory so WordPress does not repeat expensive queries on every page load. Install Redis on your server and the <em>Redis Object Cache</em> plugin, then add the constants to <code>wp-config.php</code>.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>define( 'WP_REDIS_HOST', '127.0.0.1' );\ndefine( 'WP_REDIS_PORT', 6379 );</code></pre><!-- /wp:code -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Minify CSS and JavaScript in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/minify-60/1200/630\" alt=\"Minification\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Minification removes whitespace and comments from CSS and JS files to reduce their size. WP Rocket, Autoptimize, and LiteSpeed Cache all include minification options.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Always test thoroughly after enabling JS minification — it can break scripts that rely on specific execution order.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Lazy Load Images in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/lazyload-61/1200/630\" alt=\"Lazy Load\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Lazy loading defers off-screen images until the user scrolls to them. WordPress 5.5+ adds the <code>loading=\"lazy\"</code> attribute automatically. Verify it is working with Chrome DevTools Network tab.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_cache ),
	$tag_resolver( 'performance', 'caching' )
);

// ---- Performance & Security / Backups ----
$created += (int) (bool) wzkb_seed_article(
	'How to Back Up WordPress with UpdraftPlus',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/updraftplus-62/1200/630\" alt=\"UpdraftPlus\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>UpdraftPlus is the most popular WordPress backup plugin. Configure scheduled backups of files and the database, and send them automatically to Dropbox, Google Drive, or Amazon S3.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Always store backups off-site — never rely solely on backups held on the same server as your site.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_back ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Restore a WordPress Backup',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/restore-63/1200/630\" alt=\"Restore Backup\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>To restore with UpdraftPlus: go to <strong>Settings &rarr; UpdraftPlus Backups &rarr; Existing Backups</strong>, select the backup set, and click <strong>Restore</strong>. For manual restoration, import the SQL dump via phpMyAdmin and upload the files via FTP.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_back ),
	$tag_resolver( 'security', 'plugins', 'mysql' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Back Up WordPress with WP-CLI',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wpcli-backup-64/1200/630\" alt=\"WP-CLI Backup\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Export the database with <code>wp db export backup.sql</code> and archive the files with <code>tar -czf backup.tar.gz /path/to/wordpress</code>. Schedule both commands via cron for automated daily backups.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_back ),
	$tag_resolver( 'security', 'mysql', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use Jetpack Backup for WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/jetpack-backup-65/1200/630\" alt=\"Jetpack Backup\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Jetpack Backup provides real-time or daily cloud backups with one-click restore. It runs entirely in the cloud so backups continue even if your server is down.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_back ),
	$tag_resolver( 'security', 'plugins' )
);

// ---- Performance & Security / SSL ----
$created += (int) (bool) wzkb_seed_article(
	'How to Force HTTPS in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/forcehttps-66/1200/630\" alt=\"Force HTTPS\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>After installing SSL, update your site URLs in <strong>Settings &rarr; General</strong> to <code>https://</code>. Then add the following to <code>.htaccess</code> to redirect all HTTP traffic:</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>RewriteEngine On\nRewriteCond %{HTTPS} off\nRewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]</code></pre><!-- /wp:code -->",
	array( $cat_ps, $s_ssl ),
	$tag_resolver( 'security', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'Fixing Mixed Content Warnings in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/mixedcontent-67/1200/630\" alt=\"Mixed Content\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Mixed content warnings occur when an HTTPS page loads resources over HTTP. Run <code>wp search-replace 'http://yourdomain.com' 'https://yourdomain.com'</code> to update hardcoded URLs, then check for external HTTP resources in your theme.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_ssl ),
	$tag_resolver( 'security' )
);

// ---- Performance & Security / Malware & Hardening ----
$created += (int) (bool) wzkb_seed_article(
	'How to Harden WordPress Security',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/harden-68/1200/630\" alt=\"Harden WordPress\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Key hardening steps: change the default admin username, use strong passwords, limit login attempts, disable file editing in the dashboard, and keep all software updated.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>define( 'DISALLOW_FILE_EDIT', true );</code></pre><!-- /wp:code -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'php' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Use Wordfence Security Plugin',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/wordfence-69/1200/630\" alt=\"Wordfence\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Wordfence provides a web application firewall, malware scanner, login security (2FA, CAPTCHA), and live traffic monitoring. Install it and run a full scan immediately after activation.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Enable Two-Factor Authentication in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/2fa-70/1200/630\" alt=\"Two-Factor Authentication\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Two-factor authentication (2FA) adds a second verification step to logins. Use the free <em>WP 2FA</em> plugin or enable it via Wordfence or Jetpack. Configure it to require 2FA for Administrator and Editor roles at minimum.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Limit Login Attempts in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/loginlimit-71/1200/630\" alt=\"Limit Login Attempts\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Brute-force attacks try thousands of username/password combinations. Install <em>Limit Login Attempts Reloaded</em> to block IPs after a configurable number of failed attempts. Wordfence and iThemes Security include this feature too.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Change the WordPress Login URL',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/loginurl-72/1200/630\" alt=\"Change Login URL\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Moving <code>wp-login.php</code> to a custom URL reduces automated bot attacks. Use <em>WPS Hide Login</em> or <em>iThemes Security</em> to change the URL without editing core files.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Store the new login URL safely — if you forget it, recover access via WP-CLI: <code>wp user update 1 --user_pass=newpassword</code></p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Scan WordPress for Malware',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/malwarescan-73/1200/630\" alt=\"Malware Scan\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>Use Wordfence, MalCare, or Sucuri Security to scan your WordPress files and database for malware signatures, backdoors, and code injections. Run scans weekly as a minimum.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>If malware is found, clean the infection, update all passwords, regenerate secret keys, and investigate how the breach occurred.</p><!-- /wp:paragraph -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'plugins' )
);
$created += (int) (bool) wzkb_seed_article(
	'How to Disable XML-RPC in WordPress',
	"<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"{$img}/xmlrpc-74/1200/630\" alt=\"XML-RPC\"/></figure><!-- /wp:image -->\n<!-- wp:paragraph --><p>XML-RPC is a legacy remote publishing API frequently targeted by brute-force attacks. If you do not use it, disable it with a filter or block it at the server level.</p><!-- /wp:paragraph -->\n<!-- wp:code --><pre class=\"wp-block-code\"><code>add_filter( 'xmlrpc_enabled', '__return_false' );</code></pre><!-- /wp:code -->",
	array( $cat_ps, $s_mal ),
	$tag_resolver( 'security', 'php', 'hooks' )
);

// ---------------------------------------------------------------------------
// 3. FINAL SUMMARY
// ---------------------------------------------------------------------------
WP_CLI::log( '' );
WP_CLI::success( "Seeder complete. Articles created this run: {$created}" );
WP_CLI::log( '' );
WP_CLI::log( 'Total KB articles : ' . wp_count_posts( 'wz_knowledgebase' )->publish );
WP_CLI::log( 'Total categories  : ' . wp_count_terms( array( 'taxonomy' => 'wzkb_category' ) ) );
WP_CLI::log( 'Total tags        : ' . wp_count_terms( array( 'taxonomy' => 'wzkb_tag' ) ) );
