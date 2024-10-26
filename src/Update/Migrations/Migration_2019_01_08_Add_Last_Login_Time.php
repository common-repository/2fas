<?php

namespace TwoFAS\TwoFAS\Update\Migrations;

use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\TwoFAS\Storage\User_Storage;
use TwoFAS\TwoFAS\Update\Migration;

class Migration_2019_01_08_Add_Last_Login_Time extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '2.4.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$users = get_users();

		foreach ( $users as $user ) {
			update_user_meta( $user->ID, User_Storage::USER_LAST_LOGIN_TIME, time() );
		}
	}
}
