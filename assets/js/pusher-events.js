(function( $ ) {
	try {
		var pusherSessionIdInput = $( '#pusher-session-id' ),
		    statusIdInput        = $( '#status-id' ),
		    integrationIdInput   = $( '#integration-id' ),
		    totpTokenInput       = $( '#totp-token' ),
		    errorModal           = $( '.twofas-error-modal' ),
		    errorModalMessage    = $( '#error-modal-message' );

		function handleLoginRequest( data ) {
			statusIdInput.val( data.statusId );
			totpTokenInput.val( data.totpToken );
			$( '.twofas-submit' ).addClass( 'twofas-pusher' );
			$( '.twofas-sockets .twofas-token-setup' ).removeClass( 'twofas-play' );
			$( '.twofas-sockets .twofas-token-loading' ).addClass( 'twofas-play' );

			this.disconnect();

			setTimeout( function() {
				$( '#loginform' ).submit();
			}, 1 );
		}

		function handleSubscriptionError( status, pusher, channelName, attemptCount ) {
			if ( attemptCount < 3 ) {
				subscribeChannel( pusher, channelName, ++attemptCount );
			} else {
				Sentry.captureMessage( 'Subscription error', {
					extra: {
						channel_name: channelName,
						status: status
					}
				} );
				pusher.disconnect();

				errorModalMessage.html( 'Subscription error (' + status + ')' );
				errorModal.trigger( 'display' );
			}
		}

		function subscribeChannel( pusher, channelName, attemptCount ) {
			var channel = pusher.subscribe( channelName );

			channel.bind( 'twofas-login-request', handleLoginRequest, pusher );

			channel.bind( 'pusher:subscription_error', function( status ) {
				handleSubscriptionError( status, pusher, channelName, attemptCount );
			} );
		}

		if ( pusherSessionIdInput.length ) {
			var pusher       = new Pusher( twofas.pusherKey, {
				    forceTLS: true,
				    authEndpoint: twofas.authEndpoint
			    } ),
			    channelName  = 'private-wp_' + integrationIdInput.val() + '_' + pusherSessionIdInput.val(),
			    attemptCount = 1;

			subscribeChannel( pusher, channelName, attemptCount );
		}

	} catch ( e ) {
		Sentry.captureException( e );
		if (e instanceof ReferenceError) {
			$( '.twofas-sockets .twofas-icon-padlock' ).remove();
			$( '.twofas-sockets .twofas-sockets-error' ).css('display', 'inline');
			$( '.twofas-sockets .twofas-token-setup' ).removeClass( 'twofas-play' );
			$( '.twofas-sockets .twofas-token-error' ).addClass( 'twofas-play' );
		} else {
			errorModalMessage.html( 'Server error occurred. Please try to refresh this page.' );
			errorModal.trigger( 'display' );
		}
	}

})( jQuery );
