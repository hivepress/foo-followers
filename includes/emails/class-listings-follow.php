<?php
/**
 * Request find email.
 *
 * @package HivePress\Emails
 */
// todo
namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request find email class.
 *
 * @class Request_Find
 */
class Request_Find extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Requests Available', 'hivepress-requests' ),
				'description' => esc_html__( 'This email is sent to users when there are new requests available.', 'hivepress-requests' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
				'tokens'      => [ 'user_name', 'requests_url', 'user' ],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Requests Available', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! There are new requests available, click on the following link to view them: %requests_url%', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
