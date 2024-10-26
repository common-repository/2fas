<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Exceptions\Parse_Exception;

class Upgrade_Notice {

	const SECTION_UPGRADE_NOTICE = 'Upgrade Notice';

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * @param string $source_code_version
	 *
	 * @return array
	 *
	 * @throws Download_Exception
	 * @throws Parse_Exception
	 */
	public function get_paragraphs( $source_code_version ) {
		$section = $this->get_upgrade_notice_section();
		$result  = array();

		array_walk( $section, function ( $paragraphs, $version ) use ( &$result, $source_code_version ) {
			if ( version_compare( $source_code_version, $version, '<' ) ) {
				$result = array_merge( $result, $paragraphs );
			}
		} );

		return $result;
	}

	/**
	 * @return array
	 *
	 * @throws Download_Exception
	 * @throws Parse_Exception
	 */
	private function get_upgrade_notice_section() {
		$readme  = $this->container->get();
		$section = $readme->get_section( self::SECTION_UPGRADE_NOTICE );

		if ( is_null( $section ) ) {
			return array();
		}

		return $this->parse( $section );
	}

	/**
	 * @param string $content
	 *
	 * @return array
	 *
	 * @throws Parse_Exception
	 */
	private function parse( $content ) {
		$pattern = '/= (\d+\.\d+\.\d+) =/';

		preg_match_all( $pattern, $content, $matches );

		$versions = preg_filter( $pattern, '${1}', $matches[0] );
		$messages = preg_split( $pattern, $content, null, PREG_SPLIT_NO_EMPTY );
		$messages = $this->split( $messages );

		if ( count( $versions ) === count( $messages ) ) {
			return array_combine( $versions, $messages );
		}

		throw new Parse_Exception( 'Upgrade Notice section has invalid format.' );
	}

	/**
	 * @param array $messages
	 *
	 * @return array
	 */
	private function split( array $messages ) {
		return array_map( function ( $message ) {
			$message = htmlspecialchars( $message, ENT_QUOTES );

			return preg_split( '/\r|\n|\r\n/', $message, null, PREG_SPLIT_NO_EMPTY );
		}, $messages );
	}
}
