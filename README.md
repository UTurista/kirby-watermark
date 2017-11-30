# Kirby Watermark Plugin

This plugin automatically adds a watermark to uploaded images.

## Installation

Download or clone this repository, put the folder into your `site/plugins` folder and rename it to `watermark`. Alternatively you can also use the [Kirby CLI](https://github.com/getkirby/cli).

## Configuration
### Watermark
The minimum configuration required is to set in your `site/config.php` the name of the image that will be used as watermark, like this:
```
c::set('watermark.image', 'watermark.png');
```
And to place the image in the `content` folder, ie, the $site images.

### Collumns and rows
The plugin allows to define the number of times the watermark will be repeated in the X axis (columns) and in the Y axis (rows).
Both will default to `1`. You can change this,  by adding in  `site/config.php`a confiuragtion like this:
```
c::set('watermark.collumns', 2);
c::set('watermark.rows', 2);
```

### Exclude Pages
You're also able to disable this plugin to certain pages. This configuration is an array of [glob patterns](https://en.wikipedia.org/wiki/Glob_(programming)), configured like this:
```
c::set('watermark.ignore', array('originals/**'));
```

