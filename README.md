Component Installer for Composer [![Build Status](https://secure.travis-ci.org/RobLoach/component-installer.png?branch=master)](http://travis-ci.org/RobLoach/component-installer)
================================

Allows installation of Components via [Composer](http://getcomposer.org).

Usage
-----

To install a Component with Composer, add the Component to your *composer.json*
`require` key. The following will install [jQuery](http://jquery.com), with
Component Installer, into *components/jquery*:

``` json
{
    "require": {
        "components/jquery": "1.9.*"
    }
}
```

### Using the Component

Component Installer will build a [RequireJS](http://requirejs.org) configuration
for you, which allows autoloading the scripts only when required:

``` html
<!DOCTYPE html>
<html>
    <head>
        <title>jQuery+RequireJS Component Installer Sample</title>
        <script src="components/require.js"></script>
    </head>
    <body>
        <h1>jQuery+RequireJS Component Installer Sample Page</h1>
        <script>
          require(['jquery'], function($) {
            $('body').css('background-color', 'green');
          });
        </script>
    </body>
</html>
```

It is still possible to load using normal script tags. In this example, jQuery
would be at *components/jquery/jquery.js*.

### Creating a Component

To set up a Component to be installed with Component Installer, have it
`require` the package *robloach/component-installer* and set the `type` to
*component*:

``` json
{
    "name": "components/jquery",
    "type": "component",
    "require": {
        "robloach/component-installer": "*"
    },
    "extra": {
        "component": {
            "scripts": [
                "jquery.js"
            ]
        }
    }
}
```

### Installation Directory

It is posssible to switch where Components are installed by changing the
`component-dir` option in your root *composer.json*'s `config`. The following
will install jQuery to *public/jquery* rather than *components/jquery*:

``` json
{
    "require": {
        "components/jquery": "*"
    },
    "config": {
        "component-dir": "public"
    }
}
```

Defaults to `components`.

### Base URL

While `component-dir` depicts where the components will be installed,
`component-baseurl` tells RequireJS the base path that will use when attempting
to load the scripts in the web browser. It is important to make sure the
`component-baseurl` points to the `component-dir` when loaded externally. You
can read more about [`baseUrl`](http://requirejs.org/docs/api.html#config-baseUrl)
in the RequireJS documentation.

``` json
{
    "require": {
        "components/jquery": "*"
    },
    "config": {
        "component-dir": "public/assets",
        "component-baseurl": "/assets"
    }
}
```

Defaults to `components`.

### Component Name

Components can provide their own component name. The following will install
jQuery to *components/myownjquery* rather than *components/jquery*:

``` json
{
    "name": "components/jquery",
    "type": "component",
    "extra": {
        "component": {
            "name": "myownjquery"
        }
    }
}
```

### RequireJS Configuration

Components can alter how [RequireJS](http://requirejs.org) registers and
interacts with them. Currently, only the
[`shim`](http://www.requirejs.org/docs/api.html#config-shim) configuration is
available, but additional options will become available soon:

``` json
{
    "name": "components/backbone",
    "type": "component",
    "require": {
        "components/underscore": "*"
    },
    "extra": {
        "component": {
            "shim": {
                "deps": ["underscore", "jquery"],
                "exports": "Backbone"
            }
        }
    }
}
```

Not Invented Here
-----------------

There are many other amazing projects from which Component Installer was
inspired. Many are much more mature than this one. It is encouraged to take a
look at some of the [other great package management systems](http://github.com/wilmoore/frontend-packagers):
* [npm](http://npmjs.org)
* [bower](http://twitter.github.com/bower/)
* [component](http://github.com/component/component)
* [Jam](http://jamjs.org)
* [volo](http://volojs.org)
* [Ender](http://ender.jit.su)
* etc

Todo
----

* More [RequireJS Configurations](http://www.requirejs.org/docs/api.html#config)
* Put together a list of Components that make use of Component Installer
* Compile all the components into one file (`require.min.js`?)
* Determine if `scripts` is named correctly, or if it should just use `main`
* Aggregate all `styles` together into one *require.css*
* Determine if `component-baseurl` is the correct name for it
* Install to `components/[vendor]-[package]` rather than `components/[package]`?

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
