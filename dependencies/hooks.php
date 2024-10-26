<?php

use Psr\Container\ContainerInterface;
use TwoFAS\TwoFAS\Templates\Twig;
use TwoFAS\Core\Hooks\Hook_Handler;
use TwoFAS\Core\Hooks\Admin_Menu_Action;
use TwoFAS\TwoFAS\Hooks\Action_Links_Filter;
use TwoFAS\TwoFAS\Hooks\Admin_Notices_Action;
use TwoFAS\TwoFAS\Hooks\Authenticate_Filter;
use TwoFAS\TwoFAS\Hooks\Clean_Login_Process_Action;
use TwoFAS\TwoFAS\Hooks\Cron_Job_Interval_Filter;
use TwoFAS\TwoFAS\Hooks\Delete_Authentications_Action;
use TwoFAS\TwoFAS\Hooks\Delete_Expired_Authentications_Action;
use TwoFAS\TwoFAS\Hooks\Delete_Expired_Sessions_Action;
use TwoFAS\TwoFAS\Hooks\Delete_Trusted_Devices_Action;
use TwoFAS\TwoFAS\Hooks\Destroy_Session_Action;
use TwoFAS\TwoFAS\Hooks\Enqueue_Scripts_Action;
use TwoFAS\TwoFAS\Hooks\In_Plugin_Update_Message_Action;
use TwoFAS\TwoFAS\Hooks\Login_Footer_Action;
use TwoFAS\TwoFAS\Hooks\Logout_Not_Configured_Users_Action;
use TwoFAS\TwoFAS\Hooks\Regenerate_Session_Action;
use TwoFAS\TwoFAS\Hooks\Save_Login_Time_Action;
use TwoFAS\TwoFAS\Hooks\Script_Loader_Tag_Filter;
use TwoFAS\TwoFAS\Hooks\Update_Integration_Name;
use TwoFAS\TwoFAS\Hooks\Update_Option_User_Roles_Action;
use TwoFAS\TwoFAS\Hooks\Add_Custom_Column;

return [
	Hook_Handler::class      => DI\factory( function ( ContainerInterface $c ) {
		$hook_handler = new Hook_Handler();

		$hook_handler
			->add_hook( $c->get( Enqueue_Scripts_Action::class ) )
			->add_hook( $c->get( Action_Links_Filter::class ) )
			->add_hook( $c->get( Admin_Notices_Action::class ) )
			->add_hook( $c->get( In_Plugin_Update_Message_Action::class ) )
			->add_hook( $c->get( Cron_Job_Interval_Filter::class ) )
			->add_hook( $c->get( Delete_Expired_Sessions_Action::class ) )
			->add_hook( $c->get( Destroy_Session_Action::class ) )
			->add_hook( $c->get( Regenerate_Session_Action::class ) )
			->add_hook( $c->get( Login_Footer_Action::class ) )
			->add_hook( $c->get( Authenticate_Filter::class ) )
			->add_hook( $c->get( Admin_Menu_Action::class ) )
			->add_hook( $c->get( Update_Option_User_Roles_Action::class ) )
			->add_hook( $c->get( Logout_Not_Configured_Users_Action::class ) )
			->add_hook( $c->get( Save_Login_Time_Action::class ) )
			->add_hook( $c->get( Delete_Trusted_Devices_Action::class ) )
			->add_hook( $c->get( Update_Integration_Name::class ) )
			->add_hook( $c->get( Script_Loader_Tag_Filter::class ) )
			->add_hook( $c->get( Clean_Login_Process_Action::class ) )
			->add_hook( $c->get( Delete_Authentications_Action::class ) )
			->add_hook( $c->get( Delete_Expired_Authentications_Action::class ) )
			->add_hook( $c->get( Add_Custom_Column::class ) );

		return $hook_handler;
	} ),
	Admin_Menu_Action::class => DI\object( TwoFAS\TwoFAS\Hooks\Admin_Menu_Action::class )
		->constructor( DI\get( Twig::class ) )
];
