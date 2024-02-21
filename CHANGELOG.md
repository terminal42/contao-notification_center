# Version 2.0

This is a new major version which means **it introduces BC breaks**. No extension compatible to version `1.*` of the
Notification Center will be compatible with `2.*`. Moreover, some features have been removed while new ones have
been added. Check the [UPGRADE.md](UPGRADE.md) to know how to update.

## New

* Notification Center can now automatically generate the text variant based on HTML for e-mails. It
  does try to be smart about converting links, lists etc.
* The corresponding language does not need an exact match of the root page language settings
  anymore. It will try to fall back to the general locale first, before taking the one that is
  configured to be fallback. E.g. (`de_CH` first, then `de` and only then the fallback).
* You can now configure `start` and `stop` publish times for messages.
* You can now configure a publishing condition for every message which is incredibly powerful. E.g. you can
  now only send a message if a token contains a given value. E.g. `##form_department## === 'department_a'` will
  only send that message, if `##form_department##` contains `department_a`.
* The "Personal data" change notification contains two new tokens: `comparison_text` and `comparison_html` which
  contain a simple table of all changes simplifying the use case of informing a member about their changes tremendously.
* The "Subscribe (Notification Center)" front end module (for newsletters) now allows you to define two different 
  redirect pages, one when registering (always been there) and one when the registration has been confirmed using 
  double opt-in (new).