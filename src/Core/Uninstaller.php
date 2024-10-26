<?php

namespace TwoFAS\TwoFAS\Core;

use Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Http\Cookie;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\OAuth_Storage;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Update\Migrator;

class Uninstaller {

	/**
	 * @var Migrator
	 */
	private $migrator;

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var OAuth_Storage
	 */
	private $oauth_storage;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @param Migrator                $migrator
	 * @param Storage                 $storage
	 * @param Cookie                  $cookie
	 * @param API_Wrapper             $api_wrapper
	 * @param Error_Handler_Interface $error_handler
	 */
	public function __construct(
		Migrator $migrator,
		Storage $storage,
		Cookie $cookie,
		API_Wrapper $api_wrapper,
		Error_Handler_Interface $error_handler
	) {
		$this->migrator        = $migrator;
		$this->options_storage = $storage->get_options();
		$this->user_storage    = $storage->get_user_storage();
		$this->oauth_storage   = $storage->get_oauth();
		$this->cookie          = $cookie;
		$this->api_wrapper     = $api_wrapper;
		$this->error_handler   = $error_handler;
	}

	public function uninstall() {
		try {
			$this->delete_integration();
		} catch ( Exception $e ) {
			$this->error_handler->capture_exception( $e );
		}

		try {
			$this->rollback_migrations();
		} catch ( Exception $e ) {
			$this->error_handler->capture_exception( $e );
		}

		$this->options_storage->delete_wp_options();
		$this->user_storage->delete_wp_user_meta();
		$this->cookie->delete_plugin_cookies();
	}

	/**
	 * @throws Migration_Exception
	 */
	private function rollback_migrations() {
		$this->migrator->rollback_all();
	}

	/**
	 * @throws Account_Exception
	 * @throws NotFoundException
	 * @throws TokenNotFoundException
	 */
	private function delete_integration() {
		$this->api_wrapper->delete_integration( $this->oauth_storage->get_integration_id() );
	}
}
