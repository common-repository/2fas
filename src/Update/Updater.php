<?php

namespace TwoFAS\TwoFAS\Update;

use LogicException;
use TwoFAS\TwoFAS\Exceptions\DB_Exception;
use TwoFAS\TwoFAS\Exceptions\Migration_Exception;
use TwoFAS\Core\Http\Request;

class Updater {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Plugin_Version
	 */
	private $plugin_version;

	/**
	 * @var Migrator
	 */
	private $migrator;

	/**
	 * @param Request        $request
	 * @param Plugin_Version $plugin_version
	 * @param Migrator       $migrator
	 */
	public function __construct( Request $request, Plugin_Version $plugin_version, Migrator $migrator ) {
		$this->request        = $request;
		$this->plugin_version = $plugin_version;
		$this->migrator       = $migrator;
	}

	/**
	 * @return bool
	 */
	public function should_plugin_be_updated() {
		return ! $this->request->has( 'doing_wp_cron' );
	}

	/**
	 * @throws Migration_Exception
	 * @throws DB_Exception
	 * @throws LogicException
	 */
	public function update_plugin() {
		$database_version    = $this->plugin_version->get_db_version();
		$source_code_version = $this->plugin_version->get_source_code_version();

		if ( version_compare( $database_version, $source_code_version, '!=' ) ) {
			$this->migrator->migrate();
			$this->plugin_version->update_db_version();
		}
	}
}
