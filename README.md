Notification Center
===================

The purpose of this extension is to provide a central and flexible way for
Contao developers to send notifications via their extensions.

### Which version should I choose?

| Contao Version | Haste Version | PHP Version    | Notification Center Version |
|----------------|---------------|----------------|-----------------------------|
| <= 4.13        | 4.*           | ^7.0 ǀǀ ^8.0   | 1.6.*                       |
| 4.13.*         | 5.*           | > 8.1          | 1.7.*                       |
| 4.13.* ǀǀ 5.*  | 5.*           | > 8.1          | 2.*                         |

### Documentation

[Find the documentation here.](https://extensions.terminal42.ch/docs/notification-center/)

### Community Extensions

Currently, there are no known community extensions compatible with Notification Center 2.0. Feel free to create a
Pull Request to add yours here!

### Translating the Notification Center

Translations for the Notification Center 2.0 are no longer managed on Transifex. Please, contribute translation fixes
via GitHub Pull Requests.

### Notification Center Pro 🔔❤️

This extension is - and will remain forever - provided for free. We believe it makes it significantly easier for 
extension developers to make notifications of their extensions configurable by their users. Hence, we want to make it
easy for you to require the Notification Center as a dependency.

This led to the fact that the Notification Center is one of the most popular Contao extensions for many years which 
of course is great, but it also entails a huge maintenance burden for us. Hence, we decided to provide additional 
functionality with a separate, paid extension: **Notification Center Pro**!

With Notification Center pro you can level up your Notification game and benefit from a lot of additional features 
while also supporting the further development and maintenance of the Notification Center itself. Win-win!

Here is a short list of features you can expect from Notification Center Pro:

* Log all the notifications sent via Notification Center!
  * Logs are kept for a configurable amount of days (`7` by default)
  * Allows to re-send notifications right from the logs and even allows to adjust certain information e.g.
  * Simple Tokens, so you can test things easily
  * Provides a diff viewer to see differences between log entries being sent based on another one
* Provides custom simple tokens! You can conveniently create your own, custom Simple Tokens based on other tokens. This will allow you to be a lot more flexible by extracting partial information from other tokens, combining them or virtually doing whatever you can do with Twig with them.
* Provides a "void" gateway: This gateway does not send any message at all. Instead, it just fakes delivery allowing for easier testing.

💰 [Go get Notification Center Pro today!](https://extensions.terminal42.ch/p/nc-pro)