<?php

use Psr\Container\ContainerInterface;
use TwoFAS\TwoFAS\Listeners\Delete_Trusted_Devices;
use TwoFAS\TwoFAS\Listeners\Enable_Totp;
use TwoFAS\TwoFAS\Listeners\Listener_Provider;
use TwoFAS\TwoFAS\Listeners\Setup_Plugin_Options;
use TwoFAS\TwoFAS\Listeners\Update_Integration_User_Totp_Secret;
use TwoFAS\TwoFAS\Events\Integration_Was_Created;
use TwoFAS\TwoFAS\Events\Totp_Configuration_Code_Accepted;
use TwoFAS\TwoFAS\Events\Totp_Confirmation_Code_Accepted;
use TwoFAS\Core\Events\View_Response_Created;
use TwoFAS\TwoFAS\Listeners\Add_View_Response;

return [
	'events'                 => [
		Integration_Was_Created::class          => [
			Setup_Plugin_Options::class,
		],
		Totp_Configuration_Code_Accepted::class => [
			Enable_Totp::class,
			Delete_Trusted_Devices::class,
			Update_Integration_User_Totp_Secret::class,
		],
		Totp_Confirmation_Code_Accepted::class  => [
			Enable_Totp::class,
			Delete_Trusted_Devices::class,
		],
		View_Response_Created::class            => [
			Add_View_Response::class
		]
	],
	Listener_Provider::class => DI\factory( function ( ContainerInterface $c ) {
		$listener_provider = new Listener_Provider( $c );
		$listener_provider->add_events( $c->get( 'events' ) );

		return $listener_provider;
	} )
];
