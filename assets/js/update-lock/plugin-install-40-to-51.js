(
	function( $ ) {
		$( 'a:contains("2FAS – Two Factor Authentication")' ).parent().parent().next().find( 'a' ).remove();
	}
)( jQuery );