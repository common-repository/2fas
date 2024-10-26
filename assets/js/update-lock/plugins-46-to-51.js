(
	function( $ ) {
		var notificationWrapper = $( '#2fas-update .update-message' );

		notificationWrapper.empty();
		notificationWrapper.append( "<p>There is a new version of 2FAS available, but it doesn't work with your version of PHP. <a href='https://wordpress.org/support/update-php/' target='_blank'>Learn more about updating PHP</a>.</p>" );
		notificationWrapper.addClass( 'notice-error' );
		notificationWrapper.removeClass( 'notice-warning' );
	}
)( jQuery );