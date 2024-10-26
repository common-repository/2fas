(function( $ ) {
	var showTotpSecretLink = $( '.js-show-totp-secret' );

	function showTotpSecret( event ) {
		event.preventDefault();
		$( '.twofas-totp-secret' ).show();
	}

	showTotpSecretLink.click( showTotpSecret );
})( jQuery );
