# Upgrade from Notification Center 1.x to 2.x

* The built-in Postmark gateway has been removed.
* The built-in queue gateway has been removed.
* The built-in file gateway has been removed.
* Embedding images in e-mails is not supported anymore.
* Attachment templates are not supported anymore.
* The configurable flattening delimiter in the e-mail notification type has been removed.
* The configurable template in the notification type has been removed.
* The `contao/newsletter-bundle` integration has been removed.
* The corresponding language does not need an exact match of the root page language settings
  anymore. It will try to fall back to the general locale first, before taking the one that is
  configured to be fallback. E.g. (`de_CH` first, then `de` and only then the fallback).

# New

* Notification Center can now automatically generate the text variant based on HTML for e-mails. It
  does try to be smart about converting links, lists etc.
* The corresponding language does not need an exact match of the root page language settings
  anymore. It will try to fall back to the general locale first, before taking the one that is
  configured to be fallback. E.g. (`de_CH` first, then `de` and only then the fallback).
* You can now configure `start` and `stop` publish times for messages.
* You can now configure a publishing condition for every message which is incredibly powerful. E.g. you can
  now only send a message if a token contains a given value. E.g. `##form_department## === 'department_a'` will
  only send that message, if `##form_department##` contains `department_a`.