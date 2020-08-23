<?php
/**
 * Plugin Name:  Readonly for Wordpress
 * Plugin URI:   https://github.com/pivvenit/wordpress-readonly
 * Description:  Wordpress plugin that makes the admin readonly during activation
 * Version:      1.0.0
 * Author:       PivvenIT
 * Author URI:   https://pivvenit.nl
 * License:      MIT
 * Text Domain:  pivvenit-wordpress-readonly
 */
add_filter( 'heartbeat_received', function($response, $data) {
    $readonlyInfo = get_option('pivvenit_wordpress_readonly_info');
    if (!$readonlyInfo) {
        return $response;
    }
    $response['pivvenit_wordpress_readonly_info'] = json_decode($readonlyInfo, false);
    return $response;
}, 10, 2 );

add_filter('admin_enqueue_scripts', function() {
    wp_enqueue_script('pivvenit_wordpress_readonly_heartbeat', plugins_url('js/heartbeat.js',__FILE__ ), ['wp-i18n']);
    wp_set_script_translations('pivvenit_wordpress_readonly_heartbeat', 'pivvenit-wordpress-readonly');
});

add_action('plugins_loaded', function() {
    $readonlyInfo = get_option('pivvenit_wordpress_readonly_info');
    if (!$readonlyInfo) {
        return;
    }
    $data = json_decode($readonlyInfo);
    $now = new DateTime("now");
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return;
    }
    if (isset($data->phase2_start) && $now > new DateTime("@{$data->phase2_start}ts")) {
        if (wp_is_json_request()) {
            wp_send_json("Readonly mode", 503);
        } else {
            wp_die("Readonly mode");
        }
    }

});


register_deactivation_hook(__FILE__, function() {
    delete_option('pivvenit_wordpress_readonly_info');
});

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'readonly enable', function() {
        $readonlyInfo = new \stdClass();
        /* The readonly process consists of two phases, at first a grace period.
         * During the grace period we display an alert to admin users currently logged in
         * and we disable the login.
         * At the start of the actual readonly phase we logout all users block all incoming HTTP POST requests.
         */

        $softReadonlyPeriod = new DateTime("now");
        $readonlyInfo->phase1_start = $softReadonlyPeriod->getTimestamp();
        update_option('pivvenit_wordpress_readonly_info', json_encode($readonlyInfo));
        WP_CLI::success("[{$softReadonlyPeriod->format('Y-m-d H:i:s')}] System is going in readonly mode in 60 seconds");
        WP_CLI::log("Waiting 60 seconds....");
        sleep(60);
        $beginReadonlyPeriod = new DateTime("now");
        $readonlyInfo->phase2_start = $beginReadonlyPeriod->getTimestamp();
        update_option('pivvenit_wordpress_readonly_info', json_encode($readonlyInfo));
        WP_CLI::success("Starting readonly period at {$beginReadonlyPeriod->format('Y-m-d H:i:s')}");
    } );

    WP_CLI::add_command( 'readonly disable', function() {
        delete_option('pivvenit_wordpress_readonly_info');
        WP_CLI::success("Disabled readonly mode");
    });
}