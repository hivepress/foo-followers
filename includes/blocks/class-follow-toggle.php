<?php
namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Block class.
 */
class Follow_Toggle extends Toggle {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'states' => [
					[
						'icon'    => 'user-plus',
						'caption' => esc_html__( 'Follow', 'foo-followers' ),
					],
					[
						'icon'    => 'user-minus',
						'caption' => esc_html__( 'Unfollow', 'foo-followers' ),
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Get vendor from the block context.
		$vendor = $this->get_context( 'vendor' );

		if ( $vendor ) {

			// Set URL for sending requests on click.
			$this->url = hivepress()->router->get_url(
				'vendor_follow_action',
				[
					'vendor_id' => $vendor->get_id(),
				]
			);

			// Set active state if vendor is followed.
			if ( in_array(
				$vendor->get_id(),
				hivepress()->request->get_context( 'vendor_follow_ids', [] )
			) ) {
				$this->active = true;
			}
		}

		parent::boot();
	}
}
