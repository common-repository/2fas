<?php

namespace TwoFAS\TwoFAS\Http\Controllers\Ajax;

use Exception;
use TwoFAS\Account\HttpClient\ClientInterface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\Core\Http\Request;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;

class Deactivation_Controller extends Controller {

	/**
	 * @var ClientInterface
	 */
	private $http_client;

	/**
	 * @param Storage         $storage
	 * @param API_Wrapper     $api_wrapper
	 * @param Flash           $flash
	 * @param ClientInterface $http_client
	 */
	public function __construct( Storage $storage, API_Wrapper $api_wrapper, Flash $flash, ClientInterface $http_client ) {
		parent::__construct( $storage, $api_wrapper, $flash );

		$this->http_client = $http_client;
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws Exception
	 */
	public function send_deactivation_reason( Request $request ) {
		if ( $request->has( 'message' ) && '' !== trim( $request->post( 'message' ) ) ) {
			$headers = [ 'Content-Type' => 'application/json' ];
			$data    = [
				'name'    => '2FAS Deactivation',
				'email'   => 'noreply@2fas.com',
				'message' => stripslashes( $request->post( 'message' ) ),
			];

			$this->http_client->request( 'POST', 'https://2fas.com/send-mail', $data, $headers );
		}

		return $this->json( [] );
	}
}
