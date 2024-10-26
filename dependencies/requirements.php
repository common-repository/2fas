<?php

use Psr\Container\ContainerInterface;
use TwoFAS\TwoFAS\Requirements\Extensions\Curl;
use TwoFAS\TwoFAS\Requirements\Extensions\Gd;
use TwoFAS\TwoFAS\Requirements\Extensions\Gettext;
use TwoFAS\TwoFAS\Requirements\Extensions\MbString;
use TwoFAS\TwoFAS\Requirements\Extensions\OpenSSL;
use TwoFAS\TwoFAS\Requirements\Requirement_Checker;
use TwoFAS\TwoFAS\Requirements\Versions\PHP_Version;
use TwoFAS\TwoFAS\Requirements\Versions\WP_Version;

return [
	Requirement_Checker::class => DI\factory( function ( ContainerInterface $c ) {
		$requirement_checker = new Requirement_Checker();
		$requirement_checker
			->add_requirement( $c->get(Curl::class) )
			->add_requirement( $c->get(Gd::class) )
			->add_requirement( $c->get(Gettext::class) )
			->add_requirement( $c->get(MbString::class) )
			->add_requirement( $c->get(OpenSSL::class) )
			->add_requirement( $c->get(PHP_Version::class) )
			->add_requirement( $c->get(WP_Version::class) );

		return $requirement_checker;
	} )
];
