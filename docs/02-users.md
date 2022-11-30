# Users

The Notification Center has been around for over 10 years. That's why many users are
familiar with its configuration and it's only roughly documented here.

If you are a user, you know how to configure everything, and you want to contribute to this
Open Source project, please consider editing this page and help newcomers!


## Form publish conditions

You can have certain messages only be sent if they fulfil a given condition. E.g. you could to something like
this: `##form_department## === 'department_a'`. In this case, the message will only be sent, if the form
selection field `department` contained the value `department_a`. You can also be more creative and use the
array support in Simple Tokens: `##form_department## in ['department_a', 'department_d','department_x']` etc.


## Front end modules

The Notification Center provides notifications for some Contao core functionality. Unfortunately, some of those
Core front end modules are pretty old and cannot be easily extended with custom notification functionality.
Hence, there are special Notification Center front end modules that build on top of the core ones. They only adjust
notification settings but are otherwise an exact copy of what the Core of Contao provides.

This affects the following modules:

### Lost password

Instead of using the Core "Lost password" front end module, simply select the "Lost password (Notification Center)"
one and you will be able to customize the notifications.

### Registration

Instead of using the Core "Registration" front end module, simply select the "Registration (Notification Center)"
one and you will be able to customize the notifications.

You will have the option to select the regular notification. If selected, this one is always sent when a new member
registers in the front end. You can use it to send a confirmation to the member saying something like "Your account
is being checked by the administrator" and also send a copy (or a different message) to the admin.

If you want to automate this process, so new members can self-activate their account using double opt-in, activate
the checkbox "Enable member self-activation (double opt-in)". In this case, your regular notification will get a
Simple Token `##link##` which contains the activation link a new member has to click to confirm their e-mail address.
In this case, you can also choose a second notification "Activation notification". This one is sent, once a user has clicked
on the `##link##` and confirmed their account.