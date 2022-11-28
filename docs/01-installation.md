# Installation

There are - as with most Contao extensions - two ways to install this extension.

## Installation via Contao Manager

Search for `terminal42/notification_center` in the Contao Manager and add it
to your installation. Apply changes to update the packages.

## Manual installation

Add a Composer dependency for this bundle. Therefore, change in the project root
and run the following:

```bash
composer require terminal42/notification_center
```

## Update the database

Then, either let the Contao Manager run the migrations on the database or if you fancy the
command line, run the `contao:migrate` command.