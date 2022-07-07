<?php
namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Component class.
 */
final class Followers extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set request context for pages.
		add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

		// Add menu item to user account.
		add_filter( 'hivepress/v1/menus/user_account', [ $this, 'add_menu_item' ] );

		// Add toggle block to vendor templates.
		add_filter( 'hivepress/v1/templates/vendor_view_block', [ $this, 'add_toggle_block' ] );
		add_filter( 'hivepress/v1/templates/vendor_view_page', [ $this, 'add_toggle_block' ] );

		// Send emails about a new listing.
		add_action( 'hivepress/v1/models/listing/update_status', [ $this, 'send_feed_emails' ], 10, 4 );

		parent::__construct( $args );
	}

	/**
	 * Sets request context for pages.
	 *
	 * @param array $context Context values.
	 * @return array
	 */
	public function set_request_context( $context ) {

		// Get user ID.
		$user_id = get_current_user_id();

		// Get cached vendor IDs.
		$vendor_ids = hivepress()->cache->get_user_cache( $user_id, 'vendor_follow_ids', 'models/follow' );

		if ( is_null( $vendor_ids ) ) {

			// Get follows.
			$follows = Models\Follow::query()->filter(
				[
					'user' => $user_id,
				]
			)->get();

			// Get vendor IDs.
			$vendor_ids = [];

			foreach ( $follows as $follow ) {
				$vendor_ids[] = $follow->get_vendor__id();
			}

			// Cache vendor IDs.
			hivepress()->cache->set_user_cache( $user_id, 'vendor_follow_ids', 'models/follow', $vendor_ids );
		}

		// Set request context.
		$context['vendor_follow_ids'] = $vendor_ids;

		return $context;
	}

	/**
	 * Adds menu item to user account.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function add_menu_item( $menu ) {
		if ( hivepress()->request->get_context( 'vendor_follow_ids' ) ) {
			$menu['items']['listings_feed'] = [
				'route'  => 'listings_feed_page',
				'_order' => 20,
			];
		}

		return $menu;
	}

	/**
	 * Adds toggle block to vendor templates.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function add_toggle_block( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'vendor_actions_primary' => [
						'blocks' => [
							'vendor_follow_toggle' => [
								'type'       => 'follow_toggle',
								'_order'     => 50,

								'attributes' => [
									'class' => [ 'hp-vendor__action', 'hp-vendor__action--follow' ],
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Sends emails about a new listing.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param object $listing Listing object.
	 */
	public function send_feed_emails( $listing_id, $new_status, $old_status, $listing ) {

		// Check listing status.
		if ( 'publish' !== $new_status || ! in_array( $old_status, [ 'auto-draft', 'pending' ] ) ) {
			return;
		}

		// Get follows.
		$follows = Models\Follow::query()->filter(
			[
				'vendor' => $listing->get_vendor__id(),
			]
		)->get();

		foreach ( $follows as $follow ) {

			// Get user.
			$user = $follow->get_user();

			// Send email.
			( new Emails\Listing_Feed(
				[
					'recipient' => $user->get_email(),

					'tokens'    => [
						'user_name'     => $user->get_display_name(),
						'listing_title' => $listing->get_title(),
						'listing_url'   => hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ),
					],
				]
			) )->send();
		}
	}
}
