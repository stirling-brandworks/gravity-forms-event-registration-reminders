<?php 

namespace StirlingBrandworks\GFEventRegistrationRemindersAddOn;

class ReminderEmail extends Email
{

    public function send()
    {
        wp_mail(
            $this->to_email,
            sprintf(
                '%s is coming up!', 
                $this->event_title,
            ),
            sprintf(
                "Hello %s,\r\n\r\nWe are reminding you of your registration for %s:\r\n\r\n\r\n\r\n%s\r\n\r\nTo make any changes to your RSVP, please email %s or reply to this email.\r\n\r\nThanks, %s",
                $this->to_name,
                $this->event_title,
                $this->get_submitted_fields_table_html(),
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