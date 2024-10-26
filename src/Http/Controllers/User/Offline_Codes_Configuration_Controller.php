<?php

namespace TwoFAS\TwoFAS\Http\Controllers\User;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Helpers\Environment;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Integration\Integration_User;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Templates\Twig;
use TwoFAS\TwoFAS\Templates\Views;

class Offline_Codes_Configuration_Controller extends Configuration_Controller {

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @var Environment
	 */
	private $environment;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param Integration_User    $integration_user
	 * @param Flash               $flash
	 * @param Twig                $twig
	 * @param Environment         $environment
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		Integration_User $integration_user,
		Flash $flash,
		Twig $twig,
		Environment $environment,
		Legacy_Mode_Checker $legacy_mode_checker
	) {
		parent::__construct( $storage, $api_wrapper, $integration_user, $flash, $legacy_mode_checker );

		$this->twig        = $twig;
		$this->environment = $environment;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 *
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 * @throws Account_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function show_offline_codes( Request $request ) {
		$user_storage                = $this->storage->get_user_storage();
		$offline_codes               = $user_storage->get_offline_codes();
		$data                        = [];
		$data['quantity']            = isset( $offline_codes['quantity'] ) ? $offline_codes['quantity'] : 0;
		$data['date']                = isset( $offline_codes['time'] ) ? $offline_codes['time'] : null;
		$data['offline_codes_count'] = $this->integration_user->get_backup_codes_count();
		$data['active_tab']          = 'offline_codes';
		$status_data                 = $this->get_user_status_data();
		$data                        = array_merge( $data, $status_data );
		$client                      = $this->api_wrapper->get_client();
		$data['has_client_card']     = $client->hasCard();
		$data['filename']            = '2FAS_offline_codes.txt';

		return $this->view( Views::BACKUP_CODES, $data );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function enable( Request $request ) {
		$user_storage = $this->storage->get_user_storage();
		$user_storage->enable_offline_codes();
		$this->flash->add_message( 'success', 'offline-codes-enabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_OFFLINE_CODES ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 */
	public function disable( Request $request ) {
		$user_storage = $this->storage->get_user_storage();
		$user_storage->disable_offline_codes();
		$this->flash->add_message( 'success', 'offline-codes-disabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_CHANNEL, Action_Index::ACTION_CONFIGURE_OFFLINE_CODES ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 */
	public function generate( Request $request ) {
		$user_storage = $this->storage->get_user_storage();
		$backup_codes = $this->api_wrapper->regenerate_backup_codes( $this->integration_user->get_user() );
		$codes        = $backup_codes->getCodes();

		$offline_codes = [
			'quantity' => count( $codes ),
			'time'     => time(),
		];

		$user_storage->set_offline_codes( $offline_codes );
		$user_storage->enable_offline_codes();

		return $this->json( [ 'codes' => $codes ], 201 );
	}

	/**
	 * @param Request $request
	 */
	public function print_codes( Request $request ) {
		$data             = [];
		$data['title']    = 'Print your 2FAS offline codes';
		$data['app_name'] = $this->environment->get_wordpress_app_name();
		$data['codes']    = $request->post( 'code' );

		echo $this->twig->get_view( Views::PRINT_BACKUP_CODES, $data );
		exit;
	}
}
