<?php

use Psr\Container\ContainerInterface;
use TwoFAS\Core\Http\Middleware\Middleware_Bag;
use WhichBrowser\Parser;
use TwoFAS\Core\Http\Route;
use TwoFAS\TwoFAS\Http\Cookie;
use TwoFAS\TwoFAS\Http\Middleware\Check_Account_Exists;
use TwoFAS\TwoFAS\Http\Middleware\Check_Account_Not_Exists;
use TwoFAS\TwoFAS\Http\Middleware\Check_Ajax;
use TwoFAS\TwoFAS\Http\Middleware\Check_Integration_User;
use TwoFAS\TwoFAS\Http\Middleware\Check_Nonce;
use TwoFAS\TwoFAS\Http\Middleware\Check_Premium_Plan;
use TwoFAS\TwoFAS\Http\Middleware\Check_Second_Factor_Enabled;
use TwoFAS\TwoFAS\Http\Middleware\Check_User_Has_Read_Capability;
use TwoFAS\TwoFAS\Http\Middleware\Check_User_Is_Admin;
use TwoFAS\TwoFAS\Http\Middleware\Check_Trusted_Devices_Enabled;
use TwoFAS\Core\Http\Request;

return [
	Cookie::class         => DI\object()
		->constructor( $_COOKIE ),
	Request::class        => DI\object( TwoFAS\TwoFAS\Http\Request::class )
		->constructor(
			$_GET,
			$_POST,
			$_SERVER,
			DI\get( Cookie::class )
		),
	Route::class          => DI\object()
		->constructor(
			DI\get( Request::class ),
			DI\get( 'routes' )
		),
	Parser::class         => DI\factory( function ( ContainerInterface $c ) {
		return new Parser( $c->get( Request::class )->header( 'HTTP_USER_AGENT' ) );
	} ),
	Middleware_Bag::class => DI\factory( function ( ContainerInterface $c ) {
		$middleware_bag = new Middleware_Bag();

		$middleware_bag->add_middleware( 'account_exists', $c->get( Check_Account_Exists::class ) );
		$middleware_bag->add_middleware( 'account_not_exists', $c->get( Check_Account_Not_Exists::class ) );
		$middleware_bag->add_middleware( 'ajax', $c->get( Check_Ajax::class ) );
		$middleware_bag->add_middleware( 'nonce', $c->get( Check_Nonce::class ) );
		$middleware_bag->add_middleware( 'premium_plan', $c->get( Check_Premium_Plan::class ) );
		$middleware_bag->add_middleware( 'user', $c->get( Check_User_Has_Read_Capability::class ) );
		$middleware_bag->add_middleware( 'admin', $c->get( Check_User_Is_Admin::class ) );
		$middleware_bag->add_middleware( 'integration_user', $c->get( Check_Integration_User::class ) );
		$middleware_bag->add_middleware( 'second_factor_enabled', $c->get( Check_Second_Factor_Enabled::class ) );
		$middleware_bag->add_middleware( 'trusted_devices_enabled', $c->get( Check_Trusted_Devices_Enabled::class ) );

		return $middleware_bag;
	} )
];
