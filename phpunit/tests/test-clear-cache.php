<?php
/**
 * Tests for clearing the knowledge base cache.
 *
 * @package WebberZone\Knowledge_Base
 */

/**
 * Clear cache tests.
 *
 * @group ajax
 */
class Test_WZKB_Clear_Cache extends WP_Ajax_UnitTestCase {

	protected $php_self;

	public function set_up() {
		parent::set_up();
		$this->php_self      = $_SERVER['PHP_SELF'] ?? null;
		$_SERVER['PHP_SELF'] = '/wp-admin/admin-ajax.php';

		// Admin classes only load in wp-admin, so register the AJAX handler here.
		new \WebberZone\Knowledge_Base\Util\Cache();
	}

	public function tear_down() {
		if ( null === $this->php_self ) {
			unset( $_SERVER['PHP_SELF'] );
		} else {
			$_SERVER['PHP_SELF'] = $this->php_self;
		}
		parent::tear_down();
	}

	public function test_settings_page_button_fires_cache_cleared() {
		$this->_setRole( 'administrator' );
		$version = (int) get_option( 'wzkb_rest_cache_version', 1 );

		$_POST['security'] = wp_create_nonce( 'wzkb-admin' );

		try {
			$this->_handleAjax( 'wzkb_clear_cache' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertSame( 1, did_action( 'wzkb_cache_cleared' ) );
		$this->assertSame( $version + 1, (int) get_option( 'wzkb_rest_cache_version', 1 ) );
		$response = json_decode( $this->_last_response, true );
		$this->assertIsArray( $response, $this->_last_response );
		$this->assertTrue( $response['success'] );
	}

	public function test_non_admin_cannot_clear_cache() {
		$this->_setRole( 'editor' );
		$_POST['security'] = wp_create_nonce( 'wzkb-admin' );

		try {
			$this->_handleAjax( 'wzkb_clear_cache' );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->assertSame( 0, did_action( 'wzkb_cache_cleared' ) );
	}
}
