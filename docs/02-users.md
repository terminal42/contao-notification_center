# Users

The Notification Center has been around for over 10 years. That's why many users are
familiar with its configuration.

If you are a user, you know how to configure everything, and you want to contribute to this
Open Source project, please consider editing this page and help newcomers!


## Form publish conditions

You can have certain messages only be sent if they fulfil a given condition. E.g. you could to something like
this: `##form_department## === 'department_a'`. In this case, the message will only be sent, if the form
selection field `department` contained the value `department_a`. You can also be more creative and use the
array support in Simple Tokens: `##form_department## in ['department_a', 'department_d','department_x']` etc.