(
	function( $ ) {
		// For AJAX plugin search in WP 4.6+
		MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

		var observer = new MutationObserver( function( mutations, observer ) {
			$( '.plugin-card-2fas .action-links .plugin-action-buttons' ).remove();
		} );

		var pluginFilter = document.getElementById( 'plugin-filter' );

		if ( pluginFilter ) {
			observer.observe( pluginFilter, {
				subtree: true,
				childList: true
			} );
		}
	}
)( jQuery );