<?php
namespace StirlingBrandworks;

use StirlingBrandworks\GFEventRegistrationRemindersAddOn\ConfirmationEmail;
use StirlingBrandworks\GFEventRegistrationRemindersAddOn\ReminderEmail;

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
     * @link https://docs.gravityforms.com/gfaddon/#getting-started
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
     * @link https://docs.gravityforms.com/gfaddon/#initialization
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        add_action('gf_err_cron', [$this, 'handle_cron']);
    }

    /**
     * Configures which columns should be displayed on the feed list page.
     * 
     * @link https://docs.gravityforms.com/gffeedaddon/#adding-columns-to-feed-list
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
     * Scripts to be loaded on the feed form page
     * 
     * @link https://docs.gravityforms.com/include-scripts-styles-with-addon-framework/
     * @link http://gitlab.annexe.solutions/alotte/stageline/blob/828f8026fe44aaf2441cce4000698dcaf7d86f49/wp-content/plugins/gravity-forms-list-field-date-picker/gravity-forms-list-field-datepicker-addon.php#L15
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
     * @link https://docs.gravityforms.com/gffeedaddon/
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
                        'label'   => esc_html__('Registration Confirmation', 'event-registration-reminders'),
                        'type'    => 'checkbox',
                        'name'    => 'registrationConfirmation',
                        'tooltip' => esc_html__('Confirmation email will be sent immediately after the user submits the form', 'event-registration-reminders'),
                        'choices' => [
                            [
                                'label' => 'Send email immediately after initial registration',
                                'name'  => 'registrationConfirmationEnabled',
                                'default_value'   => '1',
                            ]
                        ],
                    ],
                    [
                        'label'   => esc_html__('Send RSVP Changes to', 'event-registration-reminders'),
                        'type'    => 'text',
                        'name'    => 'replyToEmail',
                        'tooltip' => esc_html__('The email that will receive RSVP replies.', 'event-registration-reminders'),
                        'class'   => 'small',
                        'default_value'   => get_option('admin_email'),
                        'feedback_callback' => 'is_email',
                        'validate_callback' => [ $this, 'validate_email' ],
                        'required'   => 1,
                    ],
                    [
                        'label'   => esc_html__('Date to Send Reminder', 'event-registration-reminders'),
                        'type'    => 'text',
                        'name'    => 'reminderDate',
                        'tooltip' => esc_html__('The date to send the reminder', 'event-registration-reminders'),
                        'class'   => 'datepicker',
                        'required'   => 1,
                    ],
                    [
                        'label'   => esc_html__('Note', 'event-registration-reminders'),
                        'type'    => 'textarea',
                        'name'    => 'reminderNote',
                        'tooltip' => esc_html__('Optionally include a note to send in the emails.', 'event-registration-reminders'),
                    ],
                    // @link https://github.com/gravityforms/event-registration-reminders/blob/6ebc6f06a6da3120ac94caef00779f2fab34e8a6/class-gfevent-registration-reminders.php#L218
                    [
                        'name'      => 'mappedFields',
                        'label'     => esc_html__('Map Fields', 'event-registration-reminders'),
                        'type'      => 'field_map',
                        'field_map' => [
                            [
                                'name'       => 'firstName',
                                'label'      => esc_html__('Registrant First Name', 'event-registration-reminders'),
                                'required'   => 0,
                                'field_type' => array( 'name' ),
                            ],
                            [
                                'name'       => 'email',
                                'label'      => esc_html__('Registration Email', 'event-registration-reminders'),
                                'required'   => 1,
                                'field_type' => array( 'email', 'hidden' ),
                                'tooltip' => esc_html__('The email address that will receive the reminders', 'event-registration-reminders'),
                            ],
                        ],
                    ],
                    [
                        'type'  => 'feed_condition',
                        'name'  => 'feed_condition',
                        'label' => 'Feed Condition',
                    ]
                ],
            ],
        ];
    }

    /**
     * Whitelist the entry meta keys for confirmations and reminders
     * 
     * @see https://docs.gravityforms.com/using-entry-meta-with-add-on-framework/
     *
     * @param  array $entry_meta
     * @param  int   $form_id
     * @return array $entry_meta
     */
    public function get_entry_meta( $entry_meta, $form_id )
    {
        $entry_meta['confirmationSent'] = array(
            'label' => 'Confirmation Sent',
            'is_numeric' => true
        );

        $entry_meta['reminderSent'] = array(
            'label' => 'Reminder Sent',
            'is_numeric' => true
        );

        return $entry_meta;
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
     * @link https://docs.gravityforms.com/gffeedaddon/#processing-feeds
     */
    public function process_feed( $feed, $entry, $form )
    {
        gform_update_meta($entry['id'], 'reminderSent', 0);
        $entry['reminderSent'] = 0;

        gform_update_meta($entry['id'], 'confirmationSent', 0);
        $entry['confirmationSent'] = 0;

        $confirmation_enabled = isset($feed['meta']['registrationConfirmationEnabled']) && $feed['meta']['registrationConfirmationEnabled'] == 1;

        if ($confirmation_enabled) { 
            $to_email = $this->get_field_value($form, $entry, $feed['meta']['mappedFields_email']);
            $to_name = $this->get_field_value($form, $entry, $feed['meta']['mappedFields_firstName']);

            $email = new ConfirmationEmail(
                $to_email,
                $to_name,
                $feed, 
                $entry, 
                $form
            );

            $email->send();

            gform_update_meta($entry['id'], 'confirmationSent', 1);
            $entry['confirmationSent'] = 1;
        }

        return $entry;
    }

    /**
     * Process the recurring cron job
     * 
     * Send reminder emails if they haven't been sent already and it's the right date.
     *
     * @return void
     */
    public function handle_cron()
    {
        $debug = defined('WP_DEBUG_GF_ERR_CRON') && WP_DEBUG_GF_ERR_CRON === true;
        $forms = \GFAPI::get_forms();

        foreach ($forms as $form) {
            if ($debug) {
                error_log('Processing form ' . $form['id'] . '...');
            }
            $feeds = \GFAPI::get_feeds(null, $form['id'], $this->_slug);
            if (!$feeds || is_wp_error($feeds)) {
                continue;
            }
            foreach ($feeds as $feed) {

                if (!$debug && $feed['meta']['reminderSent'] === true) {
                    continue;
                }

                if (strtotime($feed['meta']['reminderDate']) > strtotime(date('Y-m-d'))) {
                    continue;
                }

                
                $search_criteria = [
                    'status' => 'active',
                    'field_filters' => []
                ];

                if (!$debug) {
                    $search_criteria['field_filters'][] = [
                        'key' => 'reminderSent',
                        'value' => 0
                    ];
                }
                

                $paging = ['page_size' => 200];

                $entries = \GFAPI::get_entries(
                    $form['id'],
                    $search_criteria,
                    null,
                    $paging
                );

                if ($debug) {
                    error_log('Found ' . count($entries) . ' for ' . $form['id'] . '. Sending...');
                }

                foreach ($entries as $entry) {

                    $to_email = $this->get_field_value($form, $entry, $feed['meta']['mappedFields_email']);

                    // If someone accidentally includes the "Full Name" field for firstName, it will error out
                    // so we check to make sure it's a string, otherwise just empty it out.
                    if (is_string($this->get_field_value($form, $entry, $feed['meta']['mappedFields_firstName']))) {
                        $to_name = $this->get_field_value($form, $entry, $feed['meta']['mappedFields_firstName']);
                    } else {
                        $to_name = '';
                    }

                    try {
                        $reminder_email = new ReminderEmail(
                            $to_email,
                            $to_name,
                            $feed, 
                            $entry, 
                            $form
                        );
                        $email_sent = $reminder_email->send();
                        if (is_wp_error($email_sent)) {
                            throw new \Exception($email_sent->get_error_message());
                        } else {
                            if ($debug) {
                                error_log('Email sent for entry ' . $entry['id'] . ' to ' . $to_email);
                            }
                            gform_update_meta($entry['id'], 'reminderSent', 1);
                        }
                    } catch (Exception $e) {
                        error_log(
                            sprintf(
                                'Error sending reminder email. Form ID: %s | Entry ID: %s | Email: %s | Error: %s',
                                $form['id'],
                                $entry['id'],
                                $to_email,
                                $e->getMessage()
                            )
                        );
                    } 
                }
            }
        }
    }
}