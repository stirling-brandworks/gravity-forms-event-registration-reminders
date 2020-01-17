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

namespace StirlingBrandworks;

\GFForms::include_feed_addon_framework();

/**
 * Gravity Forms Event Registration Reminders AddOn
 * 
 * Use the GF AddOn framework to configure event reminders.
 */
class GFEventRegistrationRemindersAddOn extends \GFFeedAddOn
{
    protected $_version = '1.0';
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'gf-event-registration-reminders';
    protected $_path = 'gf-event-registration-reminders/gf-event-registration-reminders.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Event Registration Reminders';
    protected $_short_title = 'Event Registration';

    /**
     * @var object|null $_instance If available, contains an instance of this class.
     */
    private static $_instance = null;
 
    /**
     * Returns an instance of this class, and stores it in the $_instance property.
     * 
     * @see https://docs.gravityforms.com/gfaddon/#getting-started
     *
     * @return object $_instance An instance of this class.
     */
    public static function get_instance()
    {
        if (self::$_instance == null ) {
            self::$_instance = new self();
        }
 
        return self::$_instance;
    }

    /**
     * Register any hooks to be run
     * 
     * @see https://docs.gravityforms.com/gfaddon/#initialization
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Configures which columns should be displayed on the feed list page.
     * 
     * @see https://docs.gravityforms.com/gffeedaddon/#adding-columns-to-feed-list
     *
     * @return array
     */
    public function feed_list_columns()
    {
        return array(
            'feedName'  => esc_html__('Name', 'event-registration-reminders')
        );
    }

    /**
     * Scripts to be loaded on the tab
     * 
     * @see https://docs.gravityforms.com/include-scripts-styles-with-addon-framework/
     *
     * @return void
     */
    public function scripts()
    {
        wp_enqueue_script('gform_datepicker_init');
        return parent::scripts();
    }

    /**
     * The settings which should be rendered on the 
     * Form Settings > Event Registration > Feed screen
     * 
     * @see https://docs.gravityforms.com/gffeedaddon/
     *
     * @return array
     */
    public function feed_settings_fields()
    {
        return [
            [
                'title'  => esc_html__('Event Registration Settings', 'event-registration-reminders'),
                'fields' => [
                    [
                        'label'   => esc_html__('Feed name', 'event-registration-reminders'),
                        'type'    => 'text',
                        'name'    => 'feedName',
                        'tooltip' => esc_html__('A label for this feed. Feel free to use the default', 'event-registration-reminders'),
                        'class'   => 'small',
                    ],
                    [
                        'label'   => esc_html__('Date to Send Reminder', 'event-registration-reminders'),
                        'type'    => 'text',
                        'name'    => 'reminder_date',
                        'tooltip' => esc_html__('The date to send the reminder', 'event-registration-reminders'),
                        'class'   => 'datepicker'
                    ],
                    // @see https://github.com/gravityforms/event-registration-reminders/blob/6ebc6f06a6da3120ac94caef00779f2fab34e8a6/class-gfevent-registration-reminders.php#L218
                    [
                        'name'      => 'mappedFields',
                        'label'     => esc_html__('Map Fields', 'event-registration-reminders'),
                        'type'      => 'field_map',
                        'field_map' => [
                            [
                                'name'       => 'email',
                                'label'      => esc_html__('Registration Email', 'event-registration-reminders'),
                                'required'   => 0,
                                'field_type' => array( 'email', 'hidden' ),
                                'tooltip' => esc_html__('The email address that will receive the reminders', 'event-registration-reminders'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Handle the form submission
     * 
     * Send the initial registration confirmation email and schedule the reminder.
     *
     * @param  array $feed
     * @param  array $entry
     * @param  array $form
     * @return void
     * 
     * @see https://docs.gravityforms.com/gffeedaddon/#processing-feeds
     */
    public function process_feed( $feed, $entry, $form )
    {
        $email = $this->get_field_value($form, $entry, $feed['meta']['mappedFields_email']);

        wp_mail(
            $email,
            sprintf(
                'Your Registration for %s', 
                $form['title'],
            ),
            sprintf(
                'We have received your registration for %s on %s. We will remind you about this on %s.',
                $form['title'],
                '{event date}',
                '{reminder date}'
            )
        );
    }
}

function _run_sb_err()
{
    new GFEventRegistrationRemindersAddOn();
}

_run_sb_err();

// Check for dependency activation
// @see https://rocketsquirrel.org/@duplaja/wordpress/checking-for-a-plugin-dependency-on-activation
register_activation_hook(__FILE__, __NAMESPACE__ . '\\_sb_err_activate');
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