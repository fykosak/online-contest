# flag-icon-css

[![npm version](https://badge.fury.io/js/flag-icon-css.svg)](https://badge.fury.io/js/flag-icon-css)

A collection of all country flags in SVG — plus the CSS for easier integration.
See the [demo](http://lipis.github.io/flag-icon-css/).

## Usage

For using the flags inline with text add the classes `.flag-icon` and
`.flag-icon-xx` (where `xx` is the
[ISO 3166-1-alpha-2 code](https://www.iso.org/obp/ui/#search/code/)
of a country) to an empty `<span>`. If you want to have a squared version flag
then add the class `flag-icon-squared` as well. Example:

```html
<span class="flag-icon flag-icon-gr"></span>
<span class="flag-icon flag-icon-gr flag-icon-squared"></span>
```

You could also apply this to any element, but in that case you'll have to use the
`flag-icon-background` instead of `flag-icon` and you're set. This will add the
correct background with the following CSS properties:

```css
background-size: contain;
background-position: 50%;
background-repeat: no-repeat;
```

Which means that the flag is just going to appear in the middle of an element, so
you will have to set manually the correct size of 4 by 3 ratio or if it's squared
add also the `flag-icon-squared` class.

## Credits

This project wouldn't exist without the awesome and now deleted collection of
SVG flags by [koppi](https://github.com/koppi).
