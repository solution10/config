# Solution10\Config

A super simple configuration loader / reader that focusses on being light and fast
without losing required functionality.

## Contents

- [Installation](#installation)
- [Config Format](#config-format)
- [Config Locations](#config-locations)
- [Reading Config](#reading-config)
    - [Overwriting](#overwriting)
    - [Default Values](#default-values)
- [Requiring Config Files](#requiring-config-files)
- [Exceptions](#exceptions)

## Installation

Installation is via composer, either in composer.json:

```json
{
    "require": {
        "solution10/config": "~2.0"
    }
}
```

Or even better, through the command line

```sh
$ composer require solution10/config
```

## Config Format

Configuration files are in PHP format and simply return an array:

```php
<?php

return array(
    'posts_per_page' => 20,
    'cache' => array(
        'latest_posts' => 30,
        'popular_posts' => 60,
    ),
    'api_key' => 'fhsdfkh7883475345',
);
```

Being PHP files, you can use anything as config values and even have dynamically
generated config values:

```php
<?php

return array(
    'mysql' => array(
        'host' => $_SERVER['MYSQL_HOST'],
        'username' => getenv('db_username'),
        'password' => getenv('db_password'),
        'database' => (date('l') == 'Monday')? 'monday_database' : 'other_database',
    ),
);
```

## Config Locations

You can put your config files anywhere, but the folder structure is important. The top-level
of your config location is "production", this is where you put your live config values. You
can also specify other environments by creating a folder with that name.

So, if my config location is app/config, the directory would look like:

    app/
        config/
            app.php
            database.php
            sandbox/
                database.php
            staging/
                database.php

## Reading Config

So we now have our config files setup, how do we read them? Easy:

```php
$config = new Solution10\Config\Config('/var/www/app/config');

echo $config->get('database.mysql.host');
// This translates to: app/config/database.php['mysql']['host']
```

How about if config is for another environment?

```php
// Pass in the second parameter to say which environment:
$config = new Solution10\Config\Config('/var/www/app/config', 'sandbox');

echo $config->get('database.mysql.host');
// translates to: app/config/sandbox/database.php['mysql']['host']
```

You can get single keys, or you can return whole sections of the config:

```php
// app/config/app.php
return array(
    'paginate' => array(
        'perPage' => 25,
        'queryParam' => 'page'
    ),
);

// then within your app:
echo $config->get('app.paginate.perPage'); // output: 25
$paginate = $config->get('app.paginate'); // returns: array('perPage' => 25, 'queryParam' => 'page')
```

### Overwriting

Except dear friends, I'm afraid I lied. It's not quite as simple as that.
When you load an environment other than "production", the "production" config
still gets loaded; it's values are simply overwritten by the environment config.

So, if our app/config/database.php contains:

```php
return array(
    'mysql' => array(
        'host' => '10.1.1.27',
        'user' => 'root',
        'pass' => 'M0nk3y',
        'database' => 'nsa'
    ),
);
```

But we load the 'sandbox' environment which has in app/config/sandbox/database.php:

```php
return array(
    'mysql' => array(
        'host' => 'localhost',
        'database' => 'nsa_local',
    ),
);
```

These two config files are merged together, so that what Config reports is:

```php
return array(
    'mysql' => array(
        'host' => 'localhost',      // from sandbox
        'user' => 'root',           // from production
        'pass' => 'M0nk3y',         // from production
        'database' => 'nsa_local'   // from sandbox
    ),
);
```

If you've ever used ini file "groups" before, or Laravel's config system this should
feel familiar.

### Default Values

If you know a value might not be present in config but still want to try, you can
provide a default value as the second parameter to get().

```php
$perPage = $config->get('app.paginate.perPage', 25);
```

## Requiring Config Files

Let's say you have a routes.php file which defines all of the routes for your application
in a Silex-y way:

```php
$app->get('/', function () {
    return 'Hello World!';
});
```

This is clearly config, but needs to be 'required' in the global scope rather than local
to the Config class.

You may also want to load different routes based on environment, for example, a debugging
route on all environments other than production.

Starting with Version 2, Solution10\Config supports this.

```php
$config = new Solution10\Config\Config('/var/www/app/config', 'sandbox');
$files = $config->requiredFiles('routes');

foreach ($files as $file) {
    require_once $file;
}
```

In the above example, $files is an array of full-paths to the .php files that Config loads
when normally resolving via `get()`. So `$files` would contain:

```php
$files = [
    '/var/www/app/config/routes.php',
    '/var/www/app/config/sandbox/routes.php'
];
```

Note that as with `get()`, the production config is loaded as well as the environment specific.

## Exceptions

Config throws a single Exception (`Solution10\Config\Exception`) when you give a path
to the constructor that either doesn't exist, or cannot be read.
