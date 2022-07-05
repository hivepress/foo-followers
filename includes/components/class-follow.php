<?php
/**
 * Follow component.
 *
 * @package HivePress\Components
 */
// todo
namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Follow component class.
 *
 * @class Follow
 */
final class Follow extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set follows.
		add_action( 'init', [ $this, 'set_follows' ], 100 );

		// Alter account menu.
		add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_user_account_menu' ] );

		// Alter templates.
		add_filter( 'hivepress/v1/templates/vendor_view_block', [ $this, 'alter_vendor_view_block' ] );
		add_filter( 'hivepress/v1/templates/vendor_view_page', [ $this, 'alter_vendor_view_page' ] );

		parent::__construct( $args );
	}

	/**
	 * Sets follows.
	 */
	public function set_follows() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Set query.
		$query = Models\Follow::query()->filter(
			[
				'user' => get_current_user_id(),
			]
		)->order( [ 'added_date' => 'desc' ] );

		// Get cached IDs.
		$follow_ids = hivepress()->cache->get_user_cache( get_current_user_id(), array_merge( $query->get_args(), [ 'fields' => 'vendor_ids' ] ), 'models/follow' );

		if ( is_null( $follow_ids ) ) {

			// Get follow IDs.
			$follow_ids = array_map(
				function( $follow ) {
					return $follow->get_vendor__id();
				},
				$query->get()->serialize()
			);

			// Cache IDs.
			if ( count( $follow_ids ) <= 1000 ) {
				hivepress()->cache->set_user_cache( get_current_user_id(), array_merge( $query->get_args(), [ 'fields' => 'vendor_ids' ] ), 'models/follow', $follow_ids );
			}
		}

		// Set request context.
		hivepress()->request->set_context( 'follow_ids', $follow_ids );
	}

	/**
	 * Alters account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_account_menu( $menu ) {
		if ( hivepress()->request->get_context( 'follow_ids' ) ) {
			$menu['items']['listings_follow'] = [
				'route'  => 'listings_follow_page',
				'_order' => 20,
			];
		}

		return $menu;
	}

	/**
	 * Alters listing view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_block( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_actions_primary' => [
						'blocks' => [
							'listing_follow_toggle' => [
								'type'       => 'follow_toggle',
								'view'       => 'icon',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__action', 'hp-listing__action--follow' ],
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'listing_actions_secondary' => [
						'blocks' => [
							'listing_follow_toggle' => [
								'type'       => 'follow_toggle',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__action', 'hp-listing__action--follow' ],
								],
							],
						],
					],
				],
			]
		);
	}
}
