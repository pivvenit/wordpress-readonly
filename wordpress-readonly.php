<?php

/**
 * Wordpress-readonly
 *
 * @package   pivvenit/wordpress-readonly
 * @author    PivvenIT
 * @copyright 2020 PivvenIT
 * @license   MIT
 *
 * @wordpress-plugin
 *
 * Plugin Name:  Readonly for WordPress
 * Plugin URI:   https://github.com/pivvenit/wordpress-readonly
 * Description:  WordPress plugin that makes the admin readonly during activation
 * Version:      1.0.0
 * Author:       PivvenIT
 * Author URI:   https://github.com/pivvenit
 * License:      MIT
 * Text Domain:  pivvenit-wordpress-readonly
 */

const PIVVENIT_WORDPRESS_READONLY_INFO = 'pivvenit_wordpress_readonly_info';
add_filter(
    'heartbeat_received',
    function ($response) {
        /** @var string $readonlyInfo */
        $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        if (!$readonlyInfo) {
            $readonlyInfo = new stdClass();
            $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
            $readonlyInfo->status = 'disabled';
            update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
        } else {
            $readonlyInfo = json_decode($readonlyInfo);
        }
        $response[PIVVENIT_WORDPRESS_READONLY_INFO] = $readonlyInfo;
        return $response;
    },
    10
);

add_filter(
    'admin_enqueue_scripts',
    function () {
        wp_enqueue_script(
            'pivvenit_wordpress_readonly_heartbeat',
            plugins_url('js/heartbeat.js', __FILE__),
            ['wp-i18n']
        );
        wp_set_script_translations('pivvenit_wordpress_readonly_heartbeat', 'pivvenit-wordpress-readonly');

        return true;
    }
);

add_action(
    'admin_notices',
    function () {
        /** @var string $readonlyInfo */
        $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        if (!$readonlyInfo) {
            return;
        }
        $readonlyInfo = json_decode($readonlyInfo);
        if ($readonlyInfo->status == 'disabled') {
            return;
        }
        $class = 'notice notice-warning';
        $message = __(
            'This website is in readonly mode, any changes you make from now on, will be lost.',
            'pivvenit-wordpress-readonly'
        );

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
);

add_filter(
    'wp_authenticate_user',
    function ($user) {
        /** @var string $readonlyInfo */
        $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        if (!$readonlyInfo) {
            return $user;
        }
        $readonlyInfo = json_decode($readonlyInfo);
        if ($readonlyInfo->status == 'disabled') {
            return $user;
        }
        return new WP_Error(
            'readonly_disabled_auth',
            __(
                "Login is temporary disabled due to maintenance. Please try again in a few moments.",
                'pivvenit-wordpress-readonly'
            )
        );
    },
    30,
    1
);


add_action(
    'plugins_loaded',
    function () {
        /** @var string $readonlyInfo */
        $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
        if (!$readonlyInfo) {
            return;
        }
        $data = json_decode($readonlyInfo);
        if ($data->status != "readonly") {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
        if ($GLOBALS['pagenow'] === 'wp-login.php' && !empty($_POST['wp-submit']) && $_POST['wp-submit'] === 'Log In') {
            return; // Handle this using the above authenticate filter
        }
        // Disallow all post requests
        if (wp_is_json_request()) {
            wp_send_json("Readonly mode", 503);
        } else {
            wp_die("Readonly mode");
        }
    }
);

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command(
        'readonly enable',
        function () {
            $readonlyInfo = new stdClass();
            /* The readonly process consists of two phases, at first a prepare period.
            * During the prepare period we display an alert to admin users currently logged in
            * and we disable the login.
            * At the start of the actual readonly phase we logout all users block all incoming HTTP POST requests.
            */

            $softReadonlyPeriod = new DateTime("now");
            $readonlyInfo->status = 'prepare';
            $readonlyInfo->id = $softReadonlyPeriod->getTimestamp();
            /** @var string $readonlyInfo */
            update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
            $displayDate = $softReadonlyPeriod->format('Y-m-d H:i:s');
            WP_CLI::success("[{$displayDate}] System is going in readonly mode in 60 seconds");
            WP_CLI::log("Waiting 60 seconds....");
            sleep(60);
            $beginReadonlyPeriod = new DateTime("now");
            $readonlyInfo->status = 'readonly';
            update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
            WP_CLI::success("Starting readonly period at {$beginReadonlyPeriod->format('Y-m-d H:i:s')}");
        }
    );

    WP_CLI::add_command(
        'readonly disable',
        function () {
            /** @var string $readonlyInfo */
            $readonlyInfo = get_option(PIVVENIT_WORDPRESS_READONLY_INFO);
            if (!$readonlyInfo) {
                $readonlyInfo = new stdClass();
                $readonlyInfo->id = (new DateTime("now"))->getTimestamp();
            } else {
                $readonlyInfo = json_decode($readonlyInfo);
            }
            $readonlyInfo->status = 'disabled';
            update_option(PIVVENIT_WORDPRESS_READONLY_INFO, json_encode($readonlyInfo));
            WP_CLI::success("Disabled readonly mode");
        }
    );
}
