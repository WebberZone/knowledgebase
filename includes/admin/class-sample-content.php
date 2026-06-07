<?php
/**
 * Sample content for the setup wizard.
 *
 * @since 3.0.0
 *
 * @package WebberZone\Knowledge_Base
 */

namespace WebberZone\Knowledge_Base\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Sample Content class.
 *
 * Provides demo sections and articles for the setup wizard.
 *
 * @since 3.0.0
 */
class Sample_Content {

	/**
	 * Return the sample content dataset.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $multi_product Whether multi-product mode is active.
	 * @return array{products: array, sections: array, articles: array}
	 */
	public static function get_data( bool $multi_product ): array {
		return $multi_product ? self::multi_product_data() : self::single_product_data();
	}

	/**
	 * Dataset for single-product mode: 2 sections, 2 articles each.
	 *
	 * @return array
	 */
	private static function single_product_data(): array {
		return array(
			'products' => array(),
			'sections' => array(
				array(
					'name'         => __( 'Getting Started', 'knowledgebase' ),
					'slug'         => 'getting-started',
					'description'  => __( 'Everything you need to begin.', 'knowledgebase' ),
					'product_slug' => '',
				),
				array(
					'name'         => __( 'User Guide', 'knowledgebase' ),
					'slug'         => 'user-guide',
					'description'  => __( 'Detailed guides for day-to-day use.', 'knowledgebase' ),
					'product_slug' => '',
				),
			),
			'articles' => array(
				array(
					'title'        => __( 'Welcome and Overview', 'knowledgebase' ),
					'meta_slug'    => 'sample-welcome-and-overview',
					'section_slug' => 'getting-started',
					'content'      => self::article_welcome_overview(),
				),
				array(
					'title'        => __( 'Installation Guide', 'knowledgebase' ),
					'meta_slug'    => 'sample-installation-guide',
					'section_slug' => 'getting-started',
					'content'      => self::article_installation(),
				),
				array(
					'title'        => __( 'Basic Configuration', 'knowledgebase' ),
					'meta_slug'    => 'sample-basic-configuration',
					'section_slug' => 'user-guide',
					'content'      => self::article_basic_config(),
				),
				array(
					'title'        => __( 'Advanced Features', 'knowledgebase' ),
					'meta_slug'    => 'sample-advanced-features',
					'section_slug' => 'user-guide',
					'content'      => self::article_advanced_features(),
				),
			),
		);
	}

