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
        "components/jquery": "1.9.*",
        "components/normalize.css": "2.*"
    }
}
```

### Using the Component

Component Installer will build a [RequireJS](http://requirejs.org) configuration
for you, which allows autoloading the scripts only when. A *require.css* file is
also compiled from all included Component stylesheets:

``` html
<!DOCTYPE html>
<html>
    <head>
        <title>jQuery+RequireJS Component Installer Sample</title>
        <link href="components/require.css" rel="stylesheet" type="text/css">
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

It is still possible to use the scripts directly. In this example, jQuery would
be at *components/jquery/jquery.js*, and Normalize would be available at
*components/normalize.css/normalize.css*.

### Creating a Component

To set up a Component to be installed with Component Installer, have it
`require` the package *robloach/component-installer* and set the `type` to
*component*:

``` json
{
    "name": "components/bootstrap",
    "type": "component",
    "require": {
        "robloach/component-installer": "*"
    },
    "extra": {
        "component": {
            "scripts": [
                "js/bootstrap.js"
            ],
            "styles": [
                "css/bootstrap.css"
            ],
            "files": [
                "img/glyphicons-halflings.png",
                "img/glyphicons-halflings-white.png"
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
            },
            "config": {
                "color": "blue"
            }
        }
    }
}
```

Current available RequireJS options include:
* [`shim`](http://www.requirejs.org/docs/api.html#config-shim)
* [`config`](http://www.requirejs.org/docs/api.html#config-moduleconfig)

### Packages Without Composer Support

Using [`repositories`](http://getcomposer.org/doc/05-repositories.md#repositories)
in *composer.json* allows the use of Component Installer in packages that don't
explicitly provide their own *composer.json* information. In the following
example, we define use of [html5shiv](https://github.com/aFarkas/html5shiv):

``` json
{
    "require": {
        "afarkas/html5shiv": ">=3.6.2"
    },
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "afarkas/html5shiv",
                "type": "component",
                "version": "3.6.2",
                "dist": {
                    "url": "https://github.com/aFarkas/html5shiv/archive/3.6.2.zip",
                    "type": "zip"
                },
                "source": {
                    "url": "https://github.com/aFarkas/html5shiv.git",
                    "type": "git",
                    "reference": "3.6.2"
                },
                "component": {
                    "scripts": [
                        "dist/html5shiv.js"
                    ]
                },
                "require": {
                    "robloach/component-installer": "*"
                }
            }
        }
    ],
    "minimum-stability": "dev"
}
```

Todo
----

* More [RequireJS Configurations](http://www.requirejs.org/docs/api.html#config)
* Put together a list of Components that make use of Component Installer
* Compile all the components into one file (`require.min.js`?)
* Concatenate all `scripts` into one script file and use that file for `main`
* Determine if `component-baseurl` is the correct name for it

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

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
