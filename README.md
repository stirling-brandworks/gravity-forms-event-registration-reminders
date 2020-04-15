# Event Registration Reminders for Gravity Forms


## Overview
Gravity Forms add-on for email reminders when using forms for event registration.

Adds an Event Registration Reminders feed to send reminders to anyone who filled out the form. Can also support sending out an automated confirmation email (if enabled).

### Notes

Since this plugin uses the `wp_mail()` function to send emails, this can often cause a large amount of reminders going out at once to fail. We recommend using third-party SMTP or mail handling such as Sendgrid or Mailgun.
