<?php

namespace TwoFAS\TwoFAS\Encryption;

use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\ReadKey;

class Empty_Key_Storage implements ReadKey {

	/**
	 * @return Key
	 */
	public function retrieve() {
		return new AESKey( '' );
	}
}
