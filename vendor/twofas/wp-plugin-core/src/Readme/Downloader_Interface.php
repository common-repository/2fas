<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Download_Exception;

interface Downloader_Interface {

	/**
	 * @param string $url
	 *
	 * @return Readme
	 *
	 * @throws Download_Exception
	 */
	public function download( $url );
}
