<?php

namespace TwoFAS\TwoFAS\Helpers;

class URL {

	/**
	 * @param string $page
	 * @param string $action
	 * @param array  $other_parameters
	 *
	 * @return string
	 */
	public static function create( $page, $action = '', array $other_parameters = [] ) {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( 'page', $page, $url );

		if ( $action ) {
			$url = add_query_arg( 'twofas-action', $action, $url );
		}

		$url = add_query_arg( $other_parameters, $url );

		return $url;
	}

	/**
	 * @param string $page
	 * @param string $action
	 * @param array  $other_parameters
	 *
	 * @return string
	 */
	public static function create_with_nonce( $page, $action, array $other_parameters = [] ) {
		$url = self::create( $page, $action, $other_parameters );

		return wp_nonce_url( $url, $action );
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	public static function create_form_nonce( $action ) {
		$nonce_field = '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( $action ) . '" />';
		$nonce_field .= wp_referer_field( false );

		return $nonce_field;
	}
}
