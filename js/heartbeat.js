jQuery( document ).on( 'heartbeat-tick', function ( event, data ) {
    // If there is no readonly info available, do nothing
    if ( ! data.pivvenit_wordpress_readonly_info ) {
        return;
    }
    const { __ } = wp.i18n;

    /* Determine in which phase we are.
     * There are two phases: the grace period, and the actual readonly
     * During the grace period, we only notify the user that they should stop editing (and new logins are disabled).
     * During the readonly period, changes are actually blocked server side.
     */


    const upgradeInProgress = __( 'This website is in readonly mode, any changes you make from now on, will be lost.', 'pivvenit-wordpress-readonly' );
    const readonlyInfo = data.pivvenit_wordpress_readonly_info;
    if (window.wp.data !== undefined) {
        window.wp.data.dispatch( 'core/notices' ).createNotice(
            'warning',
            upgradeInProgress,
            {
                id: readonlyInfo.phase1_start,
                isDismissible: false
            }
        );
    }

    if (jQuery('#test').length > 0) {
        return;
    }

    const $insertAfter = jQuery( '.wrap h1, .wrap h2' ).first();
    const $elem = jQuery(`<div id='test'></div>`)
        .addClass('notice')
        .addClass('notice-warning')
        .addClass('is-dismissible')
        .append("<p>Upgrade in progress</p>");
    $elem.insertAfter($insertAfter);
});