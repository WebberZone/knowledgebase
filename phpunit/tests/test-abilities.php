<?php
/**
 * Tests for the shared Knowledge Base abilities.
 *
 * @package WebberZone\Knowledge_Base
 */

/**
 * Shared Abilities API tests.
 *
 * @group abilities
 */
class Test_WZKB_Abilities extends WP_UnitTestCase {

	protected $parent_section;
	protected $child_section;
	protected $article_id;

	public function set_up() {
		parent::set_up();

		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'Requires the WordPress Abilities API.' );
		}

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->parent_section = self::factory()->term->create(
			array(
				'taxonomy' => 'wzkb_category',
				'name'     => 'Ability Parent Section',
			)
		);
		$this->child_section  = self::factory()->term->create(
			array(
				'taxonomy' => 'wzkb_category',
				'name'     => 'Ability Child Section',
				'parent'   => $this->parent_section,
			)
		);
		$this->article_id     = self::factory()->post->create(
			array(
				'post_type'    => 'wz_knowledgebase',
				'post_status'  => 'publish',
				'post_title'   => 'Jetpack Ability Search Article',
				'post_content' => '<p>Configure Jetpack account access.</p>',
			)
		);

		wp_set_object_terms( $this->article_id, $this->child_section, 'wzkb_category' );
	}

	public function test_search_accepts_section_id_and_slug_and_returns_readable_results() {
		$section = get_term( $this->child_section, 'wzkb_category' );
		$ability = wp_get_ability( 'knowledgebase/search-articles' );

		foreach ( array( $this->child_section, (string) $this->child_section, $section->slug ) as $section_input ) {
			$results = $ability->execute(
				array(
					'query'   => 'Jetpack',
					'section' => $section_input,
				)
			);

			$this->assertIsArray( $results );
			$this->assertCount( 1, $results );
			$this->assertSame( $this->article_id, $results[0]['id'] );
			$this->assertSame( 'Jetpack Ability Search Article', $results[0]['title'] );
			$this->assertSame( get_permalink( $this->article_id ), $results[0]['url'] );
			$this->assertSame( 'Configure Jetpack account access.', $results[0]['excerpt'] );
			$this->assertSame( $this->child_section, $results[0]['section'][0]['id'] );
		}

		$this->assertCount( 1, $ability->execute( array( 'query' => 'Jetpack', 'limit' => '1' ) ) );
	}

	public function test_section_ability_returns_hierarchical_terms_and_parent_filter() {
		$ability = wp_get_ability( 'knowledgebase/get-sections' );
		$tree    = $ability->execute();

		$this->assertIsArray( $tree );
		$this->assertSame( $this->parent_section, $tree[0]['id'] );
		$this->assertSame( $this->child_section, $tree[0]['children'][0]['id'] );
		$this->assertArrayHasKey( 'url', $tree[0] );

		foreach ( array( $this->parent_section, (string) $this->parent_section ) as $parent ) {
			$children = $ability->execute( array( 'parent' => $parent ) );
			$this->assertCount( 1, $children );
			$this->assertSame( $this->child_section, $children[0]['id'] );
		}
	}

	public function test_abilities_reject_invalid_input_and_deny_anonymous_users() {
		$search   = wp_get_ability( 'knowledgebase/search-articles' );
		$sections = wp_get_ability( 'knowledgebase/get-sections' );

		$this->assertWPError( $search->execute( array() ) );
		$this->assertWPError( $search->execute( array( 'query' => 'Jetpack', 'extra' => true ) ) );
		$this->assertWPError( $search->execute( array( 'query' => 'Jetpack', 'limit' => -1 ) ) );
		$this->assertWPError( $search->execute( array( 'query' => 'Jetpack', 'section' => array( 'invalid' ) ) ) );
		$this->assertWPError( $sections->execute( array( 'parent' => -1 ) ) );

		wp_set_current_user( 0 );
		$this->assertWPError( $search->execute( array( 'query' => 'Jetpack' ) ) );
		$this->assertWPError( $sections->execute( array() ) );
	}

	public function test_search_ability_is_discoverable_and_runs_over_rest() {
		$list = rest_get_server()->dispatch( new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities' ) );
		$this->assertSame( 200, $list->get_status() );
		$this->assertContains( 'knowledgebase/search-articles', wp_list_pluck( $list->get_data(), 'name' ) );

		$request = new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/knowledgebase/search-articles/run' );
		$request->set_query_params( array( 'input' => array( 'query' => 'Jetpack' ) ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
		$this->assertSame( $this->article_id, $response->get_data()[0]['id'] );

		$sections_request  = new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/knowledgebase/get-sections/run' );
		$sections_response = rest_get_server()->dispatch( $sections_request );

		$this->assertSame( 200, $sections_response->get_status(), wp_json_encode( $sections_response->get_data() ) );
		$this->assertSame( $this->parent_section, $sections_response->get_data()[0]['id'] );

		$post_request = new WP_REST_Request( 'POST', '/wp-abilities/v1/abilities/knowledgebase/search-articles/run' );
		$this->assertSame( 405, rest_get_server()->dispatch( $post_request )->get_status() );
	}
}
