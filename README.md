Notification Center
===================

The purpose of this extension is to provide a central and flexible way for
Contao developers to send notifications via their extensions.

If we can get this extension to be widely used, users will quickly get used
to the way one can configure the notification center.

## Translating
The notification center can be translated via Transifex: https://www.transifex.com/projects/p/notification_center

## Adding your own notification type

```php
// config.php
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope'] = array
(
    // Type
    'iso_order_status_change'   => array
     (
        // Field in tl_nc_language
        'recipients'    => array
        (
            // Valid tokens
            'recipient_email' // The email address of the recipient
        ),
        'attachment_tokens'    => array
        (
            'form_*', // All the order condition form fields
            'document' // The document that should be attached (e.g. an invoice)
        )
    )
);
```


## Sending a notification

Extension developers most likely want to send a single notification identified by ID,:

```php
$objNotification = NotificationCenter\Model\Notification::findByPk($intNotificationId);
if (null !== $objNotification) {
    $objNotification->send($arrTokens, $strLanguage); // Language is optional
}
```

If you want to send all notifications of a certain type, you can send it like this:

```php
$strType = 'iso_order_status_change';
$objNotificationCollection = NotificationCenter\Model\Notification::findByType($strType);
if (null !== $objNotificationCollection) {
    while ($objNotificationCollection->next()) {
        $objNotification = $objNotificationCollection->current();
        $objNotification->send($arrTokens, $strLanguage); // Language is optional
    }
}
```

## Hooks

If you want to enrich each message being sent by some meta data or want to disable some messages being sent, you can
use the sendNotificationMessage hook:

```php

// config.php
$GLOBALS['TL_HOOKS']['sendNotificationMessage'][] = array('MyHook', 'execute');

// The hook
class MyHook
{
    public function execute($objMessage, $arrTokens, $language, $objGatewayModel)
    {
         if (!$objMessage->regardUserSettings || !FE_USER_LOGGED_IN 
            || $objMessage->getRelated('pid')->type !== 'custom_notification') {
            return true;
         }
         
         $user = \MemberModel::findByPK($arrTokens['recipient']);     
         if (!$user || !$user->disableEmailNotifications) {
            return true;
         }
                      
         return false;
    }
}
```

You can modify the tokens of a message via the `mapNotificationTokens` hook. It is executed before the `sendNotificationMessage` hook and receives the same parameters. The hook expects its listeners to return an array of modified tokens:

```php
// Example: Escape all commas in tokens of a CSV export by wrapping the token in double quotes. Also escape existing double quotes inside the token.

// config.php
$GLOBALS['TL_HOOKS']['mapNotificationTokens'][] = array('MyHook', 'execute');

// The hook
class MyHook
{
    public function execute($objMessage, $arrTokens, $language, $objGatewayModel)
    {
        if ($objGatewayModel->type === 'file' && $objGatewayModel->file_type === 'csv') {
            array_walk($arrTokens, function(&$varValue) {
                if (strpos($varValue, ',') !== false)
                    $varValue = '"' . str_replace('"', '""', $varValue) . '"';
            });
        }

        return $arrTokens;
    }
}
```
