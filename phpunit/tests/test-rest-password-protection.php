<?php
/**
 * Tests for password protection in Knowledge Base REST responses.
 *
 * @package WebberZone\Knowledge_Base
 */

/**
 * Password protection regression tests.
 */
class Test_WZKB_REST_Password_Protection extends WP_UnitTestCase {

	public function test_password_cookie_does_not_unlock_other_visitors() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'wz_knowledgebase',
				'post_status'  => 'publish',
				'post_title'   => 'Password regression article',
				'post_content' => 'wzkbpasswordmarker confidential body',
				'post_password' => 'LocalSecret!2026',
			)
		);
		wp_set_current_user( 0 );
		$cookie_name = 'wp-postpass_' . COOKIEHASH;
		$old_cookie  = $_COOKIE[ $cookie_name ] ?? null;
		$request     = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase/' . $post_id );
		$list        = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase' );
		$list->set_param( 'per_page', 50 );
		$search = new WP_REST_Request( 'GET', '/wzkb/v1/search' );
		$search->set_param( 'query', 'regression' );

		try {
			unset( $_COOKIE[ $cookie_name ] );
			$locked = rest_get_server()->dispatch( $request );
			$this->assertSame( 200, $locked->get_status() );
			$this->assertSame( '', $locked->get_data()['content'] );
			$this->assertSame( '', $locked->get_data()['excerpt'] );
			$this->assertTrue( $locked->get_data()['protected'] );

			wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$hasher                  = new PasswordHash( 8, true );
			$_COOKIE[ $cookie_name ] = $hasher->HashPassword( 'LocalSecret!2026' );
			$unlocked                = rest_get_server()->dispatch( $request );
			$this->assertStringContainsString( 'wzkbpasswordmarker', $unlocked->get_data()['content'] );
			foreach ( array( $list, $search ) as $collection ) {
				$response = rest_get_server()->dispatch( $collection );
				$items    = array_column( $response->get_data(), null, 'id' );
				$this->assertStringContainsString( 'wzkbpasswordmarker', $items[ $post_id ]['excerpt'] );
			}

			unset( $_COOKIE[ $cookie_name ] );
			$this->assertSame( '', rest_get_server()->dispatch( $request )->get_data()['content'] );
			foreach ( array( $list, $search ) as $collection ) {
				$response = rest_get_server()->dispatch( $collection );
				$items    = array_column( $response->get_data(), null, 'id' );
				$this->assertSame( '', $items[ $post_id ]['excerpt'] );
			}
		} finally {
			if ( null === $old_cookie ) {
				unset( $_COOKIE[ $cookie_name ] );
			} else {
				$_COOKIE[ $cookie_name ] = $old_cookie;
			}
		}
	}

	public function test_adding_a_password_invalidates_open_responses() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'wz_knowledgebase',
				'post_status'  => 'publish',
				'post_title'   => 'Password transition article',
				'post_content' => 'wzkbtransitionmarker confidential body',
			)
		);
		wp_set_current_user( 0 );
		$single = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase/' . $post_id );
		$list   = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase' );
		$list->set_param( 'search', 'transition' );
		$this->assertStringContainsString( 'wzkbtransitionmarker', wp_json_encode( rest_get_server()->dispatch( $single )->get_data() ) );
		$this->assertStringContainsString( 'wzkbtransitionmarker', wp_json_encode( rest_get_server()->dispatch( $list )->get_data() ) );

		wp_update_post( array( 'ID' => $post_id, 'post_password' => 'new-password' ) );

		$this->assertSame( '', rest_get_server()->dispatch( $single )->get_data()['content'] );
		$this->assertStringNotContainsString( 'wzkbtransitionmarker', wp_json_encode( rest_get_server()->dispatch( $list )->get_data() ) );
	}
	public function test_public_collection_does_not_cache_private_articles() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'private', 'post_content' => 'wzkbprivatemarker' ) );
		$request = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		rest_get_server()->dispatch( $request );
		wp_set_current_user( 0 );
		$response = rest_get_server()->dispatch( $request );
		$this->assertNotContains( $post_id, array_column( $response->get_data(), 'id' ) );
	}

	public function test_related_rest_does_not_cache_unlocked_body_images() {
		$source = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish' ) );
		$protected = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish', 'post_password' => 'secret', 'post_content' => '<img src="https://example.com/wzkbprotectedimage.png" />' ) );
		$request = new WP_REST_Request( 'GET', '/wzkb/v1/related' );
		$request->set_param( 'post_id', $source );
		$request->set_param( 'limit', 5 );
		wp_set_current_user( 0 );
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new PasswordHash( 8, true );
		$cookie_name = 'wp-postpass_' . COOKIEHASH;
		$old_cookie = $_COOKIE[ $cookie_name ] ?? null;
		try {
			$_COOKIE[ $cookie_name ] = $hasher->HashPassword( 'secret' );
			$unlocked = rest_get_server()->dispatch( $request );
			$this->assertStringContainsString( 'wzkbprotectedimage', $unlocked->get_data() );
			unset( $_COOKIE[ $cookie_name ] );
			$this->assertStringNotContainsString( 'wzkbprotectedimage', rest_get_server()->dispatch( $request )->get_data() );
		} finally {
			if ( null === $old_cookie ) {
				unset( $_COOKIE[ $cookie_name ] );
			} else {
				$_COOKIE[ $cookie_name ] = $old_cookie;
			}
		}
	}

	public function test_route_filters_and_taxonomy_parameters_cannot_unlock_content() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish', 'post_content' => 'wzkbparammarker', 'post_password' => 'secret' ) );
		$section = self::factory()->term->create( array( 'taxonomy' => 'wzkb_category' ) );
		$product = self::factory()->term->create( array( 'taxonomy' => 'wzkb_product' ) );
		wp_set_object_terms( $post_id, array( $section ), 'wzkb_category' );
		wp_set_object_terms( $post_id, array( $product ), 'wzkb_product' );
		add_filter( 'wzkb_rest_route_permission', '__return_true' );
		foreach ( array( 0, self::factory()->user->create( array( 'role' => 'subscriber' ) ), self::factory()->user->create( array( 'role' => 'contributor' ) ) ) as $user_id ) {
			wp_set_current_user( $user_id );
			foreach ( array( '', 'en', 'fr' ) as $language ) {
				$request = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase/' . $post_id );
				$request->set_param( 'lang', $language );
				$request->set_param( 'context', 'edit' );
				$request->set_param( 'password', 'secret' );
				$this->assertSame( '', rest_get_server()->dispatch( $request )->get_data()['content'] );
				$list = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase' );
				$list->set_param( 'product', $product );
				$list->set_param( 'section', $section );
				$list->set_param( 'per_page', 1 );
				$list->set_param( 'page', 1 );
				$list->set_param( 'lang', $language );
				$response = rest_get_server()->dispatch( $list );
				$this->assertSame( 200, $response->get_status() );
				$this->assertSame( '', $response->get_data()[0]['excerpt'] );
				$this->assertSame( '1', $response->get_headers()['X-WP-Total'] );
			}
		}
	}

	public function test_password_removal_restores_content() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish', 'post_content' => 'wzkbremovedpassword', 'post_password' => 'secret' ) );
		wp_set_current_user( 0 );
		$request = new WP_REST_Request( 'GET', '/wzkb/v1/knowledgebase/' . $post_id );
		$this->assertSame( '', rest_get_server()->dispatch( $request )->get_data()['content'] );
		wp_update_post( array( 'ID' => $post_id, 'post_password' => '' ) );
		$this->assertStringContainsString( 'wzkbremovedpassword', rest_get_server()->dispatch( $request )->get_data()['content'] );
	}

	public function test_attached_image_fallback_does_not_expose_locked_images() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish', 'post_password' => 'secret' ) );
		$attachment = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => $post_id, 'post_mime_type' => 'image/jpeg' ) );
		update_post_meta( $attachment, '_wp_attached_file', 'wzkbsecretattachment.jpg' );
		wp_set_current_user( 0 );
		$html = \WebberZone\Knowledge_Base\Frontend\Media_Handler::get_the_post_thumbnail( array( 'post' => $post_id ) );
		$this->assertStringNotContainsString( 'wzkbsecretattachment', $html );
		wp_update_post( array( 'ID' => $post_id, 'post_password' => '' ) );
		$html = \WebberZone\Knowledge_Base\Frontend\Media_Handler::get_the_post_thumbnail( array( 'post' => $post_id ) );
		$this->assertStringContainsString( 'wzkbsecretattachment', $html );
	}

	public function test_section_query_refreshes_when_an_article_gains_a_password() {
		$section = self::factory()->term->create( array( 'taxonomy' => 'wzkb_category' ) );
		$id = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish', 'post_excerpt' => 'wzkbstalesectionmarker' ) );
		wp_set_object_terms( $id, array( $section ), 'wzkb_category' );
		wp_set_current_user( 0 );
		$property = new ReflectionProperty( \WebberZone\Knowledge_Base\Frontend\Display::class, 'options_cache' );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		$previous = $property->getValue();
		$property->setValue( null, array( 'cache' => true ) );
		try {
			$term = get_term( $section );
			$query = \WebberZone\Knowledge_Base\Frontend\Display::get_term_posts( $term );
			$this->assertSame( '', $query->posts[0]->post_password );
			wp_update_post( array( 'ID' => $id, 'post_password' => 'secret' ) );
			$query = \WebberZone\Knowledge_Base\Frontend\Display::get_term_posts( $term );
			$this->assertSame( 'secret', $query->posts[0]->post_password );
			$this->assertStringNotContainsString( 'wzkbstalesectionmarker', get_the_excerpt( $query->posts[0] ) );
		} finally {
			$property->setValue( null, $previous );
		}
	}

	public function test_section_query_cache_survives_a_new_request_without_an_object_cache() {
		global $wpdb;

		$section = self::factory()->term->create( array( 'taxonomy' => 'wzkb_category' ) );
		$id      = self::factory()->post->create( array( 'post_type' => 'wz_knowledgebase', 'post_status' => 'publish' ) );
		wp_set_object_terms( $id, array( $section ), 'wzkb_category' );
		$property = new ReflectionProperty( \WebberZone\Knowledge_Base\Frontend\Display::class, 'options_cache' );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		$previous = $property->getValue();
		$property->setValue( null, array( 'cache' => true ) );
		$meta_ids = static function () use ( $wpdb, $section ) {
			return $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->termmeta} WHERE term_id = %d AND meta_key LIKE %s ORDER BY meta_id", $section, '\_wzkb\_cache\_%' ) );
		};
		try {
			$term = get_term( $section );
			\WebberZone\Knowledge_Base\Frontend\Display::get_term_posts( $term );
			$first = $meta_ids();
			$this->assertNotEmpty( $first );

			// A new request on a site without a persistent object cache starts with an empty object cache.
			wp_cache_flush();
			\WebberZone\Knowledge_Base\Frontend\Display::get_term_posts( $term );
			$this->assertSame( $first, $meta_ids() );
		} finally {
			$property->setValue( null, $previous );
		}
	}

}
