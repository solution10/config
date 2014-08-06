# Solution10\Config

This is a tiny, lightning fast way of defining configuration for your app.

This is deliberately bare-bones and not swamped with options / parsers. The focus is
on speed and lightness, whilst still retaining vital features.

[![Build Status](https://travis-ci.org/Solution10/config.svg?branch=master)](https://travis-ci.org/Solution10/config)
[![Coverage Status](https://coveralls.io/repos/Solution10/config/badge.png)](https://coveralls.io/r/Solution10/config)

[![Latest Stable Version](https://poser.pugx.org/solution10/config/v/stable.svg)](https://packagist.org/packages/solution10/config)
[![Total Downloads](https://poser.pugx.org/solution10/config/downloads.svg)](https://packagist.org/packages/solution10/config)
[![License](https://poser.pugx.org/solution10/config/license.svg)](https://packagist.org/packages/solution10/config)

## Features

- No dependancies
- PHP 5.3+
- Lightning fast
- Extremely light
- Inheritance; define a base config and override per-environment

## Installation

Installation is via composer, in the usual manner:

```json
{
    "require" {
        "solution10/config": "1.*"
    }
}
```

## Documentation

### Userguide

[Check out the Wiki](https://github.com/Solution10/config/wiki)

(or the /docs folder in the repo)

### API Docs

From a checkout of this project, run:

    $ make

This will create an api/ folder for you to peruse.

## PHP Requirements

- PHP >= 5.3

## Author

Alex Gisby: [GitHub](http://github.com/alexgisby), [Twitter](http://twitter.com/alexgisby)

## License

[MIT](http://github.com/solution10/config/tree/master/LICENSE.md)

## Contributing

[Contributors Notes](http://github.com/solution10/config/tree/master/CONTRIBUTING.md)
