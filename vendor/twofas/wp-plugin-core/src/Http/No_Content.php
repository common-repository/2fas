<?php

namespace TwoFAS\Core\Http;

class No_Content extends View_Response {

	public function __construct() {
		parent::__construct( '', array() );
	}
}
