<?php

namespace TwoFAS\TwoFAS\Http\Controllers\Admin;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\TwoFAS\Core\Legacy_Mode_Checker;
use TwoFAS\TwoFAS\Helpers\Flash;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Http\Action_URL;
use TwoFAS\TwoFAS\Http\Controllers\Controller;
use TwoFAS\TwoFAS\Integration\API_Wrapper;
use TwoFAS\TwoFAS\Storage\Storage;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Templates\Views;
use TwoFAS\TwoFAS\Update\Migrator;
use TwoFAS\Core\Http\JSON_Response;

class Dashboard_Controller extends Controller {

	/**
	 * @var Legacy_Mode_Checker
	 */
	private $legacy_mode_checker;

	/**
	 * @var Migrator
	 */
	private $migrator;

	/**
	 * @param Storage             $storage
	 * @param API_Wrapper         $api_wrapper
	 * @param Flash               $flash
	 * @param Legacy_Mode_Checker $legacy_mode_checker
	 * @param Migrator            $migrator
	 */
	public function __construct(
		Storage $storage,
		API_Wrapper $api_wrapper,
		Flash $flash,
		Legacy_Mode_Checker $legacy_mode_checker,
		Migrator $migrator
	) {
		parent::__construct( $storage, $api_wrapper, $flash );

		$this->legacy_mode_checker = $legacy_mode_checker;
		$this->migrator            = $migrator;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 *
	 * @throws NotFoundException
	 * @throws Account_Exception
	 */
	public function show_dashboard_page( Request $request ) {

		$options_storage = $this->storage->get_options();
		$user_storage    = $this->storage->get_user_storage();
		$client          = $this->api_wrapper->get_client();
		$data            = [];

		if ( $client->hasCard() ) {
			$card                    = $this->api_wrapper->get_primary_card( $client );
			$data['credit_card']     = 'XXXX-XXXX-XXXX-' . $card->getLastFour();
			$data['has_client_card'] = true;
		} else {
			$data['has_client_card'] = false;

			if ( $options_storage->is_plan_premium() ) {
				$options_storage->set_basic_plan();
				$this->flash->add_message_now( 'error', 'plan-downgraded-automatically' );
			}
		}

		$data['twofas_email']                            = $options_storage->get_twofas_email();
		$data['is_plugin_enabled']                       = $options_storage->is_plugin_enabled();
		$data['statistics']                              = $this->get_statistics( $user_storage );
		$token                                           = $this->storage->get_oauth()->retrieveToken( 'wordpress' );
		$data['token']                                   = $token->getAccessToken();
		$data['show_wizard_modal']                       = $this->display_wizard_modal( $request );
		$data['is_plan_premium']                         = $options_storage->is_plan_premium();
		$data['number_of_users_with_enabled_sms_backup'] = $user_storage->get_number_of_users_with_enabled_sms_backup();
		$data['can_migrate_user']                        = $this->can_migrate_users();

		return $this->view( Views::ADMIN_MENU, $data );

	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function disable_plugin( Request $request ) {
		$this->storage->get_options()->disable_plugin();
		$this->flash->add_message( 'success', 'plugin-disabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function enable_plugin( Request $request ) {
		$this->storage->get_options()->enable_plugin();
		$this->flash->add_message( 'success', 'plugin-enabled' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function downgrade_to_basic( Request $request ) {
		$options      = $this->storage->get_options();
		$user_storage = $this->storage->get_user_storage();

		$this->legacy_mode_checker->disable_legacy_2fa();
		$user_storage->disable_sms_backup_globally();
		$options->set_basic_plan();
		$this->flash->add_message( 'success', 'plan-updated' );

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 */
	public function upgrade_to_premium( Request $request ) {
		try {
			$options = $this->storage->get_options();
			$client  = $this->api_wrapper->get_client();

			if ( $client->hasCard() ) {
				$options->set_premium_plan();
				$this->flash->add_message( 'success', 'plan-updated' );
			} else {
				$this->flash->add_message( 'error', 'credit-card-required' );
			}
		} catch ( Account_Exception $e ) {
			$this->flash->add_message( 'error', 'client-error' );
		}

		return $this->redirect( new Action_URL( Action_Index::SUBMENU_DASHBOARD ) );
	}

	public function migrate_users( Request $request ) {
		if ( $this->migrator->is_migrated( 'Migration_2021_05_01_Migrate_Users' ) ) {
			return $this->json( [ __( 'User migration has already been done', '2fas' ) ], 403 );
		}

		$this->storage->get_options()->allow_user_migration();
		$this->migrator->migrate();

		return $this->json( [] );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function get_migration_status( Request $request ) {
		return $this->json( $this->get_statistics( $this->storage->get_user_storage() ) );
	}

	/**
	 * @param User_Storage $user_storage
	 *
	 * @return array
	 */
	private function get_statistics( User_Storage $user_storage ) {
		$wp_user_count       = $user_storage->get_user_count();
		$active_user_count   = $user_storage->get_active_user_count();
		$migrated_users      = $user_storage->get_migrated_user_count();
		$inactive_user_count = $wp_user_count - $active_user_count;

		return [
			'active_users_count'   => $active_user_count,
			'inactive_users_count' => $inactive_user_count,
			'migrated_users'       => $migrated_users
		];
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function display_wizard_modal( Request $request ) {
		return $request->header( 'HTTP_REFERER' ) === $this->get_create_account_url();
	}

	/**
	 * @return string
	 */
	private function get_create_account_url() {
		$page   = Action_Index::SUBMENU_DASHBOARD;
		$action = Action_Index::ACTION_CREATE_ACCOUNT;
		$path   = "admin.php?page={$page}&twofas-action={$action}";

		return admin_url( $path );
	}

	/**
	 * @return bool
	 */
	private function can_migrate_users() {
		return (bool)twofas_prime_plugin_active() && ! $this->migrator->is_migrated('Migration_2021_05_01_Migrate_Users');
	}
}
