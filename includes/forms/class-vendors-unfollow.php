<?php
namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form class.
 */
class Vendors_Unfollow extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Are you sure you want to unfollow all vendors?', 'foo-followers' ),
				'action'      => hivepress()->router->get_url( 'vendors_unfollow_action' ),
				'method'      => 'POST',
				'redirect'    => true,

				'button'      => [
					'label' => esc_html__( 'Unfollow', 'foo-followers' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
