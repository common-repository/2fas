<?php

namespace TwoFAS\TwoFAS\Listeners;

use Psr\Container\ContainerInterface;

class Listener_Provider {

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var array
	 */
	private $events = [];

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * @param array $events
	 *
	 * @return $this
	 */
	public function add_events( array $events ) {
		$this->events = $events;

		return $this;
	}

	public function listen() {
		foreach ( $this->events as $event => $services ) {
			foreach ( $services as $service ) {
				/** @var Listener $listener */
				$listener = $this->container->get( $service );
				$listener->listen_for( $event );
			}
		}
	}
}
