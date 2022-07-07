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
final class Follow extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'vendor_follow_action' => [
						'base'   => 'vendor_resource',
						'path'   => '/follow',
						'method' => 'POST',
						'action' => [ $this, 'follow_vendor' ],
						'rest'   => true,
					],

					'listings_feed_page'   => [
						'title'     => esc_html__( 'Feed', 'foo-followers' ),
						'base'      => 'user_account_page',
						'path'      => '/feed',
						'redirect'  => [ $this, 'redirect_listings_feed_page' ],
						'action'    => [ $this, 'render_listings_feed_page' ],
						'paginated' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Follows vendor.
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

			// Add follow.
			$follow = ( new Models\Follow() )->fill(
				[
					'user'    => get_current_user_id(),
					'listing' => $listing->get_id(),
				]
			);

			if ( ! $follow->save() ) {
				return hp\rest_error( 400, $follow->_get_errors() );
			}
		}

		return hp\rest_response( 200 );
	}

	/**
	 * Redirects listings feed page.
	 *
	 * @return mixed
	 */
	public function redirect_listings_feed_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check listings.
		if ( ! hivepress()->request->get_context( 'vendor_follow_ids' ) ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders listings feed page.
	 *
	 * @return string
	 */
	public function render_listings_feed_page() {

		// Set listing query.
		hivepress()->request->set_context(
			'post_query',
			Models\Listing::query()->filter(
				[
					'status'     => 'publish',
					'vendor__in' => hivepress()->request->get_context( 'vendor_follow_ids' ),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( get_option( 'hp_listings_per_page' ) )
			->paginate( hivepress()->request->get_page_number() )
			->get_args()
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
