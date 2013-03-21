Component Installer for Composer [![Build Status](https://secure.travis-ci.org/RobLoach/component-installer.png?branch=master)](http://travis-ci.org/RobLoach/component-installer)
================================

Allows installation of Components via [Composer](http://getcomposer.org).

Usage
-----

To install a Component with Composer, add the Component to your `composer.json`
`require` key. The following will install [jQuery](http://jquery.com), with
Component Installer, into `components/jquery`:

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

Although this is completely optional, you can still load the global script if
desired. In this example, jQuery would be at `components/jquery/jquery.js`.

### Creating a Component

To set up a Component to be installed with Component Installer, have it
`require` the package `robloach/component-installer` and set the `type` to
`component`:

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

By default, Components will be installed to the `components` directory, but this
can be overriden by using `component-dir` in `config` of the root package. The
following will install jQuery to `public/jquery` rather than
`components/jquery`:

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

### Component Name

Components can override their own component name. The following will install
jQuery to `components/myownjquery` rather than `components/jquery`:

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

Components can specify how [RequireJS](http://requirejs.org) registers and
interacts with them:

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
    },
    "config": {
        "component-baseurl": "/another/path"
    }
}
```

* The [`shim`](http://www.requirejs.org/docs/api.html#config-shim) configuration
option allows dependencies and exports for scripts that don't explicitly provide
RequireJS support.
* The `component-baseurl` config variable allows alteration of the
[baseUrl](http://requirejs.org/docs/api.html#config-baseUrl) configuration for
the scripts. This will change the base URL used when loading the scripts.

Todo
----

* Put together a list of Components that make use of Component Installer
* Compile the RequireJS configuration (`require.min.js` possibly?)
* Determine if "scripts" is handled correctly, or if it should just use "main"

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
