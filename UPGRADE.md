# Upgrade from Notification Center 1.x to 2.x

* The built-in Postmark gateway has been removed.
* The built-in queue gateway has been removed.
* The built-in file gateway has been removed.
* Embedding images in e-mails is not supported anymore.
* Attachment templates are not supported anymore.
* The configurable flattening delimiter in the e-mail notification type has been removed.
* The configurable template in the notification type has been removed.
* The Notification Center integration for the Contao core "Registration" front end module has
  been changed. If you want to use the Notification Center to send registration e-mails, update
  to the new "Registration (Notification Center)" module. Also see docs.
* The corresponding language does not need an exact match of the root page language settings
  anymore. It will try to fall back to the general locale first, before taking the one that is
  configured to be fallback. E.g. (`de_CH` first, then `de` and only then the fallback).
* `contao/newsletter-bundle` integration: If you did use the "Activate (Notification Center)" front end module,
  you will have to adjust your workflow. This module has been removed. However, the "Subscribe (Notification Center)"
  now has a second forward page setting. You can use this one in order to have a separate confirmation page.
* Tokens will not be validated in the back end anymore. Basically because it's totally okay to write something
  like `##something-not-token-related##` in your message, and you should be able to write this.
* The `filenames` token introduced in 1.7 has been removed. It's a very specific use case which can be provided very easily
  as a third party bundle now (not easily possible before and thus part of the core in 1.7).
* The `member_*` tokens in the `lost_password` notification type used to contain the raw database values, they are now
  formatted the same way as in all the other notification types. Use `member_raw_*` if you need to access the raw values.

NOTE: Please, thoroughly test all your processes involving notifications after the upgrade. Make sure all the tokens
you've used are still working!