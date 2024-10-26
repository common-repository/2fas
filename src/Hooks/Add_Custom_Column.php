<?php

namespace TwoFAS\TwoFAS\Hooks;

use DI\FactoryInterface;
use DI\DependencyException;
use DI\NotFoundException;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\TwoFAS\Helpers\URL;
use TwoFAS\TwoFAS\Http\Action_Index;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\User\Capabilities;
use WP_User;

class Add_Custom_Column implements Hook_Interface {

	/**
	 * @var FactoryInterface
	 */
	private $user_factory;

	/**
	 * @param FactoryInterface $user_factory
	 */
	public function __construct( FactoryInterface $user_factory ) {
		$this->user_factory = $user_factory;
	}

	public function register_hook() {
		add_filter( 'user_row_actions', [ $this, 'user_row_actions' ], 10, 2 );
		add_filter( 'manage_users_columns', [ $this, 'manage_users_columns' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'manage_users_custom_column' ], 10, 3 );
	}

	/**
	 * @param array   $actions
	 * @param WP_User $user
	 *
	 * @return array
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function user_row_actions( $actions, $user ) {
		/** @var User_Storage $user_storage */
		$user_storage = $this->user_factory->make( User_Storage::class );
		if ( current_user_can( Capabilities::ADMIN ) && $user_storage->get_user_id() === $user->ID ) {
			$url             = URL::create( Action_Index::SUBMENU_CHANNEL );
			$actions['2fas'] = '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( __( 'Edit your 2FAS settings', '2fas' ) ) . '">2FAS</a>';
		}

		return $actions;
	}

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
	public function manage_users_columns( array $columns ) {
		if ( current_user_can( Capabilities::ADMIN ) ) {
			$columns['twofas_2fa_status'] = '2FAS';
		}

		return $columns;
	}

	/**
	 * @param string $value
	 * @param string $column_name
	 * @param int    $user_id
	 *
	 * @return string
	 */
	public function manage_users_custom_column( $value, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'twofas_2fa_status':
				/** @var User_Storage $user_storage */
				$user_storage = $this->user_factory->make( User_Storage::class );
				$user_storage->set_wp_user( new WP_User( $user_id ) );
				$value = $user_storage->is_2fa_enabled() ? __( 'Active', '2fas' ) : __( 'Inactive', '2fas' );

				break;
		}

		return $value;
	}
}
