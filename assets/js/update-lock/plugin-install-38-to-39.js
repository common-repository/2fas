(
	function( $ ) {
		$( "td strong:contains('2FAS – Two Factor Authentication')" ).next().find( 'a' ).eq( 1 ).remove();
	}
)( jQuery );