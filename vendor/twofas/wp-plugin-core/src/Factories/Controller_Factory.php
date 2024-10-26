<?php

namespace TwoFAS\Core\Factories;

use LogicException;
use Psr\Container\ContainerInterface;
use TwoFAS\Core\Http\Controller;

class Controller_Factory {

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * @param string $controller_name
	 *
	 * @return Controller
	 *
	 * @throws LogicException
	 */
	public function create( $controller_name ) {
		if ( ! $this->container->has( $controller_name ) ) {
			throw new LogicException( 'Controller name: ' . $controller_name . ' is not registered in DI container' );
		}

		return $this->container->get( $controller_name );
	}
}
