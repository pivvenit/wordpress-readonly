jQuery(document).on('heartbeat-tick', function (event, data) {
        // If there is no readonly info available, do nothing
        if (!data.pivvenit_wordpress_readonly_info) {
            return;
        }

        const {__} = wp.i18n;

        /* Determine in which phase we are.
         * There are two phases: the prepare period, and the actual readonly
         * During the prepare period, we only notify the user that they should stop editing (and new logins are disabled).
         * During the readonly period, changes are actually blocked server side.
         */


        const upgradeInProgress = __('This website is in readonly mode, any changes you make from now on, will be lost.', 'pivvenit-wordpress-readonly');
        const readonlyInfo = data.pivvenit_wordpress_readonly_info;

        if (data.pivvenit_wordpress_readonly_info.status == "disabled") {
            if (jQuery(`#${readonlyInfo.id}`).length > 0) {
                jQuery(`#${readonlyInfo.id}`).remove();
                location.reload();
                return;
            }
            if (window.wp.data !== undefined && window.wp.data.dispatch('core/notices')) {
                window.wp.data.dispatch('core/notices').removeNotice(readonlyInfo.id)
                location.reload();
                return;
            }
        }

        if (window.wp.data !== undefined && window.wp.data.dispatch('core/notices')) {
            window.wp.data.dispatch('core/notices').createNotice(
                'warning',
                upgradeInProgress,
                {
                    id: readonlyInfo.id,
                    isDismissible: false
                }
            );
        }

        if (jQuery(`#${readonlyInfo.id}`).length > 0) {
            return;
        }

        const $insertAfter = jQuery('.wrap h1, .wrap h2').first();
        const $elem = jQuery(`<div id='${readonlyInfo.id}'></div>`)
            .addClass('notice')
            .addClass('notice-warning')
            .addClass('is-dismissible')
            .append(`<p>${upgradeInProgress}</p>`);
        $elem.insertAfter($insertAfter);
    }
);