	/**
	 * Dataset for multi-product mode: 2 products, 2 sections per product, 2 articles per section.
	 *
	 * @return array
	 */
	private static function multi_product_data(): array {
		return array(
			'products' => array(
				array(
					'name'        => __( 'Nova', 'knowledgebase' ),
					'slug'        => 'nova',
					'description' => __( 'Documentation for Nova users.', 'knowledgebase' ),
				),
				array(
					'name'        => __( 'Nexus', 'knowledgebase' ),
					'slug'        => 'nexus',
					'description' => __( 'Documentation for Nexus developers.', 'knowledgebase' ),
				),
			),
			'sections' => array(
				array(
					'name'         => __( 'Getting Started', 'knowledgebase' ),
					'slug'         => 'cs-getting-started',
					'description'  => __( 'First steps for new users.', 'knowledgebase' ),
					'product_slug' => 'nova',
				),
				array(
					'name'         => __( 'Account Management', 'knowledgebase' ),
					'slug'         => 'cs-account-management',
					'description'  => __( 'Profile and billing information.', 'knowledgebase' ),
					'product_slug' => 'nova',
				),
				array(
					'name'         => __( 'API Reference', 'knowledgebase' ),
					'slug'         => 'dev-api-reference',
					'description'  => __( 'Endpoints, authentication, and responses.', 'knowledgebase' ),
					'product_slug' => 'nexus',
				),
				array(
					'name'         => __( 'Integration Guides', 'knowledgebase' ),
					'slug'         => 'dev-integration-guides',
					'description'  => __( 'Step-by-step integration walkthroughs.', 'knowledgebase' ),
					'product_slug' => 'nexus',
				),
			),
			'articles' => array(
				array(
					'title'        => __( 'Welcome to Nova', 'knowledgebase' ),
					'meta_slug'    => 'sample-cs-welcome',
					'section_slug' => 'cs-getting-started',
					'content'      => self::article_cs_welcome(),
				),
				array(
					'title'        => __( 'How to Submit a Support Ticket', 'knowledgebase' ),
					'meta_slug'    => 'sample-cs-submit-ticket',
					'section_slug' => 'cs-getting-started',
					'content'      => self::article_cs_ticket(),
				),
				array(
					'title'        => __( 'Managing Your Profile', 'knowledgebase' ),
					'meta_slug'    => 'sample-cs-manage-profile',
					'section_slug' => 'cs-account-management',
					'content'      => self::article_cs_profile(),
				),
				array(
					'title'        => __( 'Billing and Subscriptions', 'knowledgebase' ),
					'meta_slug'    => 'sample-cs-billing',
					'section_slug' => 'cs-account-management',
					'content'      => self::article_cs_billing(),
				),
				array(
					'title'        => __( 'Authentication', 'knowledgebase' ),
					'meta_slug'    => 'sample-dev-authentication',
					'section_slug' => 'dev-api-reference',
					'content'      => self::article_dev_auth(),
				),
				array(
					'title'        => __( 'Making API Requests', 'knowledgebase' ),
					'meta_slug'    => 'sample-dev-api-requests',
					'section_slug' => 'dev-api-reference',
					'content'      => self::article_dev_requests(),
				),
				array(
					'title'        => __( 'Quick Start Guide', 'knowledgebase' ),
					'meta_slug'    => 'sample-dev-quick-start',
					'section_slug' => 'dev-integration-guides',
					'content'      => self::article_dev_quickstart(),
				),
				array(
					'title'        => __( 'Webhook Integration', 'knowledgebase' ),
					'meta_slug'    => 'sample-dev-webhooks',
					'section_slug' => 'dev-integration-guides',
					'content'      => self::article_dev_webhooks(),
				),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Block content helpers
	// -------------------------------------------------------------------------

	/**
	 * Build an unordered list block.
	 *
	 * @param string[] $items List items (may contain inline HTML).
	 * @return string
	 */
	private static function ul( array $items ): string {
		$inner = '';
		foreach ( $items as $item ) {
			$inner .= '<!-- wp:list-item --><li>' . $item . '</li><!-- /wp:list-item -->';
		}
		return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$inner}</ul>\n<!-- /wp:list -->";
	}

	/**
	 * Build an ordered list block.
	 *
	 * @param string[] $items List items (may contain inline HTML).
	 * @return string
	 */
	private static function ol( array $items ): string {
		$inner = '';
		foreach ( $items as $item ) {
			$inner .= '<!-- wp:list-item --><li>' . $item . '</li><!-- /wp:list-item -->';
		}
		return "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">{$inner}</ol>\n<!-- /wp:list -->";
	}

	/**
	 * Build a paragraph block.
	 *
	 * @param string $text Paragraph content (may contain inline HTML).
	 * @return string
	 */
	private static function p( string $text ): string {
		return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->";
	}

	/**
	 * Build an h2 heading block.
	 *
	 * @param string $text Heading text.
	 * @return string
	 */
	private static function h2( string $text ): string {
		return "<!-- wp:heading {\"level\":2} -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->";
	}

	/**
	 * Build a code block.
	 *
	 * @param string $text Code content.
	 * @return string
	 */
	private static function code( string $text ): string {
		return "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>{$text}</code></pre>\n<!-- /wp:code -->";
	}

	/**
	 * Join block strings with double newlines.
	 *
	 * @param string[] $parts Block strings.
	 * @return string
	 */
	private static function blocks( array $parts ): string {
		return implode( "\n\n", $parts );
	}

	// -------------------------------------------------------------------------
	// Single-product articles
	// -------------------------------------------------------------------------

	/**
	 * Article: Welcome and Overview.
	 *
	 * @return string
	 */
	private static function article_welcome_overview(): string {
		return self::blocks(
			array(
				self::p( 'Welcome to your knowledge base. This is a sample article — feel free to edit or delete it once you have added your own content.' ),
				self::p( 'Use the sidebar navigation to browse sections, or type a keyword into the search bar at the top of the page to find specific topics.' ),
				self::h2( 'How to use this knowledge base' ),
				self::ul(
					array(
						'Browse sections in the sidebar to find relevant guides.',
						'Use the search bar to look up specific topics by keyword.',
						'Click any article title to read the full content.',
					)
				),
			)
		);
	}

	/**
	 * Article: Installation Guide.
	 *
	 * @return string
	 */
	private static function article_installation(): string {
		return self::blocks(
			array(
				self::p( 'Follow these steps to install the plugin on your WordPress site.' ),
				self::h2( 'Requirements' ),
				self::ul(
					array(
						'WordPress 6.7 or later',
						'PHP 7.4 or later',
						'Administrator access to your site',
					)
				),
				self::h2( 'Installation steps' ),
				self::ol(
					array(
						'Go to <strong>Plugins → Add New</strong> in your WordPress dashboard.',
						'Search for the plugin by name.',
						'Click <strong>Install Now</strong>, then <strong>Activate</strong>.',
						'The setup wizard launches automatically on first activation.',
					)
				),
			)
		);
	}

	/**
	 * Article: Basic Configuration.
	 *
	 * @return string
	 */
	private static function article_basic_config(): string {
		return self::blocks(
			array(
				self::p( 'After activating the plugin, visit <strong>Knowledge Base → Settings</strong> to configure it for your site.' ),
				self::h2( 'Key settings' ),
				self::ul(
					array(
						'<strong>Slug</strong> — the URL prefix for your knowledge base archive.',
						'<strong>Columns</strong> — how many section columns to display on the archive page.',
						'<strong>Show excerpt</strong> — whether article previews appear beneath section headings.',
					)
				),
				self::h2( 'Flushing permalinks' ),
				self::p( 'After changing the slug, go to <strong>Settings → Permalinks</strong> and click <strong>Save Changes</strong> to update your site\'s rewrite rules.' ),
			)
		);
	}

	/**
	 * Article: Advanced Features.
	 *
	 * @return string
	 */
	private static function article_advanced_features(): string {
		return self::blocks(
			array(
				self::p( 'Beyond the basics, several advanced features are available to enhance the reader experience.' ),
				self::h2( 'Table of contents' ),
				self::p( 'Enable <strong>Show table of contents</strong> in Display Options to automatically generate a TOC at the top of each article from its headings.' ),
				self::h2( 'Live search' ),
				self::p( 'Turn on <strong>Enable live search</strong> to add a real-time search bar to the knowledge base archive. Results appear as the user types.' ),
				self::h2( 'Related articles' ),
				self::p( 'Enable <strong>Show related articles</strong> to display a list of similar articles at the bottom of each article page.' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Multi-product: Starter articles
	// -------------------------------------------------------------------------

	/**
	 * Article: Welcome to Nova.
	 *
	 * @return string
	 */
	private static function article_cs_welcome(): string {
		return self::blocks(
			array(
				self::p( 'Welcome to the Nova knowledge base. Here you will find answers to common questions, account management guides, and instructions for resolving typical issues.' ),
				self::p( 'If you cannot find what you need, our support team is available via the contact form linked in the site navigation.' ),
				self::h2( 'Where to start' ),
				self::ul(
					array(
						'Browse sections in the sidebar to find relevant guides.',
						'Use the search bar to look up specific topics.',
						'Check <strong>Account Management</strong> for help with your profile and billing.',
					)
				),
			)
		);
	}

	/**
	 * Article: How to Submit a Support Ticket.
	 *
	 * @return string
	 */
	private static function article_cs_ticket(): string {
		return self::blocks(
			array(
				self::p( 'If you need help that is not covered in this knowledge base, you can submit a support ticket directly from your account dashboard.' ),
				self::h2( 'Steps to submit a ticket' ),
				self::ol(
					array(
						'Log in to your account and click <strong>Support</strong> in the top navigation.',
						'Click <strong>New Ticket</strong>.',
						'Select the relevant product or service from the dropdown.',
						'Describe your issue and attach any relevant screenshots.',
						'Click <strong>Submit</strong>. You will receive a confirmation email with your ticket number.',
					)
				),
				self::p( 'Typical response times are within one business day. Track your ticket status from the Support dashboard.' ),
			)
		);
	}

	/**
	 * Article: Managing Your Profile.
	 *
	 * @return string
	 */
	private static function article_cs_profile(): string {
		return self::blocks(
			array(
				self::p( 'You can update your account information at any time from the account settings page.' ),
				self::h2( 'What you can update' ),
				self::ul(
					array(
						'<strong>Name and email</strong> — updated under <em>Account → Profile</em>.',
						'<strong>Password</strong> — changed under <em>Account → Security</em>. Use at least 12 characters.',
						'<strong>Notification preferences</strong> — managed under <em>Account → Notifications</em>.',
					)
				),
				self::p( 'If you update your email address, a confirmation link is sent to the new address before the change is applied.' ),
			)
		);
	}

	/**
	 * Article: Billing and Subscriptions.
	 *
	 * @return string
	 */
	private static function article_cs_billing(): string {
		return self::blocks(
			array(
				self::p( 'All billing and subscription details are managed from the Billing section of your account dashboard.' ),
				self::h2( 'Managing your subscription' ),
				self::ul(
					array(
						'<strong>Upgrade or downgrade</strong> — click <em>Change Plan</em> and select a new tier.',
						'<strong>Cancel</strong> — click <em>Cancel Subscription</em>. Access continues until the end of the current billing period.',
						'<strong>Update payment method</strong> — click <em>Payment Methods</em> to add or replace a card.',
					)
				),
				self::p( 'Invoices are available to download at any time under <em>Billing → Invoice History</em>.' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Multi-product: Professional articles
	// -------------------------------------------------------------------------

	/**
	 * Article: Authentication.
	 *
	 * @return string
	 */
	private static function article_dev_auth(): string {
		return self::blocks(
			array(
				self::p( 'All API requests must be authenticated using a Bearer token passed in the <code>Authorization</code> header.' ),
				self::h2( 'Obtaining a token' ),
				self::ol(
					array(
						'Log in to the developer portal and navigate to <strong>API Keys</strong>.',
						'Click <strong>Generate New Key</strong>.',
						'Copy the key — it is only shown once. Store it securely.',
					)
				),
				self::h2( 'Sending the token' ),
				self::code( 'Authorization: Bearer YOUR_API_KEY' ),
				self::p( 'Never expose your API key in client-side code or public repositories.' ),
			)
		);
	}

	/**
	 * Article: Making API Requests.
	 *
	 * @return string
	 */
	private static function article_dev_requests(): string {
		return self::blocks(
			array(
				self::p( 'All API endpoints accept and return JSON over HTTPS. Include the <code>Accept: application/json</code> header with every request.' ),
				self::h2( 'Base URL' ),
				self::code( 'https://api.example.com/v1' ),
				self::h2( 'Example request' ),
				self::code( "GET /v1/articles HTTP/1.1\nHost: api.example.com\nAuthorization: Bearer YOUR_API_KEY\nAccept: application/json" ),
				self::p( 'A successful response returns a <code>200 OK</code> status with a JSON body.' ),
			)
		);
	}

	/**
	 * Article: Quick Start Guide.
	 *
	 * @return string
	 */
	private static function article_dev_quickstart(): string {
		return self::blocks(
			array(
				self::p( 'This guide covers the minimum steps required to integrate with the API and make your first successful request.' ),
				self::h2( 'Step 1 — Obtain your API key' ),
				self::p( 'Follow the <strong>Authentication</strong> article in the API Reference section to generate a key and understand how to pass it in requests.' ),
				self::h2( 'Step 2 — Verify connectivity' ),
				self::p( 'Send a <code>GET</code> request to <code>/v1/ping</code>. A <code>200 OK</code> response with <code>{"status":"ok"}</code> confirms you are authenticated and connected.' ),
				self::h2( 'Step 3 — Fetch your first resource' ),
				self::p( 'Send a <code>GET</code> request to <code>/v1/articles</code> to retrieve a paginated list of articles. Use <code>per_page</code> and <code>page</code> query parameters to navigate results.' ),
			)
		);
	}

	/**
	 * Article: Webhook Integration.
	 *
	 * @return string
	 */
	private static function article_dev_webhooks(): string {
		return self::blocks(
			array(
				self::p( 'Webhooks let your application receive real-time notifications when events occur, without polling the API.' ),
				self::h2( 'Registering an endpoint' ),
				self::ol(
					array(
						'In the developer portal, go to <strong>Webhooks → Add Endpoint</strong>.',
						'Enter the public HTTPS URL of your listener.',
						'Select the event types you want to subscribe to.',
						'Save. A signing secret is generated — store it securely.',
					)
				),
				self::h2( 'Verifying payloads' ),
				self::p( 'Each webhook request includes an <code>X-Signature</code> header. Verify it against an HMAC-SHA256 hash of the raw request body using your signing secret. Reject any request where the signatures do not match.' ),
			)
		);
	}
}
