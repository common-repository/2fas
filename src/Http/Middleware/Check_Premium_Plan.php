<?php

namespace TwoFAS\TwoFAS\Http\Middleware;

use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\TwoFAS\Storage\Options_Storage;

class Check_Premium_Plan extends Middleware {

	/**
	 * @var Options_Storage
	 */
	private $options;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param Options_Storage $options
	 * @param Flash           $flash
	 */
	public function __construct( Options_Storage $options, Flash $flash ) {
		$this->options = $options;
		$this->flash   = $flash;
	}

	/**
	 * @return null|Redirect_Response
	 */
	public function handle() {
		if ( $this->options->is_plan_basic() ) {
			$this->flash->add_message( 'error', 'premium-only' );

			return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL ) );
		}

		return $this->next->handle();
	}
}
