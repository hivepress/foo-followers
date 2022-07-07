<?php
namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Email class.
 */
class Listing_Feed extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'New Listing', 'foo-followers' ),
				'body'    => esc_html__( 'Hi, %user_name%! There is a new listing "%listing_title%" in your feed, click on the following link to view it: %listing_url%', 'foo-followers' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
