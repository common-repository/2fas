<?php

use Psr\Container\ContainerInterface;
use TwoFAS\TwoFAS\Authentication\Handler\Login_Handler;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\TwoFAS\Authentication\Handler\Configuration_Confirmation;
use TwoFAS\TwoFAS\Authentication\Handler\Configuration_Reset;
use TwoFAS\TwoFAS\Authentication\Handler\Login_Configuration;
use TwoFAS\TwoFAS\Authentication\Handler\Standard_Login;
use TwoFAS\TwoFAS\Authentication\Login_Process;
use TwoFAS\TwoFAS\Authentication\Middleware\Login_Stop;
use TwoFAS\TwoFAS\Authentication\Middleware\Trusted_Device_Login;
use TwoFAS\TwoFAS\Authentication\Middleware\External_Login;
use TwoFAS\TwoFAS\Authentication\Middleware\Blocked_Account_Check;
use TwoFAS\TwoFAS\Authentication\Middleware\Login_Template;
use TwoFAS\TwoFAS\Authentication\Middleware\Multisite_Check;
use TwoFAS\TwoFAS\Authentication\Middleware\New_Trusted_Device;
use TwoFAS\TwoFAS\Authentication\Middleware\Second_Factor_Status_Check;
use TwoFAS\TwoFAS\Authentication\Middleware\SSL;
use TwoFAS\TwoFAS\Authentication\Middleware\Step_Token_Manager;
use TwoFAS\TwoFAS\Authentication\Middleware\Authentication_Opener;
use TwoFAS\TwoFAS\Authentication\Middleware\Trusted_Devices_Enabled_Check;
use TwoFAS\TwoFAS\Authentication\Middleware\Middleware_Builder;
use TwoFAS\TwoFAS\Authentication\Handler\Handler_Builder;

return [
	'BeforeMiddleware' => DI\factory( function ( ContainerInterface $c ) {
		$builder = new Middleware_Builder();
		$builder
			->add_middleware( $c->get( Multisite_Check::class ) )
			->add_middleware( $c->get( Second_Factor_Status_Check::class ) )
			->add_middleware( $c->get( Blocked_Account_Check::class ) )
			->add_middleware( $c->get( Step_Token_Manager::class ) )
			->add_middleware( $c->get( SSL::class ) )
			->add_middleware( $c->get( External_Login::class ) )
			->add_middleware( $c->get( Trusted_Devices_Enabled_Check::class))
			->add_middleware( $c->get( Trusted_Device_Login::class ) )
			->add_middleware( $c->get( Login_Stop::class ) );

		return $builder->build();
	} ),
	'AfterMiddleware'  => DI\factory( function ( ContainerInterface $c ) {
		$builder = new Middleware_Builder();
		$builder
			->add_middleware( $c->get( Authentication_Opener::class ) )
			->add_middleware( $c->get( Login_Template::class ) )
			->add_middleware( $c->get( New_Trusted_Device::class ) );

		return $builder->build();
	} ),
	Login_Handler::class => DI\factory(function (ContainerInterface $c) {
		$builder = new Handler_Builder();
		$builder
			->add_handler( $c->get(Login_Configuration::class) )
			->add_handler( $c->get(Configuration_Reset::class) )
			->add_handler( $c->get(Configuration_Confirmation::class) )
			->add_handler( $c->get(Standard_Login::class) );

		return $builder->build();
	}),
	Login_Process::class => DI\object()
		->constructor(
			DI\get('BeforeMiddleware'),
			DI\get('AfterMiddleware'),
			DI\get(Login_Handler::class),
			DI\get(Error_Handler_Interface::class)
		)
];
