# Solution10\Config

This is a tiny, lightning fast way of defining configuration for your app.

This is deliberately bare-bones and not swamped with options / parsers. The focus is
on speed and lightness, whilst still retaining vital features.

[![Build Status](https://travis-ci.org/Solution10/config.svg?branch=master)](https://travis-ci.org/Solution10/config)
[![Coverage Status](https://coveralls.io/repos/Solution10/config/badge.png)](https://coveralls.io/r/Solution10/config)

[![Latest Stable Version](https://poser.pugx.org/solution10/config/v/stable.svg)](https://packagist.org/packages/solution10/config)
[![Total Downloads](https://poser.pugx.org/solution10/config/downloads.svg)](https://packagist.org/packages/solution10/config)
[![License](https://poser.pugx.org/solution10/config/license.svg)](https://packagist.org/packages/solution10/config)

- [Features](#features)
- [Installation](#installation)
- [Documentation](#documentation)
    - [Userguide](#userguide)
    - [API Docs](#api-docs)
- [PHP Requirements](#php-requirements)
- [Changelog](#changelog)
- [Author](#author)
- [License](#license)

## Features

- No dependencies
- PHP 5.4+
- Lightning fast
- Extremely light
- Inheritance; define a base config and override per-environment

## Installation

Installation is via composer, in the usual manner:

```sh
$ composer require solution10/config
```

## Documentation

### Userguide

[Check out the Wiki](https://github.com/Solution10/config/wiki)

(or the /docs folder in the repo)

### API Docs

From a checkout of this project, run:

    $ make apidocs

This will create an api/ folder for you to peruse.

## PHP Requirements

- PHP >= 5.4

(If you require 5.3 support, versions up to 1.2.0 supported 5.3, so pin to that version)

## Changelog

### 2.0.0

- Added `requiredFiles()` API call
- Removed PHP 5.3 support
- Added PHP 7 into build matrix
- Removed HHVM from allowed fails

### 1.2.0

- **Last release supporting PHP 5.3**
- Passing 'null' to construct now assumes production

### 1.1.0

- Migrating to PSR4

### 1.0.0

- Initial version

## Author

Alex Gisby: [GitHub](http://github.com/alexgisby), [Twitter](http://twitter.com/alexgisby)

## License

[MIT](http://github.com/solution10/config/tree/master/LICENSE.md)
