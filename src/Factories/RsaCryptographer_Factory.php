<?php

namespace TwoFAS\TwoFAS\Factories;

use TwoFAS\Account\Integration;
use TwoFAS\Encryption\RsaCryptographer;

class RsaCryptographer_Factory {

	/**
	 * @param Integration $integration
	 *
	 * @return RsaCryptographer
	 */
	public function create( Integration $integration ) {
		return new RsaCryptographer( $integration->getPublicKey(), $integration->getPrivateKey() );
	}
}
