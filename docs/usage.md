# Solution10\Config

A super simple configuration loader / reader that focuses on being light and fast
without losing required functionality.

## Contents

- [Installation](#installation)
- [ConfigInterface](#configinterface)
- [FilesystemConfig](#filesystemconfig)
    - [Config Format](#config-format)
    - [Config Locations](#config-locations)
    - [Multiple Config Locations](#multiple-config-locations)
    - [Reading Config](#reading-config)
        - [Overwriting](#overwriting)
        - [Default Values](#default-values)
        - [Overwriting from multiple files](#overwriting-from-multiple-files)
    - [Requiring Config Files](#requiring-config-files)
- [ArrayConfig](#arrayconfig)
- [Exceptions](#exceptions)

## Installation

Installation is via composer:

```sh
$ composer require solution10/config
```

## ConfigInterface

There are two Config systems contained within `Solution10\Config` - one which deals with reading configuration
from files on the filesystem, and one which allows you to simply pass in arrays of config to be read back out.

The `Solution10\Config\ConfigInterface` defines an interface between the two which should allow you to use them
fairly interchangeably, for instance, in unit tests you may prefer to use `ArrayConfig` since it doesn't require
any special files or paths.

## FilesystemConfig

This class is the 'classic' way of doing config using `Solution10\Config` using .php files within
directories to specify config. If in pre-3.0 versions you used `Solution10\Config\Config` then use
this class.

### Config Format

Configuration files are in PHP format and simply return an array:

```php
<?php

return [
    'posts_per_page' => 20,
    'cache' => [
        'latest_posts' => 30,
        'popular_posts' => 60,
    ],
    'api_key' => 'fhsdfkh7883475345',
];
```

Being PHP files, you can use anything as config values and even have dynamically
generated config values:

```php
<?php

return [
    'mysql' => [
        'host' => $_SERVER['MYSQL_HOST'],
        'username' => getenv('db_username'),
        'password' => getenv('db_password'),
        'database' => (date('l') == 'Monday')? 'monday_database' : 'other_database',
    ],
];
```

### Config Locations

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

### Multiple Config Locations

You can also configure several base directories where config should be read from. Each directory should follow the
same structure as above; a top level with environment named folders beneath them. For example:

```
modules/
    topics/
        config/
            topics.php
            development/
                topics.php
    users/
        config/
            users.php
            development/
                users.php
```

To set up the two config roots (topics/config and users/config) you can either pass them into the constructor
or add them later with `addConfigPath()`:

```php
$c = new Solution10\Config\FilesystemConfig([
    __DIR__.'/modules/topics/config',
    __DIR__.'/modules/users/config'
]);

// OR

$c = new Solution10\Config\FilesystemConfig([__DIR__.'/modules/topics/config']);
$c->addConfigPath(__DIR__.'/modules/users/config');
```

### Reading Config

So we now have our config files setup, how do we read them? Easy:

```php
$config = new Solution10\Config\FilesystemConfig(['/var/www/app/config']);

echo $config->get('database.mysql.host');
// This translates to: app/config/database.php['mysql']['host']
```

How about if config is for another environment?

```php
$config = new Solution10\Config\FilesystemConfig(['/var/www/app/config']);
$config->setEnvironment('sandbox');

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

#### Overwriting

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

These two config files are merged together, so that what FilesystemConfig reports is:

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

#### Default Values

If you know a value might not be present in config but still want to try, you can
provide a default value as the second parameter to get().

```php
$perPage = $config->get('app.paginate.perPage', 25);
```

#### Overwriting from multiple files

So how does overwriting work when we have multiple files in multiple locations and environments? Let's look
at a fairly complex, but representative example. Imagine our config files look like this:

```php
modules/
    topics/
        config/
            app.php
            development/
                app.php
    users/
        config/
            app.php
            development/
                app.php
```

If I ask for `$config->get('app.version');` just which of those four potential app.php files is going to get called?!

The answer is simple; the same rules apply as a single config location (production overwritten by environment specific),
but it'll apply all the production files from all locations first, before applying all the environment files.

As to the order of those files - last defined = first read. It's a stack, rather than a queue.

Too many words right? Here's a proper example, using the above file structure:

```php
$config = new FilesystemConfig([
    __DIR__.'/modules/topics/config',
    __DIR__.'/modules/users/config'
]);
```

Since we're in production (no env specified) and we defined the `users/` locations **after** the `topics/` one, the
order of overwrite is:

```
1. modules/topics/config/app.php
2. modules/users/config/app.php
```

So values in users/config/app.php overwrite those of topics/config/app.php.

If environments are involved like this:

```php
$config = new FilesystemConfig([
    __DIR__.'/modules/topics/config',
    __DIR__.'/modules/users/config'
], 'development');
```

Then the files are visited and merged in this order:

```
Start with the 'production' layer of config, in the order the paths were added:
1. modules/topics/config/app.php
2. modules/users/config/app.php

And then the 'environment' layer of config, again in the order that the paths were added:
3. modules/topics/config/development/app.php
4. modules/users/config/development/app.php
```

Hopefully the sheer number of words needed to describe this behaviour warns you off doing anything as complex
or confusing as this! If you need to have every single config location and environment overriding the same file
then I'd suggest you have something wrong with the structure of your app.

### Requiring FilesystemConfig Files

Let's say you have a routes.php file which defines all of the routes for your application
in a Silex-y way:

```php
$app->get('/', function () {
    return 'Hello World!';
});
```

This is clearly config, but needs to be 'required' in the global scope rather than local
to the FilesystemConfig class.

You may also want to load different routes based on environment, for example, a debugging
route on all environments other than production.

Starting with Version 2, Solution10\FilesystemConfig supports this.

```php
$config = new Solution10\Config\FilesystemConfig(['/var/www/app/config']);
$config->setEnvironment('sandbox');
$files = $config->getRequiredFiles('routes');

foreach ($files as $file) {
    require_once $file;
}
```

In the above example, $files is an array of full-paths to the .php files that FilesystemConfig loads
when normally resolving via `get()`. So `$files` would contain:

```php
$files = [
    '/var/www/app/config/routes.php',
    '/var/www/app/config/sandbox/routes.php'
];
```

Note that as with `get()`, the production config is loaded as well as the environment specific.

This also works with multiple base config locations, you'll get the files in the order you would expect the config
system would attempt to load them.

## Exceptions

FilesystemConfig throws a single Exception (`Solution10\FilesystemConfig\Exception`) when you give a path
to the constructor that either doesn't exist, or cannot be read.
