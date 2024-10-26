<?php

namespace TwoFAS\TwoFAS\Randomization;

use TwoFAS\Encryption\Random\NonCryptographicalRandomIntGenerator;
use TwoFAS\Encryption\Random\RandomStringGenerator;
use TwoFAS\Encryption\Random\Str;

class Hash {

	const TRUSTED_DEVICE_KEY_LENGTH = 23;
	const STEP_TOKEN_KEY_LENGTH     = 128;
	const SESSION_KEY_LENGTH        = 16;
	const PUSHER_SESSION_KEY_LENGTH = 23;

	/**
	 * @return string
	 */
	public static function get_trusted_device_id() {
		$str = self::generate( self::TRUSTED_DEVICE_KEY_LENGTH );

		return md5( $str->__toString() );
	}

	/**
	 * @return string
	 */
	public static function get_step_token() {
		$str = self::generate( self::STEP_TOKEN_KEY_LENGTH );

		return $str->toBase64()->__toString();
	}

	/**
	 * @return string
	 */
	public static function get_session_id() {
		$str = self::generate( self::SESSION_KEY_LENGTH );

		return $str->toBase64()->__toString();
	}

	/**
	 * @return string
	 */
	public static function get_pusher_session_id() {
		$str = self::generate( self::PUSHER_SESSION_KEY_LENGTH );

		return md5( $str->__toString() );
	}

	/**
	 * @param int $length
	 *
	 * @return Str
	 */
	private static function generate( $length ) {
		$generator = new RandomStringGenerator( new NonCryptographicalRandomIntGenerator() );

		return $generator->string( $length );
	}
}
