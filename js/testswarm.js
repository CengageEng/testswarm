/**
 * JavaScript file for all Pages.
 *
 * @author John Resig, 2008-2011
 * @author Timo Tijhof, 2012
 * @since 0.1.0
 * @package TestSwarm
 */
jQuery(function ( $ ) {
	if ( $.fn.prettyDate ) {
		$( '.pretty' ).prettyDate();
	}

	if ( SWARM.auth ) {
		$( '.swarm-logout-link' ).on( 'click', function ( e ) {
			$( '<form>', {
				action: SWARM.conf.web.contextpath,
				method: 'POST',
				css: { display: 'none' }
			})
			.append(
				$( '<input type="hidden"/>' ).prop({ name: 'action', value: 'logout' }),
				$( '<input type="hidden"/>' ).prop({ name: 'authID', value: SWARM.auth.project.id }),
				$( '<input type="hidden"/>' ).prop({ name: 'authToken', value: SWARM.auth.sessionToken })
			)
			.appendTo( 'body' )
			.submit();

			e.preventDefault();
		});
	}
});
