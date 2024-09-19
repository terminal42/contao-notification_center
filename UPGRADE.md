# Upgrade from Notification Center 1.x to 2.x

* The built-in Postmark gateway has been removed.
* The built-in queue gateway has been removed.
* The built-in file gateway has been removed.
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
* File attachment tokens do not work with absolute paths anymore. If e.g. your `##my_file##` token contained
  `/path/to/file.jpg`, you cannot use that as attachment token anymore. Developers are expected to work with the 
  "Bulky Item Storage" and use "Vouchers" accordingly. See documentation for more information. This is due to 
  several reasons:
  * Security: Token sources are often unknown. By randomly attaching a token value if it `file_exist()`s, there's a 
    boatload of stuff that can go wrong. Forcing developers to work with the Bulky Item Storage ensures, they 
    validate and think about the source of the file.
  * Reproducibility: The Bulky Item Storage ensures, that files remain present for a configurable amount of days 
    which allows to e.g. re-send failed notifications. It's a unified process working the same for all files.
  * Immutability: The Bulky Item Storage ensures, that the same file is sent that would have been sent at the time 
    the notification was created. It's a unified process working the same for all files.
  * Metadata: The Bulky Item Storage allows for metadata which was missing for tokens with absolute file paths only.
  * Virtual Filesystem: The Bulky Item Storage allows working with Contao's Virtual Filesystem. So you can configure 
    your bulky items to be stored on S3 for example.


NOTE: Please, thoroughly test all your processes involving notifications after the upgrade. Make sure all the tokens
you've used are still working!