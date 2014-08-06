# Solution10\Config

This is a tiny, lightning fast way of defining configuration for your app.

This is deliberately bare-bones and not swamped with options / parsers. The focus is
on speed and lightness, whilst still retaining vital features.

**WIP: Not quite ready for primetime yet**

[![Build Status](https://travis-ci.org/Solution10/calendar.svg?branch=master)](https://travis-ci.org/Solution10/calendar)
[![Coverage Status](https://coveralls.io/repos/Solution10/calendar/badge.png)](https://coveralls.io/r/Solution10/calendar)

## Features

- No dependancies
- PHP 5.3+
- Lightning fast
- Extremely light
- Inheritance; define a base config and override per-environment

## Getting Started

Installation is via composer, in the usual manner:

```json
{
    "require" {
        "solution10/config": "1.*"
    }
}
```

## Further Reading

### Userguide

[Check out the Wiki](https://github.com/Solution10/config/wiki)

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
