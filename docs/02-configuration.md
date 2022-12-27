# Configuration

As with every Symfony Bundle or Contao extension, you may configure the settings of the Notification
Center using your `config/config.yaml`.

## Bulky items

The Notification Center ships with built-in handling for big files. These files are kept in a specific storage and
might include for example user uploads. By default, these files are kept for `7` days, but you may configure a different
retention period.

If you e.g. wanted to extend the default configuration from 7 to 14 days, the configuration would look like this:

```yaml
terminal42_notification_center:
    bulky_items_storage:
        retention_period: 14
```

## Cron jobs

The Notification Center uses cron jobs to make sure your system is kept in a clean state. It will work without you
configuring anything. However, it's always recommended to configure [Contao's Cronjob Framework with a
real cron job](https://docs.contao.org/manual/en/performance/cronjobs/).