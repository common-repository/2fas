<?php

use TwoFAS\Core\Environment_Interface;
use Psr\Container\ContainerInterface;
use TwoFAS\Api\QrCode\QrClientFactory;
use TwoFAS\Core\Readme\Container;
use TwoFAS\Core\Readme\Downloader;
use TwoFAS\Core\Storage\DB_Wrapper;
use TwoFAS\Core\Update\Deprecation;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Update\PHP_Requirement_Interface;
use TwoFAS\TwoFAS\Exceptions\Handler\Error_Handler;
use TwoFAS\TwoFAS\Exceptions\Handler\Sentry_Logger;
use TwoFAS\TwoFAS\Factories\Session_Storage_Factory;
use TwoFAS\TwoFAS\Helpers\Config;
use TwoFAS\TwoFAS\Helpers\Environment;
use TwoFAS\TwoFAS\Integration\Integration_Name;
use TwoFAS\TwoFAS\Storage\Options_Storage;
use TwoFAS\TwoFAS\Storage\Session_Storage_Interface;
use TwoFAS\TwoFAS\Storage\User_Storage;
use Twig\Environment as Twig_Environment;
use Twig\TwigFunction;
use Twig\Loader\LoaderInterface;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\Core\Http\Request;
use TwoFAS\Api\QrCode\QrClientInterface;
use TwoFAS\Core\Readme\Header;

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Core
 * --------------------------------------------------------------------------------------------------------------------
 */
global $wpdb;

return [
	DB_Wrapper::class                => DI\object()
		->constructor( $wpdb ),
	LoaderInterface::class           => DI\object( FilesystemLoader::class )
		->constructor( TWOFAS_TEMPLATES_PATH ),
	Twig_Environment::class          => DI\factory( function ( ContainerInterface $c ) {
		$twig_environment = new Twig_Environment( $c->get( LoaderInterface::class ) );
		$twig_environment->addGlobal( 'flash', $c->get( Flash::class ) );
		$twig_environment->addGlobal( 'config', $c->get( Config::class ) );
		$twig_environment->addFunction( new TwigFunction( 'create_url', [ '\TwoFAS\TwoFAS\Helpers\URL', 'create' ] ) );
		$twig_environment->addFunction( new TwigFunction( 'create_form_nonce', [
			'\TwoFAS\TwoFAS\Helpers\URL',
			'create_form_nonce'
		] ) );
		$twig_environment->addFunction( new TwigFunction( 'translate_user_role', 'translate_user_role' ) );
		$twig_environment->addFunction( new TwigFunction( 'sprintf', 'sprintf' ) );
		$twig_environment->addFunction( new TwigFunction( 'login_header', 'login_header' ) );
		$twig_environment->addFunction( new TwigFunction( 'login_footer', 'login_footer' ) );
		$twig_environment->addFunction( new TwigFunction( 'esc_html__', 'esc_html__' ) );
		$twig_environment->addFunction( new TwigFunction( 'esc_attr__', 'esc_attr__' ) );
		$twig_environment->addFunction( new TwigFunction( '__', '__' ) );
		$twig_environment->addFunction( new TwigFunction( '_n', '_n' ) );
		$twig_environment->addRuntimeLoader( $c->get( ContainerRuntimeLoader::class ) );

		return $twig_environment;
	} ),
	Config::class                    => DI\object()->constructor( TWOFAS_PLUGIN_PATH ),
	Error_Handler_Interface::class   => DI\factory( function ( ContainerInterface $c ) {
		return new Error_Handler( $c->get( Sentry_Logger::class ), $c->get( Options_Storage::class )->is_logging_allowed() );
	} ),
	Environment_Interface::class     => DI\object( Environment::class ),
	PHP_Requirement_Interface::class => DI\object( Header::class ),
	Integration_Name::class          => DI\factory( function ( ContainerInterface $c ) {
		return new Integration_Name( $c->get( Environment::class )->get_wordpress_app_url() );
	} ),
	Container::class                 => DI\factory( function ( ContainerInterface $c ) {
		return new Container( $c->get( Request::class ), $c->get( Downloader::class ), $c->get( Config::class )->get_readme_url() );
	} ),
	Deprecation::class               => DI\factory( function ( ContainerInterface $c ) {
		$deprecation = new Deprecation( $c->get( Environment::class ) );
		$deprecation->deprecate_php_older_than( TWOFAS_DEPRECATE_PHP_OLDER_THAN );

		return $deprecation;
	} ),
	QrClientInterface::class         => DI\factory( [ QrClientFactory::class, 'getInstance' ] ),
	User_Storage::class              => DI\factory( function ( ContainerInterface $c ) {
		$storage = new User_Storage( $c->get( DB_Wrapper::class ) );

		if ( function_exists( 'wp_get_current_user' ) ) {
			$wp_user = wp_get_current_user();

			if ( $wp_user->ID > 0 ) {
				$storage->set_wp_user( $wp_user );
			}
		}

		return $storage;
	} ),
	Session_Storage_Interface::class => Di\factory( function ( ContainerInterface $c ) {
		$factory = new Session_Storage_Factory( $c->get( DB_Wrapper::class ), $c->get( Request::class ) );

		return $factory->create();
	} )
];
