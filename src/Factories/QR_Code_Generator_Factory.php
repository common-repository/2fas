<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\Api\QrCode\QrClientFactory;
use TwoFAS\Api\QrCodeGenerator;

class QR_Code_Generator_Factory {

	/**
	 * @return QrCodeGenerator
	 */
	public static function create() {
		return new QrCodeGenerator( QrClientFactory::getInstance() );
	}
}
