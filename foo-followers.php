<?php
/**
 * Plugin Name: Followers for HivePress
 * Description: Allow users to follow vendors.
 * Version: 1.0.0
 * Author: Foo
 * Author URI: https://example.com/
 * Text Domain: foo-followers
 * Domain Path: /languages/
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register extension directory.
add_filter(
	'hivepress/v1/extensions',
	function( $extensions ) {
		$extensions[] = __DIR__;

		return $extensions;
	}
);
