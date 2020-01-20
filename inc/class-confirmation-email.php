<?php 

namespace StirlingBrandworks\GFEventRegistrationRemindersAddOn;
use StirlingBrandworks\GFEventRegistrationRemindersAddOn\Email;

class ConfirmationEmail extends Email
{

    public function send()
    {
        $reminder_date = $this->feed['meta']['reminderDate'];

        wp_mail(
            $this->to_email,
            sprintf(
                'Your Registration for %s', 
                $this->event_title,
            ),
            sprintf(
                "Hello %s,\r\n\r\nWe have received your registration for %s:\r\n\r\n\r\n\r\n%s\r\n\r\nWe will remind you about this on %s.\r\nTo make any changes to your RSVP, please email %s or reply to this email.\r\n\r\nThanks, %s",
                $this->to_name,
                $this->event_title,
                $this->get_submitted_fields_table_html(),
                $reminder_date,
                $this->reply_to_email,
                get_bloginfo('name')
            ),
            [
                sprintf(
                    'Reply-To: %s <%s>',
                    \get_bloginfo('name') . ' RSVPs',
                    $this->reply_to_email
                )
            ]
        );
    }

}