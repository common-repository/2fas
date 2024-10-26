<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Templates\Views;
use WhichBrowser\Parser;

class Trusted_Devices_Controller extends Configuration_Controller {

	/**
	 * @var Parser
	 */
	private $browser;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param Integration_User    $integration_user
	 * @param Flash               $flash
	 * @param Parser              $browser
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		Integration_User $integration_user,
		Flash $flash,
		Parser $browser,
		Legacy_Mode_Checker $legacy_mode_checker
	) {
		parent::__construct( $storage, $api_wrapper, $integration_user, $flash, $legacy_mode_checker );

		$this->browser = $browser;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 *
	 * @throws Account_Exception
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function show_trusted_devices( Request $request ) {
		$trusted_devices_storage           = $this->storage->get_trusted_devices_storage();
		$user_storage                      = $this->storage->get_user_storage();
		$user_id                           = $user_storage->get_user_id();
		$trusted_devices                   = $trusted_devices_storage->get_trusted_devices( $user_id );
		$data                              = [];
		$data['trusted_devices']           = $this->prepare( $trusted_devices );
		$status_data                       = $this->get_user_status_data();
		$data                              = array_merge( $data, $status_data );
		$client                            = $this->api_wrapper->get_client();
		$data['offline_codes_count']       = $this->integration_user->get_backup_codes_count();
		$data['has_client_card']           = $client->hasCard();
		$data['is_current_device_trusted'] = $trusted_devices_storage->is_device_trusted( $user_id );
		$data['active_tab']                = 'trusted_devices';

		return $this->view( Views::TRUSTED_DEVICES, $data );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function add_trusted_device( Request $request ) {
		$trusted_devices_storage = $this->storage->get_trusted_devices_storage();
		$user_storage            = $this->storage->get_user_storage();
		$user_id                 = $user_storage->get_user_id();

		if ( ! $trusted_devices_storage->is_device_trusted( $user_id ) ) {
			$trusted_devices_storage->add_trusted_device( $user_id );
			$this->flash->add_message( 'success', 'trusted-device-added' );
		} else {
			$this->flash->add_message( 'error', 'trusted-device-already-added' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_DISPLAY_TRUSTED_DEVICES ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function remove_trusted_device( Request $request ) {
		$device_id               = $request->post( 'device_id' );
		$user_storage            = $this->storage->get_user_storage();
		$trusted_devices_storage = $this->storage->get_trusted_devices_storage();

		$trusted_devices_storage->remove_trusted_device( $user_storage->get_user_id(), $device_id );
		$this->flash->add_message( 'success', 'trusted-device-removed' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_DISPLAY_TRUSTED_DEVICES ) );
	}

	/**
	 * @param array $trusted_devices
	 *
	 * @return array
	 */
	private function prepare( array $trusted_devices ) {
		$list = [];

		foreach ( $trusted_devices as $device_data ) {
			$this->browser->analyse( $device_data['user_agent'] );
			$device_id      = $device_data['device_id'];
			$ip             = $device_data['ip'];
			$browser        = $this->browser->toString();
			$added_on       = $device_data['created_at'];
			$last_logged_in = $device_data['last_logged_in'];

			$list[ $device_id ] = [
				'ip'             => $ip,
				'browser'        => $browser,
				'last_logged_in' => $last_logged_in,
				'added_on'       => $added_on,
			];
		}

		return $list;
	}
}
