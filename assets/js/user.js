(function( $ ) {
	try {
		var telInput             = $( 'input[type="tel"]' ),
		    collapseExpandButton = $( '.js-collapse-expand' );

		function collapseExpand( event ) {
			event.preventDefault();
			var collapseButton = $( this );
			var collapse       = collapseButton.parents().find( '.twofas-content-items-container' );
			var contentHeight  = parseInt( collapse.css( 'padding-top' ) ) + parseInt( collapse.css( 'padding-bottom' ) );

			collapse.children().each( function() {
				if ( !$( this ).hasClass( 'twofas-configured-collapse' ) ) {
					contentHeight += $( this ).outerHeight();
				}
			} );

			collapse.css( { 'height': '300' } );
			collapse.parent().parent().removeClass( 'twofas-configured' );
			collapse.animate( { "height": contentHeight }, 500, function() {
				$( 'html, body' ).animate( {
					scrollTop: $( '.twofas-bar' ).offset().top - 50
				}, 500 );
			} );
		}

		telInput.intlTelInput( {
			nationalMode: true,
			utilsScript: twofas.utilsUrl,
			initialCountry: 'auto',
			geoIpLookup: function( callback ) {
				$.get( 'https://ipapi.co/json/', function() {}, 'json' )
					.always( function( resp ) {
						var countryCode = (resp && resp.country) ? resp.country : "";
						callback( countryCode );
					} );
			}
		} );

		telInput.on( 'keyup change', function() {
			var number = $( this ).intlTelInput( "getNumber" );
			$( this ).parents().find( '.js-tel-value' ).val( number );
		} );

		collapseExpandButton.click( collapseExpand );

	} catch ( e ) {
		Sentry.captureException( e );
		errorModalMessage.html( 'Server error occurred. Please try to refresh this page.' );
		errorModal.trigger( 'display' );
	}

})( jQuery );
