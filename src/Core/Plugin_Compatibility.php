<?php

namespace TwoFAS\TwoFAS\Core;

use TwoFAS\Core\Http\Request;

class Plugin_Compatibility {

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function is_external_login( Request $request ) {
		$host           = $request->header( 'HTTP_HOST' );
		$request_uri    = $request->header( 'REQUEST_URI' );
		$requested_path = $host . $request_uri;
		$wp_login_path  = preg_replace( '/https?:\/\//', '', wp_login_url() );

		return false === strpos( $requested_path, $wp_login_path );
	}

	/**
	 * @param Request $request
	 *
	 * @return string
	 */
	public function get_redirect_to_url( Request $request ) {
		$url         = wp_login_url();
		$redirect_to = '';
		$wc_redirect = $request->post( 'redirect' );
		$remember_me = $request->request( 'rememberme' );

		if ( $wc_redirect ) {
			$redirect_to = $wc_redirect;
		} elseif ( function_exists( 'wc_get_page_permalink' ) ) {
			$redirect_to = wc_get_page_permalink( 'myaccount' );
		}

		if ( $redirect_to ) {
			$redirect_to = urlencode( $redirect_to );
			$url         = add_query_arg( 'redirect_to', $redirect_to, $url );
		}

		if ( $remember_me ) {
			$url = add_query_arg( 'rememberme', $remember_me, $url );
		}

		return $url;
	}
}
