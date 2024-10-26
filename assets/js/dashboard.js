(function( $ ) {
	try {
		// Actions
		var generateOfflineCodesButton        = $( '.js-generate-offline-codes' ),
		    migrateUsersButton                = $( '.js-open-migrate-users-modal' ),
	        migrateUsersButtonConfirm         = $( '.js-twofas-migrate-users' ),
		    reloadQrCodeForm                  = $( '.js-reload-qr-code' ),
		    reloadQrCodeAndSetModalStatusForm = $( '.js-reload-qr-code-and-set-modal-status' ),
		    sendSmsButton                     = $( '.js-send-sms' ),
		    waitingButton                     = $( '.js-waiting-button' ),
		    waitingLink                       = $( '.js-waiting-link' ),
		    autofocusInput                    = $( '.js-autofocus' );

		// Modals
		var reloadWarningModal = $( '.twofas-reload-warning-modal' ),
		    errorModal         = $( '.twofas-error-modal' ),
		    successModal       = $( '.twofas-success-modal' ),
		    migrateModal       = $('.twofas-migrate-users-confirmation-modal');

		var trustedDevices      = $( '.twofas-trusted-device' ),
		    offlineCodesDate    = $( '.twofas-offline-codes-date' ),
		    myChart             = $( '#myChart' ),
		    errorModalMessage   = $( '#error-modal-message' ),
		    successModalMessage = $( '#success-modal-message' ),
		    qrImg               = $( 'img.twofas-qr-code' ),
		    qrSpinner           = $( '.twofas-spinner-container.twofas-qr-refresh' ),
		    userBoxTitle        = $('.twofas-admin-users-wrapper h2'),
		    tempBoxTitle        = '',
		    migrationIntervalId = 0;

		if (migrateUsersButtonConfirm.length) {
			migrateUsersButtonConfirm.on('click', migrateUsers);
		}

		function getErrorFromResponse( response ) {
			return JSON.parse( response.responseText ).error;
		}

		function handleErrorResponse( response ) {
			var error = getErrorFromResponse( response );

			if ( typeof error === 'undefined' ) {
				error = 'Server error occurred. Please try to refresh this page.';
			}

			errorModalMessage.html( error );
			errorModal.trigger( 'display' );
		}

		function handleReloadQrCodeSuccessfulResponse( response ) {
			var qrCode     = response.qrCode,
			    totpSecret = response.totpSecret;

			$( '.twofas-qr-code' ).attr( 'src', qrCode );
			$( '.twofas-totp-secret' ).html( totpSecret );
			$( '#totp-secret' ).val( totpSecret );
			$( '.twofas-mobile-config' ).attr( 'href', response.qrCodeMessage );

			qrImg.removeClass('twofas-hidden');
			qrSpinner.removeClass('twofas-visible');
		}

		function prepareList( elements ) {
			var list = '<code><pre>';
			var index;

			for ( index in elements ) {
				if ( elements.hasOwnProperty( index ) ) {
					list += elements[ index ] + '\r\n';
				}
			}

			list += '</pre></code>';

			return list;
		}

		function prepareHiddenInputs( elements ) {
			var inputs = '';
			var index;

			for ( index in elements ) {
				if ( elements.hasOwnProperty( index ) ) {
					inputs += '<input type="hidden" name="code[' + index + ']" value="' + elements[ index ] + '" />';
				}
			}

			return inputs;
		}

		function generateOfflineCodes( event ) {
			event.preventDefault();

			var url    = $( '.twofas-generate-offline-codes-form' ).attr( 'action' ),
			    nonce  = $( '.twofas-generate-offline-codes-form > input[name="_wpnonce"]' ).val(),
			    button = $( this );

			button.addClass( 'twofas-wait' ).prop( 'disabled', true );

			var data = {
				_wpnonce: nonce
			};

			$.ajax( {
				url: url,
				type: 'post',
				data: data,
				success: function( response ) {
					var codes        = response.codes;
					var encodedCodes = encodeURIComponent( codes.join( '\n' ) );
					var list         = prepareList( codes );
					var inputs       = prepareHiddenInputs( codes );

					$( '.twofas-offline-stats-generated' ).hide();
					$( '.twofas-offline-codes-data' ).html( list );
					$( '.twofas-offline-codes-inputs' ).html( inputs );
					$( '.twofas-download-codes-link' ).attr( 'href', 'data:text/plain;charset=utf-8,' + encodedCodes );
					$( '.twofas-offline-codes-list' ).show();
					$( '.js-backup-codes-tick' )
						.removeClass( 'twofas-icon-tick' )
						.addClass( 'twofas-icon-tick-enabled' );

					button.removeClass( 'twofas-wait' ).prop( 'disabled', false );
				},
				error: function( response ) {
					button.removeClass( 'twofas-wait' ).prop( 'disabled', false );
					handleErrorResponse( response );
				}
			} );
		}

		function reloadQrCode() {
			var url   = $( '.twofas-reload-qr-code-form' ).attr( 'action' ),
			    nonce = $( '.twofas-buttons-bottom > input[name="_wpnonce"]' ).val(),
			    data  = {
				    _wpnonce: nonce
			    };

			qrImg.addClass('twofas-hidden');
			qrSpinner.addClass('twofas-visible');

			$.ajax( {
				url: url,
				type: 'post',
				data: data,
				success: handleReloadQrCodeSuccessfulResponse,
				error: handleErrorResponse
			} );
		}

		function reloadQrCodeAndSetModalStatus( event ) {
			event.preventDefault();

			if ( $( '#reload-modal-check' ).is( ':checked' ) ) {
				var url   = $( '.js-reload-qr-code-and-set-modal-status' ).attr( 'action' ),
				    nonce = $( '.js-reload-qr-code-and-set-modal-status > input[name="_wpnonce"]' ).val(),
				    data  = {
					    _wpnonce: nonce
				    };

				$.ajax( {
					url: url,
					type: 'post',
					data: data,
					error: handleErrorResponse
				} );

				$( '.twofas-reload-qr-code-form' ).removeClass( 'js-open-reload-warning-modal' ).addClass( 'js-reload-qr-code' );
			}

			reloadWarningModal.trigger( 'close' );

			reloadQrCode();
		}

		function sendSms( event ) {
			event.preventDefault();

			var button = $( this );

			button
				.addClass( 'twofas-wait' )
				.prop( 'disabled', true );

			var url         = $( '.twofas-send-sms-form' ).attr( 'action' ),
			    nonce       = $( '.twofas-send-sms-form > input[name="_wpnonce"]' ).val(),
			    phoneNumber = $( '#phone-number' ).val(),
			    data        = {
				    phone_number: phoneNumber,
				    _wpnonce: nonce
			    };

			$.ajax( {
				url: url,
				type: 'post',
				data: data,
				success: function( response ) {
					$( '#verified-phone-number' ).val( phoneNumber );
					$( '#authentication-id' ).val( response.authenticationId );
					$( '.twofas-sms-sent-button-text' ).hide();
					$( '.twofas-sms-sent-message' ).show();

					button.removeClass( 'twofas-wait' );

					$( '#twofas-token' ).prop( 'disabled', false );
					$( '.twofas-enable-token-btn' ).prop( 'disabled', false );
				},
				error: function( response ) {
					button
						.removeClass( 'twofas-wait' )
						.prop( 'disabled', false );

					handleErrorResponse( response );
				}
			} );
		}

		function getCountryByIp( index, element ) {
			$.get( 'https://ipapi.co/' + element.innerText + '/json/', function( data ) {
				var countryCode = data.country;

				element.innerText = countryCode ? countryCode : '';
				element.className += ' twofas-visible';
			} );
		}

		function migrateUsers( event ) {
			event.preventDefault();
			migrateModal.trigger('close');
			migrateUsersButton.attr('disabled', true);
			tempBoxTitle = userBoxTitle.html();

			var url         = $( '#twofas-migration-users-form' ).attr( 'action' ),
			    nonce       = $( '#twofas-migration-users-form > input[name="_wpnonce"]' ).val(),
			    data        = {
				    _wpnonce: nonce
			    },
				i = 0;

			$.ajax( {
				url: url,
				type: 'post',
				data: data,
				error: function( response ) {
					handleErrorResponse( response );
				}
			} );

			migrationIntervalId = setInterval(function() {
				i = ++i % 4;
				userBoxTitle.html(twofas.migrationInProgress+Array(i+1).join('.'));
			}, 500);
			myChart.data.labels[1] = twofas.migratedUsers;
			myChart.data.datasets[0].backgroundColor[1] = '#10b74a';
			myChart.update();
			$( '#js-legend' ).html( myChart.generateLegend() );
			getMigrationStatistics();
		}

		function getMigrationStatistics() {
			setTimeout(function () {
				$.ajax( {
					url: twofas.ajaxUrl,
					type: 'get',
					data: {
						'page': twofas.submenuDashboard,
						'twofas-action': 'migration-users-status'
					},
					success: function( response ) {
						var data = myChart.data;

						if (data.datasets.length > 0) {
							data.datasets[0].data[1] = response.migrated_users;
						}
						myChart.update();

						if ( response.migrated_users >= response.active_users_count ) {
							clearInterval( migrationIntervalId );
							userBoxTitle.html( twofas.migrationCompleted );
							successModalMessage.html( 'Migration completed successfully. Now please turn off and uninstall 2FAS Classic plugin.' );
							successModal.trigger( 'display' );
							migrateUsersButton.hide();
							setTimeout(function() {
								setChart();
								userBoxTitle.html(tempBoxTitle);
							}, 3000);
						} else {
							getMigrationStatistics();
						}

					},
					error: function( response ) {
						handleErrorResponse( response );
					}
				} );
			}, 1000);
		}

		function getMonthName( monthNumber ) {
			return twofas.months[ monthNumber ];
		}

		function getDate( timestamp ) {
			var date      = new Date( timestamp ),
			    monthName = getMonthName( date.getMonth() ),
			    day       = date.getDate(),
			    year      = date.getFullYear();

			return monthName + ' ' + day + ', ' + year;
		}

		function getTime( timestamp ) {
			var date    = new Date( timestamp ),
			    hours   = date.getHours(),
			    minutes = date.getMinutes(),
			    period  = hours >= 12 ? 'PM' : 'AM';

			if ( hours > 12 ) {
				hours -= 12;
			}

			if ( minutes < 10 ) {
				minutes = '0' + minutes;
			}

			return hours + ':' + minutes + ' ' + period;
		}

		function formatDate( index, element ) {
			var timestamp = element.innerText * 1000;

			if ( !isNaN( timestamp ) ) {
				element.innerText = getDate( timestamp );
			}
		}

		function formatTime( index, element ) {
			var timestamp = element.innerText * 1000;

			if ( !isNaN( timestamp ) ) {
				element.innerText = getTime( timestamp );
				element.className += ' twofas-visible';
			}
		}

		function formatOfflineCodesDate() {
			var timestamp = offlineCodesDate[ 0 ].innerText * 1000;

			if ( !isNaN( timestamp ) ) {
				var date      = new Date( timestamp ),
				    monthName = getMonthName( date.getMonth() ),
				    day       = date.getDate(),
				    year      = date.getFullYear();

				offlineCodesDate[ 0 ].innerText = day + ' ' + monthName + ' ' + year;
			}
		}

		function setButtonWaiting( event ) {
			event.preventDefault();
			var form = this;

			$( this )
				.find( '*[type="submit"]' )
				.addClass( 'twofas-wait' )
				.prop( 'disabled', true );

			setTimeout( function() {
				form.submit();
			}, 1 );

			return true;
		}

		function setLinkWaiting() {
			$( this )
				.addClass( 'twofas-wait' )
				.prop( 'disabled', true );
		}

		function setFocus() {
			this.value = this.value;
		}

		function setChart() {
			var ctx     = $( '#myChart' )[ 0 ].getContext( '2d' ),
			    data    = {
				    datasets: [ {
					    data: [
						    $( '.js-active-users-count' ).val(),
						    $( '.js-inactive-users-count' ).val()
					    ],
					    backgroundColor: [
						    '#202225',
						    '#eb1c23'
					    ],
					    borderWidth: 0
				    } ],
				    labels: [
					    twofas.activeUsers,
					    twofas.inactiveUsers
				    ]
			    },
			    options = {
				    responsive: true,
				    maintainAspectRatio: false,
				    legend: {
					    display: false
				    },
				    title: {
					    display: false
				    },
				    animation: {
					    animateScale: true,
					    animateRotate: true
				    },
				    tooltips: {
					    enabled: true
				    }
			    };

			myChart = new Chart( ctx, {
				type: 'doughnut',
				data: data,
				options: options
			} );

			$( '#js-legend' ).html( myChart.generateLegend() );
			myChart.update();
		}

		if ( trustedDevices.length ) {
			$( '.twofas-trusted-device-country' ).each( getCountryByIp );
			$( '.twofas-date' ).each( formatDate );
			$( '.twofas-hour' ).each( formatTime );
		}

		if ( offlineCodesDate.length ) {
			formatOfflineCodesDate();
		}

		if ( myChart.length ) {
			setChart();
		}

		autofocusInput.focus( setFocus );
		generateOfflineCodesButton.click( generateOfflineCodes );

		reloadQrCodeForm.submit( function( event ) {
			event.preventDefault();
			reloadQrCode();
		} );

		$( document ).on( 'click', '.js-reload-qr-code', function( event ) {
			event.preventDefault();
			reloadQrCode();
		} );

		reloadQrCodeAndSetModalStatusForm.submit( reloadQrCodeAndSetModalStatus );
		sendSmsButton.click( sendSms );
		waitingButton.submit( setButtonWaiting );
		waitingLink.click( setLinkWaiting );

	} catch ( e ) {
		Sentry.captureException( e );
		errorModalMessage.html( 'Server error occurred. Please try to refresh this page.' );
		errorModal.trigger( 'display' );
	}

})( jQuery );
