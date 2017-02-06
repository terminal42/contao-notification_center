Notification Center Changelog
===========================

Version 1.4.1 (2017-??-??)
--------------------------

### Fixed
- Not all member data available in registration notification (#118)

Version 1.4.0 (2017-02-02)
--------------------------

### New
- Added a verification for valid token characters (#68)
- Support embedding images in e-mails (#109)
- Support for different flatten delimiter in core form notification (#111)


Version 1.3.6 (2016-05-04)
--------------------------

### Fixed
- Support the language variations (e.g. en-US) (#93)


Version 1.3.5 (2016-03-02)
--------------------------

### Fixed
- The extension is now compatible with PHP7 (see #86)


Version 1.3.4 (2016-02-09)
--------------------------

### Improved
- The extension is now compatible with Contao 4


Version 1.3.3 (2016-01-15)
--------------------------

### Improved
- Developers can now easier extend the existing tokens
- Attachment tokens in form notifications now also work when "save file" was enabled (#80)


Version 1.3.2 (2015-10-14)
--------------------------

### Fixed
- Dependencies could not be resolved due to missing \_autoload module (#82)
- Support getInstance method handling for sendNotificationMessage hook (#79)


Version 1.3.1 (2015-10-09)
--------------------------

### Improved
- No need to replace tokens again (#75)
- Allow simple token conditions in email fields

### Fixed
- Cronjob did not work when installed via composer (#77)


Version 1.3.0 (2015-09-03)
--------------------------

### New
- Added a sendNotificationMessage hook (#72)
- Allow recipient token to contain a list of recipients. (#75)

### Fixed
- Do not add a new line if file storage mode append but existing file was empty


Version 1.3.0-rc1 (2015-07-07)
------------------------------

### New
- The Notification Center now provides a queue gateway that buffers messages and sends them based on cron job settings (#63)
- The "store to file" gateway now supports appending to an already existing file (#65)
- InsertTags as well as Simple Tokens are now allowed in sender e-mail name and sender e-mail address fields as well (#40 and #58)
- Added support for file uploads in form generator fields that can now use the upload attachment token (#39)


Versions previous to 1.3.0-rc1
------------------------------

Changelog was not maintained in previous versions.
Try to use the git history for details.
