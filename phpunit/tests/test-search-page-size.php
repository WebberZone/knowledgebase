<?php
/**
 * Tests for the knowledge base search results page size.
 *
 * @package WebberZone\Knowledge_Base
 */

/**
 * Search page size tests.
 */
class Test_WZKB_Search_Page_Size extends WP_UnitTestCase {

	/**
	 * Create KB articles containing a word.
	 *
	 * @param int $count Number of articles.
	 */
	protected function create_articles( $count ) {
		self::factory()->post->create_many(
			$count,
			array(
				'post_type'    => 'wz_knowledgebase',
				'post_status'  => 'publish',
				'post_content' => 'sprocket',
			)
		);
	}

	public function test_secondary_query_keeps_its_own_limit() {
		$this->create_articles( 5 );
		$this->go_to( home_url( '/' ) );

		$query = new WP_Query(
			array(
				's'              => 'sprocket',
				'post_type'      => 'wz_knowledgebase',
				'posts_per_page' => 2,
			)
		);

		$this->assertCount( 2, $query->posts );
		$this->assertSame( 2, (int) $query->get( 'posts_per_page' ) );
	}
	public function test_main_search_query_uses_twelve_results() {
		$this->create_articles( 14 );
		$this->go_to( add_query_arg( array( 's' => 'sprocket', 'post_type' => 'wz_knowledgebase' ), home_url( '/' ) ) );

		$this->assertTrue( is_search() );
		$this->assertSame( 12, (int) $GLOBALS['wp_query']->get( 'posts_per_page' ) );
		$this->assertCount( 12, $GLOBALS['wp_query']->posts );
	}
	public function test_rest_search_honours_limit() {
		$this->create_articles( 5 );
		$request = new WP_REST_Request( 'GET', '/wzkb/v1/search' );
		$request->set_param( 'query', 'sprocket' );
		$request->set_param( 'limit', 2 );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $response->get_data() );
	}
}
