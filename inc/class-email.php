<?php
namespace StirlingBrandworks\GFEventRegistrationRemindersAddOn;

abstract class Email
{
    public $to_email;
    public $to_name;
    public $feed;
    public $entry;
    public $form;

    public $event_title;
    public $reply_to_email;

    public function __construct($to_email, $to_name, $feed, $entry, $form)
    {
        $this->to_email = $to_email;
        $this->to_name = $to_name;
        $this->feed = $feed;
        $this->entry = $entry;
        $this->form = $form;

        $this->event_title = $this->form['title'];
        $this->reply_to_email = $this->feed['meta']['replyToEmail'];
    }

    protected function get_reminder_note_text()
    {
        $reminder_note = $this->feed['meta']['reminderNote'];
        if (!$reminder_note) { 
            return '';
        }
        return sprintf("Note: %s\r\n\r\n\r\n\r\n", $reminder_note);
    }

    public function get_submitted_fields_table_html()
    {
        return \GFCommon::get_submitted_fields(
            $this->form,
            $this->entry,
            false,
            false,
            'text'
        );
    }
}