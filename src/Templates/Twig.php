<?php

namespace TwoFAS\TwoFAS\Templates;

use Exception;
use Twig_Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;

class Twig {

	/**
	 * @var Twig_Environment
	 */
	private $twig;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @param Twig_Environment $twig
	 * @param Error_Handler_Interface    $error_handler
	 */
	public function __construct( Twig_Environment $twig, Error_Handler_Interface $error_handler ) {
		$this->twig          = $twig;
		$this->error_handler = $error_handler;
	}

	/**
	 * @param string $template_name
	 * @param array  $params
	 *
	 * @return string
	 */
	public function get_view( $template_name, array $params = [] ) {
		try {
			return $this->try_render( $template_name, $params );
		} catch ( Exception $e ) {
			return $this->error_handler->capture_exception( $e )->to_notification( $e );
		}
	}

	/**
	 * @param string $template_name
	 * @param array  $params
	 *
	 * @return string
	 *
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function try_render( $template_name, array $params = [] ) {
		$params['assets_url'] = TWOFAS_ASSETS_URL;
		$params['login_url']  = wp_login_url();

		return $this->twig->render( $template_name, $params );
	}
}
