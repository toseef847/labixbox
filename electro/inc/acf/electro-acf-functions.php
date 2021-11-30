<?php
/**
 * Electro ACF Functions
 *
 * @package  electro
 */

if ( ! function_exists( 'electro_get_field' ) ) {
	/**
	 * Wrapper for ACF's get_field function.
	 *
	 * @param string   $field custom field key.
	 * @param int|bool $post_id ID of the post.
	 * @param bool     $format_value should format the meta value or not.
	 * @return mixed
	 */
	function electro_get_field( $field, $post_id = false, $format_value = true ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $field, $post_id, $format_value );
		}

		return false;
	}
}

require_once get_template_directory() . '/inc/acf/functions/home-v10-functions.php';
require_once get_template_directory() . '/inc/acf/functions/home-v11-functions.php';