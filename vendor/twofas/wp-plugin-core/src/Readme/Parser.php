<?php

namespace TwoFAS\Core\Readme;

use TwoFAS\Core\Exceptions\Parse_Exception;

class Parser {

	/**
	 * @param string $content
	 *
	 * @return Readme
	 *
	 * @throws Parse_Exception
	 */
	public function parse( $content ) {
		$sections = $this->get_sections( $content );

		return new Readme( $sections );
	}

	/**
	 * @param string $content
	 *
	 * @return array
	 *
	 * @throws Parse_Exception
	 */
	private function get_sections( $content ) {
		$content = trim( $content );
		$pattern = '/={2,3} (.+) ={2,3}/';

		preg_match_all( $pattern, $content, $matches );

		$section_names    = preg_filter( $pattern, '${1}', $matches[0] );
		$section_names    = $this->trim( $section_names );
		$section_contents = preg_split( $pattern, $content, null, PREG_SPLIT_NO_EMPTY );
		$section_contents = $this->trim( $section_contents );

		if ( count( $section_names ) === count( $section_contents ) ) {
			return array_combine( $section_names, $section_contents );
		}

		throw new Parse_Exception( 'Readme has invalid format.' );
	}

	/**
	 * @param array $elements
	 *
	 * @return array
	 */
	private function trim( array $elements ) {
		return array_map( function ( $element ) {
			return trim( $element );
		}, $elements );
	}
}
