jQuery( document ).ready( function($) {

	var page_builder = function() {

		var pb = this;
		
		pb.init = function() {
			pb.tabs( '.cherry-settings-tabs' );
			pb.saveEvent();
		};

		pb.tabs = function( selectors ) {
			jQuery( selectors ).tabs();
		};

		pb.saveEvent = function() {
			jQuery( '.cherry-settings-tabs form' ).submit( function( e ) {
				jQuery( this ).ajaxSubmit({
					success: function() {
						console.log('success');
						pb.noticeCreate( 'success', 'Success!' );
					},
					error: function() {
						console.log( 'error' );
					},
					timeout: 5000
				});
				
				e.preventDefault();
			});
		};

		pb.noticeCreate = function( type, message ){
			var
				notice = $('<div class="notice-box ' + type + '-notice"><span class="dashicons"></span><div class="inner">' + message + '</div></div>')
			,	rightDelta = 0
			,	timeoutId
			;

			jQuery('body').prepend( notice );
			reposition();
			rightDelta = -1*(notice.outerWidth( true ) + 10);
			notice.css({'right' : rightDelta });

			timeoutId = setTimeout( function () { notice.css({'right' : 10 }).addClass('show-state') }, 100 );
			timeoutId = setTimeout( function () {
				rightDelta = -1*(notice.outerWidth( true ) + 10);
				notice.css({ right: rightDelta }).removeClass('show-state');
			}, 4000 );
			timeoutId = setTimeout( function () { notice.remove(); clearTimeout(timeoutId); }, 4500 );

				function reposition(){
					var
						topDelta = 100
					;
					$('.notice-box').each(function( index ){
						$( this ).css({ top: topDelta });
						topDelta += $(this).outerHeight(true);
					})
				}
		}
	}

	var pb = new page_builder();
	pb.init();
} );