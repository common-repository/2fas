(function( $ ) {
	try {
		// Modals
		var QRModal                   = $( '.twofas-qr-modal' ),
		    planModal                 = $( '.twofas-plan-modal' ),
		    deletionConfirmationModal = $( '.twofas-deletion-confirmation-modal' ),
		    deleteTotpModal           = $( '.twofas-delete-totp-modal' ),
		    logoutModal               = $( '.twofas-logout-modal' ),
		    downgradeModal            = $( '.twofas-downgrade-modal' ),
		    wizardModal               = $( '.twofas-wizard-modal' ),
		    reloadWarningModal        = $( '.twofas-reload-warning-modal' ),
		    creditCardModal           = $( '.twofas-credit-card-modal' ),
		    errorModal                = $( '.twofas-error-modal' ),
		    successModal              = $( '.twofas-success-modal' ),
		    removeConfigModal         = $( '.twofas-remove-config-modal' ),
		    disableTotpModal          = $( '.twofas-disable-totp-modal' ),
		    disablePhoneModal         = $( '.twofas-disable-phone-modal' ),
		    migrateModal              = $( '.twofas-migrate-users-confirmation-modal');

		// Actions
		var closeModalButton              = $( '.js-close-modal' ),
		    showQRModal                   = $( '.js-open-qr-modal' ),
		    showPlanModal                 = $( '.js-open-plan-modal' ),
		    planRedirectButton            = $( '.js-plan-redirect' ),
		    confirmButton                 = $( '.js-confirm' ),
		    creditCardOKButton            = $( '.js-close-cc-complete' ),
		    showLogoutModal               = $( '.js-open-logout-modal' ),
		    showReloadWarningModal        = $( '.js-open-reload-warning-modal' ),
		    showDowngradeModal            = $( '.js-open-downgrade-modal' ),
		    showCreditCardModal           = $( '.js-open-credit-card-modal' ),
		    showDeletionConfirmationModal = $( '.js-open-deletion-confirmation-modal' ),
		    showDeleteTotpModal           = $( '.js-open-delete-totp-modal' ),
		    openRemoveConfigModal         = $( '.js-open-remove-config-modal' ),
		    showDisableTotpModal          = $( '.js-open-disable-totp-modal' ),
		    showDisablePhoneModal         = $( '.js-open-disable-phone-modal' ),
		    showMigrateUsersModal         = $( '.js-open-migrate-users-modal' );

		var modalClass         = '.twofas-modal',
		    modalBackdropClass = '.twofas-modal-backdrop',
		    modalOpened        = false,
		    actionForm         = null;

		function openModal( modal ) {
			modal.css( { 'display': 'table' } ).animate( { opacity: 1 }, 500 );
			modalOpened = true;
		}

		function closeModal( modal ) {
			modal.animate( { opacity: 0 }, 250, function() {
				$( this ).css( { 'display': '' } );
				modal.children().css( { 'display': '' } );
				modalOpened = false;
			} );
		}

		function clickCloseModal( e ) {
			if ( modalOpened ) {
				var container = $( modalClass );

				if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
					var modal = container.parents().find( modalBackdropClass );

					closeModal( modal );
				}
			}
		}

		function creditCardComplete( event ) {
			event.preventDefault();
			location.reload();
		}

		// open modals
		showQRModal.click( function() {
			openModal( QRModal );
		} );

		showPlanModal.click( function() {
			$( '.twofas-update-card-before' ).removeClass( 'twofas-hidden' );
			$( '.twofas-update-card-after' ).addClass( 'twofas-hidden' );
			openModal( planModal );
		} );

		planRedirectButton.click( function() {
			$( '.twofas-update-card-before' ).addClass( 'twofas-hidden' );
			$( '.twofas-update-card-after' ).removeClass( 'twofas-hidden' );
		} );

		openRemoveConfigModal.click( function( event ) {
			event.preventDefault();
			openModal( removeConfigModal );
		} );

		creditCardOKButton.click( function( event ) {
			creditCardComplete( event );
		} );

		confirmButton.click( function( event ) {
			event.preventDefault();
			actionForm.trigger( 'submit' );
		} );

		showDisableTotpModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( disableTotpModal );
			}
		} );

		showDisablePhoneModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( disablePhoneModal );
			}
		} );

		showDeletionConfirmationModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( deletionConfirmationModal );
			}
		} );

		showDeleteTotpModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( deleteTotpModal );
			}
		} );

		showLogoutModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( logoutModal );
			}
		} );

		showReloadWarningModal.submit( function( event ) {
			event.preventDefault();
			openModal( reloadWarningModal );
		} );

		reloadWarningModal.on( 'close', function() {
			closeModal( reloadWarningModal );
		} );

		showMigrateUsersModal.click( function( event ) {
			event.preventDefault();
			openModal( migrateModal );
		} );

		migrateModal.on( 'close', function() {
			closeModal( migrateModal );
		} );

		errorModal.on( 'display', function() {
			openModal( errorModal );
		} );

		successModal.on( 'display', function() {
			openModal( successModal );
		} );

		if ( wizardModal.length ) {
			openModal( wizardModal );
		}

		showDowngradeModal.submit( function( event ) {
			if ( !modalOpened ) {
				actionForm = $( this );
				event.preventDefault();
				openModal( downgradeModal );
			}
		} );

		showCreditCardModal.submit( function( event ) {
			event.preventDefault();
			openModal( creditCardModal );
		} );

		// close modals
		closeModalButton.click( function( event ) {
			event.preventDefault();
			var modal = $( this ).parents().find( modalBackdropClass );
			closeModal( modal );
		} );

		// close modals with ESC click
		$( document ).keyup( function( e ) {
			if ( e.keyCode === 27 ) {
				clickCloseModal( e );
			}
		} );

		// close modals with backdrop click
		$( document ).mouseup( function( e ) {
			if ( modalOpened ) {
				clickCloseModal( e );
			}
		} );

	} catch ( e ) {
		Sentry.captureException( e );
	}
})( jQuery );
