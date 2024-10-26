<?php

namespace TwoFAS\Core\Http;

class Safe_Redirect_Response extends Redirect_Response {

	public function redirect() {
		nocache_headers();
		wp_safe_redirect( $this->url->get() );
		exit;
	}

}
