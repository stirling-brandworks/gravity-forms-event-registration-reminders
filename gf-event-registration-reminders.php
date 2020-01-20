<?php
/**
 * Plugin Name:       Gravity Forms Event Registration Reminders
 * Plugin URI:        https://bedfordlibrary.net
 * Description:       Extends Gravity Forms to allow event registration email reminders for registered attendees.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Brian Hanna, Stirling Brandworks
 * Author URI:        https://stirlingbrandworks.com
 * Text Domain:       event-registration-reminders
 */

\GFForms::include_feed_addon_framework();

require_once 'inc/class-email.php';
require_once 'inc/class-confirmation-email.php';
require_once 'inc/class-reminder-email.php';
require_once 'inc/class-addon.php';

function _run_sb_err()
{
    new StirlingBrandworks\GFEventRegistrationRemindersAddOn();
}

_run_sb_err();

// Schedule the cron job, if it isn't already scheduled.
add_action(
    'init', function () {
        if (! wp_next_scheduled('gf_err_cron') ) {
            wp_schedule_event(time(), 'twicedaily', 'gf_err_cron');
        }
    }
);

// Check for dependency activation
// @link https://rocketsquirrel.org/@duplaja/wordpress/checking-for-a-plugin-dependency-on-activation
register_activation_hook(__FILE__, '_sb_err_activate');
function _sb_err_activate()
{
    if (!class_exists('GFFeedAddOn') ) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('Please install and activate Gravity Forms.', 'event-registration-reminders'), 
            'Plugin dependency check', 
            array( 'back_link' => true )
        );
    }
}

register_activation_hook(__FILE__, '_sb_err_deactivate');
function _sb_err_deactivate()
{
    $timestamp = wp_next_scheduled('gf_err_cron');
    wp_unschedule_event($timestamp, 'gf_err_cron');
}