<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Templates\Twig;
use TwoFAS\TwoFAS\Templates\Views;

class Admin_Notices_Action implements Hook_Interface {

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Twig $twig
	 */
	public function __construct( Twig $twig ) {
		$this->twig = $twig;
	}

	public function register_hook() {
		add_action( 'admin_notices', [ $this, 'render_notices' ], 20 );
	}

	public function render_notices() {
		echo $this->twig->get_view( Views::NOTICES );
	}
}
