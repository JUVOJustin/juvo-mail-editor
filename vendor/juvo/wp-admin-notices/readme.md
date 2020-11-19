# WP Admin Notices

This is an extension for the [wptrt/admin-notices](https://github.com/WPTRT/admin-notices) package to store notices in a transient. The goal is to allow a dynamic storing process throughout WordPress.
Since this package is only a proxy that stores the notices in the database, you can find examples and options in the [wptrt/admin-notices](https://github.com/WPTRT/admin-notices) repository.

## Usage

```php
// Display stored notices
add_action( 'admin_init', function() {
   $notices = new \juvo\WordPressAdminNotices\Manager();
   $notices->notices();
} );

// Add a notice.
\juvo\WordPressAdminNotices\Manager::add((string) $id, (string) $title, (string) $content, (array) $options);

// Remove a notice.
\juvo\WordPressAdminNotices\Manager::remove((string) $id, (bool) $onlyGlobal);

//Example: Check if Advanced Custom Fields Pro is installed
if ( ! class_exists( 'acf_pro' ) ) {
   // Add a notice.
   Manager::add( "missing_plugin", "Required plugin missing","The advanced custom fields plugin is required for this plugin to work" ), [ "type" => "error" ] );
} else {
   Manager::remove( "missing_plugin");
}
```

[wptrt/admin-notices](https://github.com/WPTRT/admin-notices) parameters are fully supported. If a notice with global scope is dismissed, it will be automatically removed from the transient. Additionally a `max_age` parameter can be passed to the `Manager` constructor.
All notices older than this value will be removed. Notices with a user scope are not removed from the transient by dismissing them. They will only be removed if they exceed the `max_age`. The transient itself does not expire at all.


### Composer

From the command line:

```sh
composer require juvo/wp-admin-notices
```
