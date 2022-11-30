# Upgrade from Notification Center 1.x to 2.x

* The built-in Postmark gateway has been removed.
* The built-in queue gateway has been removed.
* The built-in file gateway has been removed.
* Embedding images in e-mails is not supported anymore.
* Attachment templates are not supported anymore.
* The configurable flattening delimiter in the e-mail notification type has been removed.
* The configurable template in the notification type has been removed.
* The `contao/newsletter-bundle` integration has been removed.
* The Notification Center integration for the Contao core "Registration" front end module has
  been changed. If you want to use the Notification Center to send registration e-mails, update
  to the new "Registration (Notifcation Center)" module. Also see docs.
* The corresponding language does not need an exact match of the root page language settings
  anymore. It will try to fall back to the general locale first, before taking the one that is
  configured to be fallback. E.g. (`de_CH` first, then `de` and only then the fallback).