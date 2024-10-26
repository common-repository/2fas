(
	function( $ ) {
		try {
			var deactivationButton = $("a[aria-label='Deactivate 2FAS — Two Factor Authentication']"),
			    deactivationUrl    = deactivationButton.attr( 'href' ),
			    isFormAdded        = false,
			    modalOpened        = false;

			function openModal( modal ) {
				modal.css( { 'display': 'table' } ).animate( { opacity: 1 }, 500 );
				modalOpened = true;
			}

			function disableElement( element ) {
				element.prop( 'disabled', true );
			}

			function enableElement( element ) {
				element.prop( 'disabled', false );
			}

			deactivationButton.click( function( e ) {
				e.preventDefault();

				if ( !isFormAdded ) {
					$( 'body' ).append( twofasDeactivation.deactivationForm );
					isFormAdded = true;
				}

				var deactivationToken       = $( '.twofas-deactivation-token > input[name="_wpnonce"]' ),
				    skipButton              = $( '.twofas-js-deactivation-skip' ),
				    sendButton              = $( '.twofas-js-deactivation-send' ),
				    deactivationPluginModal = $( '.twofas-js-deactivation-modal' ),
				    reasonRadio             = $( 'input[name=reason]' ),
				    textArea                = $( 'textarea[name=reason-desc]' );

				reasonRadio.on( 'change', function() {
					if ( $( this ).val() === 'other' ) {
						enableElement( textArea );

						if ( $.trim( textArea.val() ).length ) {
							enableElement( sendButton );
						} else {
							disableElement( sendButton );
						}

					} else {
						enableElement( sendButton );
						disableElement( textArea );
					}
				} );

				textArea.on( 'input change keyup paste', function() {
					if ( $.trim( $( this ).val() ).length ) {
						enableElement( sendButton );
					} else {
						disableElement( sendButton );
					}
				} );

				skipButton.off( 'click' ).on( 'click', function( e ) {
					e.preventDefault();
					window.location.href = deactivationUrl;
				} );

				sendButton.off( 'click' ).on( 'click', function( e ) {
					e.preventDefault();
					disableElement( sendButton );

					var reason  = $( 'input[name=reason]:checked' ),
					    message = '';

					if ( reason.length ) {
						if ( reason.val() !== 'other' ) {
							message = reason.next().html();
						} else {
							message = textArea.val();
						}
					}

					$.post( twofasDeactivation.deactivationUrl, {
						_wpnonce: deactivationToken.val(),
						message: message
					} ).always( function() {
						window.location.href = deactivationUrl;
					} )
				} );

				openModal( deactivationPluginModal );
			} );
		} catch ( e ) {
			Sentry.captureException( e );
		}
	}
)( jQuery );
