(function( $ ) {
	function shouldNotificationBeWide() {
		return $( '.twofas-login-box' ).length && ! $( 'body' ).hasClass( 'interim-login' );
	}

	if ( $( '.twofas-login-form-container' ).length ) {
		$( '#backtoblog' ).hide();

		$( '#loginform' ).submit( function() {
			$( '.twofas-submit' ).addClass( 'twofas-disabled' );
			$( '#wp-submit' ).val( 'Loading...' );
		} );

		$( '.twofas-hide-after-time' ).delay( 3200 ).fadeOut( 300 );
	}

	if ( shouldNotificationBeWide() ) {
		$( '#login_error' ).addClass( 'twofas-login-error-wide' );
	}
})( jQuery );
