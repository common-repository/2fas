<?php

namespace TwoFAS\TwoFAS\Hooks;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Exceptions\Parse_Exception;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\Core\Readme\Upgrade_Notice;
use TwoFAS\TwoFAS\Templates\Twig;
use TwoFAS\TwoFAS\Update\Plugin_Version;

class In_Plugin_Update_Message_Action implements Hook_Interface {

	/**
	 * @var Upgrade_Notice
	 */
	private $upgrade_notice;

	/**
	 * @var Plugin_Version
	 */
	private $plugin_version;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Upgrade_Notice $upgrade_notice
	 * @param Plugin_Version $plugin_version
	 * @param Twig           $twig
	 */
	public function __construct( Upgrade_Notice $upgrade_notice, Plugin_Version $plugin_version, Twig $twig ) {
		$this->upgrade_notice = $upgrade_notice;
		$this->plugin_version = $plugin_version;
		$this->twig           = $twig;
	}

	public function register_hook() {
		add_action( 'in_plugin_update_message-' . TWOFAS_PLUGIN_BASENAME, [ $this, 'show_upgrade_notice' ] );
	}

	public function show_upgrade_notice() {
		try {
			$paragraphs = $this->get_paragraphs();
		} catch ( Download_Exception $e ) {
			$paragraphs = [];
		} catch ( Parse_Exception $e ) {
			$paragraphs = [];
		}

		if ( empty( $paragraphs ) ) {
			return;
		}

		echo $this->twig->get_view( 'dashboard/admin/upgrade-notice.html.twig', [ 'paragraphs' => $paragraphs ] );
	}

	/**
	 * @return array
	 *
	 * @throws Download_Exception
	 * @throws Parse_Exception
	 */
	private function get_paragraphs() {
		return $this->upgrade_notice->get_paragraphs( $this->plugin_version->get_source_code_version() );
	}
}
