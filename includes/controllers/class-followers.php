<?php
namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Controller class.
 */
final class Followers extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'vendor_follow_action'    => [
						'base'   => 'vendor_resource',
						'path'   => '/follow',
						'method' => 'POST',
						'action' => [ $this, 'follow_vendor' ],
						'rest'   => true,
					],

					'vendors_unfollow_action' => [
						'base'   => 'vendors_resource',
						'path'   => '/unfollow',
						'method' => 'POST',
						'action' => [ $this, 'unfollow_vendors' ],
						'rest'   => true,
					],

					'listings_feed_page'      => [
						'title'     => esc_html__( 'Feed', 'foo-followers' ),
						'base'      => 'user_account_page',
						'path'      => '/feed',
						'redirect'  => [ $this, 'redirect_feed_page' ],
						'action'    => [ $this, 'render_feed_page' ],
						'paginated' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Follows or unfollows vendor.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function follow_vendor( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->get_by_id( $request->get_param( 'vendor_id' ) );

		if ( ! $vendor || $vendor->get_status() !== 'publish' ) {
			return hp\rest_error( 404 );
		}

		// Get follows.
		$follows = Models\Follow::query()->filter(
			[
				'user'   => get_current_user_id(),
				'vendor' => $vendor->get_id(),
			]
		)->get();

		if ( $follows->count() ) {

			// Delete follows.
			$follows->delete();
		} else {

			// Add new follow.
			$follow = ( new Models\Follow() )->fill(
				[
					'user'   => get_current_user_id(),
					'vendor' => $vendor->get_id(),
				]
			);

			if ( ! $follow->save() ) {
				return hp\rest_error( 400, $follow->_get_errors() );
			}
		}

		return hp\rest_response(
			200,
			[
				'data' => [],
			]
		);
	}

	/**
	 * Unfollows all vendors.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function unfollow_vendors( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Delete follows.
		$follows = Models\Follow::query()->filter(
			[
				'user' => get_current_user_id(),
			]
		)->delete();

		return hp\rest_response(
			200,
			[
				'data' => [],
			]
		);
	}

	/**
	 * Redirects listing feed page.
	 *
	 * @return mixed
	 */
	public function redirect_feed_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check followed vendors.
		if ( ! hivepress()->request->get_context( 'vendor_follow_ids' ) ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders listing feed page.
	 *
	 * @return string
	 */
	public function render_feed_page() {

		// Create listing query.
		$query = Models\Listing::query()->filter(
			[
				'status'     => 'publish',
				'vendor__in' => hivepress()->request->get_context( 'vendor_follow_ids' ),
			]
		)->order( [ 'created_date' => 'desc' ] )
		->limit( get_option( 'hp_listings_per_page' ) )
		->paginate( hivepress()->request->get_page_number() );

		// Set request context.
		hivepress()->request->set_context(
			'post_query',
			$query->get_args()
		);

		// Render page template.
		return ( new Blocks\Template(
			[
				'template' => 'listings_feed_page',

				'context'  => [
					'listings' => [],
				],
			]
		) )->render();
	}
}
