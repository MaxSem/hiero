# Hiero is an Egyptian Hieroglyphics to SVG renderer in pure PHP

# Installation
    composer install maxsem/hiero

# Fonts
To render something, you need a font, which for this package is a set of SVG files along with some metadata.
You can make one out of a TTF font:

    bin/build-font.php path/to/font.ttf destination/dir

You'll need Python and FontForge module for it to export SVGs from fonts.

# Usage



# Error handling

All errors specific to the package are `instanceof HieroException`. For problems related to user input, both parsing and rendering stages can be configured to either throw exceptions or return a list of `Error` objects.
If configuerd to throw exceptions, descendants of `LocalizableException` will be thrown.
All these errors contain an error code and an optional list of parameters that can be fed into an external localization framework.
See the `ErrorCodes` class for possible codes.


# Development

* If it isn't tested, it doesn't work.
* Prefer immutable data classes with readonly properties to getters an setters.  
* Run all the tests with `make`.
* Minimum supported PHP version is [the one used by Wikimedia wikis](https://en.wikipedia.org/wiki/Special:Version).
