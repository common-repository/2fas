<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Http\Request;

class Container {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Downloader_Interface
	 */
	private $downloader;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var Readme|null
	 */
	private $readme;

	/**
	 * @param Request              $request
	 * @param Downloader_Interface $downloader
	 * @param string               $url
	 */
	public function __construct( Request $request, Downloader_Interface $downloader, $url ) {
		$this->request    = $request;
		$this->downloader = $downloader;
		$this->url        = $url;
	}

	/**
	 * @return Readme
	 *
	 * @throws Download_Exception
	 */
	public function get() {
		if ( $this->request->is_plugins_page() || $this->request->is_plugin_search_page() ) {
			return $this->readme ? $this->readme : $this->readme = $this->downloader->download( $this->url );
		}

		return new Readme( array() );
	}
}
