Olark webhook to Freshdesk
==========================

Help you create a new freshdesk ticket for each olark transcript (can config to offline message only)

## Setup

1. Clone repo, run `composer install` (Composer can be installed from https://getcomposer.org/doc/00-intro.md)
2. Copy file `config/config.sample.php` to `config/config.php`
3. Update config params like Freshdesk API Key (Get from freshdesk profile, right sidebar), Freshdesk subdomain and message format
4. Create a Olark webhook to `http://url-to-root/web/index.php`