if ( typeof Sentry != 'undefined' ) {
	Sentry.init( {
		dsn: twofasSentry.sentryDsn,
		release: twofasSentry.release,
		whitelistUrls: [
			twofasSentry.whitelistUrls
		],
		beforeSend: function( event ) {
			if ( false === Boolean( twofasSentry.loggingAllowed ) ) {
				return null;
			}

			if ( twofasSentry.loginPageUrl === event.request.url ) {
				event.request.url = '[Filtered: ' + twofasSentry.siteUrl + ']';
			}

			return event;
		}
	} );

	Sentry.configureScope( function( scope ) {
		scope.setTag( "jquery_version", jQuery.fn.jquery );
		scope.setTag( "wp_version", twofasSentry.wp_version );
		scope.setTag( "api_sdk_version", twofasSentry.api_sdk_version );
		scope.setTag( "account_sdk_version", twofasSentry.account_sdk_version );
	} );
} else {
	var Sentry = {
		captureException: function( e ) {
			console.log( e );
		}
	};
}
