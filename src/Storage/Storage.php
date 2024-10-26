<?php

namespace TwoFAS\TwoFAS\Storage;

use TwoFAS\TwoFAS\Http\Cookie;

class Storage {

	/**
	 * @var Options_Storage
	 */
	private $options_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var OAuth_Storage
	 */
	private $oauth_storage;

	/**
	 * @var Session_Storage_Interface
	 */
	private $session_storage;

	/**
	 * @var Authentication_Storage
	 */
	private $authentication_storage;

	/**
	 * @var Trusted_Devices_Storage
	 */
	private $trusted_devices_storage;

	/**
	 * @param Cookie                    $cookie
	 * @param Options_Storage           $options_storage
	 * @param User_Storage              $user_storage
	 * @param OAuth_Storage             $oauth_storage
	 * @param Session_Storage_Interface $session_storage
	 * @param Authentication_Storage    $authentication_storage
	 * @param Trusted_Devices_Storage   $trusted_devices_storage
	 */
	public function __construct(
		Cookie $cookie,
		Options_Storage $options_storage,
		User_Storage $user_storage,
		OAuth_Storage $oauth_storage,
		Session_Storage_Interface $session_storage,
		Authentication_Storage $authentication_storage,
		Trusted_Devices_Storage $trusted_devices_storage
	) {
		$this->cookie                  = $cookie;
		$this->options_storage         = $options_storage;
		$this->user_storage            = $user_storage;
		$this->oauth_storage           = $oauth_storage;
		$this->session_storage         = $session_storage;
		$this->authentication_storage  = $authentication_storage;
		$this->trusted_devices_storage = $trusted_devices_storage;
	}

	/**
	 * @return Options_Storage
	 */
	public function get_options() {
		return $this->options_storage;
	}

	/**
	 * @return User_Storage
	 */
	public function get_user_storage() {
		return $this->user_storage;
	}

	/**
	 * @return Cookie
	 */
	public function get_cookie() {
		return $this->cookie;
	}

	/**
	 * @return OAuth_Storage
	 */
	public function get_oauth() {
		return $this->oauth_storage;
	}

	/**
	 * @return Session_Storage_Interface
	 */
	public function get_session_storage() {
		return $this->session_storage;
	}

	/**
	 * @return Authentication_Storage
	 */
	public function get_authentication_storage() {
		return $this->authentication_storage;
	}

	/**
	 * @return Trusted_Devices_Storage
	 */
	public function get_trusted_devices_storage() {
		return $this->trusted_devices_storage;
	}
}
