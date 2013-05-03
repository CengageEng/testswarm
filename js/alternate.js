/**
 * JavaScript file for the "run" page in the browser.
 *
 * @author John Resig, 2008-2011
 * @author Timo Tijhof, 2012
 * @since 0.1.0
 * @package TestSwarm
 */
(function ( $, SWARM, undefined ) {
    var currRunId, currRunUrl, testTimeout, pauseTimer, cmds, errorOut, testWindow;

    function msg( htmlMsg ) {
        $( '#msg' ).html( htmlMsg );
    }

    function log( htmlMsg ) {
        $( '#history' ).prepend( '<li><strong>' +
            new Date().toString().replace( /^\w+ /, '' ).replace( /:[^:]+$/, '' ) +
            ':</strong> ' + htmlMsg + '</li>'
        );

        msg( htmlMsg );
    }

    /**
     * Softly validate the SWARM object
     */
    if ( !SWARM.client_id || !SWARM.conf ) {
        $( function () {
            msg( 'Error: No client id configured! Aborting.' );
        });
        return;
    }

    errorOut = 0;
    cmds = {
        reload: function () {
            window.location.reload();
        }
    };

    /**
     * @param query String|Object: $.ajax "data" option, converted with $.param.
     * @param retry Function
     * @param ok Function
     */
    function retrySend( query, retry, ok ) {
        function error( errMsg ) {
            if ( errorOut > SWARM.conf.client.saveRetryMax ) {
                cmds.reload();
            } else {
                errorOut += 1;
                errMsg = errMsg ? (' (' + errMsg + ')') : '';
                msg( 'Error connecting to server' + errMsg + ', retrying...' );
                setTimeout( retry, SWARM.conf.client.saveRetrySleep * 1000 );
            }
        }

        $.ajax({
            type: 'POST',
            url: SWARM.conf.web.contextpath + 'api.php',
            timeout: SWARM.conf.client.saveReqTimeout * 1000,
            cache: false,
            data: query,
            dataType: 'json',
            success: function ( data ) {
                clearTimeout(testTimeout);
                if ( !data || data.error ) {
                    error( data.error.info );
                } else {
                    errorOut = 0;
                    ok.apply( this, arguments );
                }
            },
            error: function () {
                error();
            }
        });
    }

    function testTimedout( runInfo ) {
        // Don't use the cleanupTest promise,
        // the abort should be reported to the swarm right away,
        // regardless of whether the window has closed yet.
        // The next runDone/getTests call handles that instead.
        retrySend(
            {
                action: 'saverun',
                fail: 0,
                error: 0,
                total: 0,
                status: 3, // ResultAction::STATE_ABORTED
                report_html: 'Test Timed Out.',
                run_id: currRunId,
                client_id: SWARM.client_id,
                run_token: SWARM.run_token,
                results_id: runInfo.resultsId,
                results_store_token: runInfo.resultsStoreToken
            },
            function () {
                testTimedout( runInfo );
            },
            function ( data ) {
                if ( data.saverun === 'ok' ) {
                    SWARM.runDone();
                } else {
                    getTests();
                }
            }
        );
    }


    function runTests() {
        // Timeout after a period of time
        testTimeout = setTimeout( function () {
            testTimedout( runInfo );
        }, SWARM.conf.client.runTimeout * 1000 );

    }

    // Needs to be a publicly exposed function, so that when inject.js
    // does a (potentially cross-domain) <form> submission to the,
    // SaverunPage, it (testWindow) can call this as window.opener.SWARM.runDone().
    SWARM.runDone = function () {
        window.parent.postMessage('CLOSE', '*');
    };

    function handleMessage(e) {
        e = e || window.event;
        retrySend( e.data, function () {
            handleMessage(e);
        }, SWARM.runDone );
    }

    /**
     * Bind
     */
    if ( window.addEventListener ) {
        window.addEventListener( 'message', handleMessage, false );
    } else if ( window.attachEvent ) {
        window.attachEvent( 'onmessage', handleMessage );
    }

    $( document).ready( function () {
        runTests();
    });

}( jQuery, SWARM ) );
