<?php
namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Model class.
 */
class Follow extends Comment {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'user'   => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'user_id',
						'_model'   => 'user',
					],

					'vendor' => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'comment_post_ID',
						'_model'   => 'vendor',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
