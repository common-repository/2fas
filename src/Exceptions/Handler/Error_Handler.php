<?php

namespace TwoFAS\TwoFAS\Exceptions\Handler;

use Exception;
use RuntimeException;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Exceptions\Handler\Logger_Interface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Exceptions\Authentication_Expired_Exception;
use TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception;
use TwoFAS\TwoFAS\Exceptions\DB_Exception;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Exceptions\Session_Exception;
use TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception;
use TwoFAS\TwoFAS\Notifications\Notification;
use TwoFAS\TwoFAS\Templates\Views;
use WP_Error;

class Error_Handler implements Error_Handler_Interface {

	/**
	 * @var Logger_Interface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $logging_allowed;

	/**
	 * @var array
	 */
	private $dont_log = [
		'TwoFAS\Api\Exception\AuthorizationException',
		'TwoFAS\Account\Exception\AuthorizationException',
		'TwoFAS\TwoFAS\Exceptions\Authentication_Expired_Exception',
		'TwoFAS\TwoFAS\Exceptions\Authentication_Limit_Reached_Exception',
		'TwoFAS\TwoFAS\Exceptions\User_Not_Found_Exception'
	];

	/**
	 * @param Logger_Interface $logger
	 * @param bool             $logging_allowed
	 */
	public function __construct( Logger_Interface $logger, $logging_allowed ) {
		$this->logger          = $logger;
		$this->logging_allowed = $logging_allowed;
	}

	/**
	 * @param Exception $e
	 * @param array     $options
	 *
	 * @return Error_Handler
	 */
	public function capture_exception( Exception $e, array $options = [] ) {
		if ( $this->logging_allowed && $this->can_log( $e ) ) {
			$this->logger->capture_exception( $e, $options );
		}

		return $this;
	}

	/**
	 * @param Exception $e
	 *
	 * @return JSON_Response
	 */
	public function to_json( Exception $e ) {
		$response = $this->create_response( $e );

		return new JSON_Response( [ 'error' => $response['message'] ], $response['status'] );
	}

	/**
	 * @param Exception $e
	 *
	 * @return View_Response
	 */
	public function to_view( Exception $e ) {
		$response = $this->create_response( $e );

		return new View_Response( Views::ERROR, [ 'description' => $response['message'] ] );
	}

	/**
	 * @param Exception $e
	 *
	 * @return WP_Error
	 */
	public function to_wp_error( Exception $e ) {
		$response = $this->create_response( $e );

		return new WP_Error( 'twofas_login_error', $response['message'] );
	}

	/**
	 * @param Exception $e
	 * @param string    $class
	 *
	 * @return string
	 */
	public function to_notification( Exception $e, $class = 'notice notice-error error twofas-error-notice' ) {
		$response = $this->create_response( $e );

		return "
		<div class='{$class}'>
			<p>{$response['message']}</p>
		</div>";
	}

	/**
	 * @param string $message
	 * @param int    $status
	 *
	 * @return array
	 */
	private function to_array( $message, $status ) {
		return [
			'message' => $message,
			'status'  => $status
		];
	}

	/**
	 * @param Exception $e
	 *
	 * @return bool
	 */
	private function can_log( Exception $e ) {
		foreach ( $this->dont_log as $excluded_exception ) {
			if ( $e instanceof $excluded_exception ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param Exception $e
	 *
	 * @return array
	 */
	private function create_response( Exception $e ) {
		if ( $e instanceof Authentication_Expired_Exception ) {
			return $this->to_array( $this->get_message_by_key( 'authentication-expired' ), 403 );
		}

		if ( $e instanceof Authentication_Limit_Reached_Exception ) {
			return $this->to_array( $this->get_message_by_key( 'authentication-limit' ), 403 );
		}

		if ( $e instanceof User_Not_Found_Exception ) {
			return $this->to_array( $this->get_message_by_key( 'user-not-found' ), 404 );
		}

		if ( $e instanceof TokenNotFoundException ) {
			return $this->to_array( $this->get_message_by_key( 'oauth-token-not-found' ), 404 );
		}

		if ( $e instanceof NotFoundException ) {
			return $this->to_array( $this->get_message_by_key( 'entity-not-found' ), 404 );
		}

		if ( $e instanceof Twig_Error_Loader ) {
			return $this->to_array( $this->get_message_by_key( 'template-not-found' ), 500 );
		}

		if ( $e instanceof Twig_Error_Syntax ) {
			return $this->to_array( $this->get_message_by_key( 'template-compilation' ), 500 );
		}

		if ( $e instanceof Twig_Error_Runtime ) {
			return $this->to_array( $this->get_message_by_key( 'template-rendering' ), 500 );
		}

		if ( $e instanceof Session_Exception ) {
			return $this->to_array( $this->map_message( $e ), 500 );
		}

		if ( $e instanceof API_Exception ) {
			return $this->to_array( $this->get_message_by_key( 'default' ), 500 );
		}

		if ( $e instanceof Account_Exception ) {
			return $this->to_array( $this->get_message_by_key( 'default' ), 500 );
		}

		if ( $e instanceof DB_Exception ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof Migration_Exception ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof RuntimeException ) {
			return $this->to_array( $this->map_message( $e ), 500 );
		}

		return $this->to_array( $this->get_message_by_key( 'default' ), 500 );
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function get_message_by_key( $key ) {
		return Notification::get( $key );
	}

	/**
	 * @param Exception $e
	 *
	 * @return string
	 */
	private function map_message( Exception $e ) {
		return Notification::get( $e->getMessage() );
	}
}
