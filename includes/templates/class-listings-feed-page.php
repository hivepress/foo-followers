<?php
namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template class.
 */
class Listings_Feed_Page extends User_Account_Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'listings'               => [
								'type'    => 'listings',
								'columns' => 2,
								'_order'  => 10,
							],

							'listing_pagination'     => [
								'type'   => 'part',
								'path'   => 'page/pagination',
								'_order' => 20,
							],

							'vendors_unfollow_link'  => [
								'type'   => 'part',
								'path'   => 'vendor/follow/vendors-unfollow-link',
								'_order' => 30,
							],

							'vendors_unfollow_modal' => [
								'title'  => esc_html__( 'Unfollow Vendors', 'foo-followers' ),
								'type'   => 'modal',

								'blocks' => [
									'vendors_unfollow_form' => [
										'type' => 'form',
										'form' => 'vendors_unfollow',
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
