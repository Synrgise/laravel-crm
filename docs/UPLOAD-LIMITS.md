# Fixing "413 Request Entity Too Large"

File uploads (e.g. SLA and Contract documents) are limited to **10 MB** in the app. If you see **413 Request Entity Too Large**, the web server or PHP is rejecting the request before it reaches Laravel. Adjust the limit for your stack:

## PHP (php.ini)

Set in your `php.ini` (or equivalent):

```ini
upload_max_filesize = 20M
post_max_size = 20M
```

`post_max_size` must be at least as large as `upload_max_filesize`. After changing, restart PHP-FPM or Apache if needed.

## Nginx

In your `server` block (or in `http`):

```nginx
client_max_body_size 20M;
```

Then reload Nginx: `nginx -s reload` (or restart the service).

## Apache (mod_php)

The repo’s `public/.htaccess` already sets `upload_max_filesize` and `post_max_size` when `mod_php` is used. If you use **PHP-FPM** with Apache, the `.htaccess` PHP directives are ignored; use `php.ini` or `public/.user.ini` instead.

## PHP-FPM / shared hosting

A `public/.user.ini` is included with:

```ini
upload_max_filesize = 20M
post_max_size = 20M
```

Some hosts allow `.user.ini`; others require changing limits in their panel or `php.ini`.

## Verify

- PHP: run `php -i | grep -E 'upload_max_filesize|post_max_size'`.
- Nginx: check the active config for `client_max_body_size`.
