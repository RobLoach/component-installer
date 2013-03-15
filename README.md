Component Installer for Composer [![Build Status](https://secure.travis-ci.org/RobLoach/component-installer.png?branch=master)](http://travis-ci.org/RobLoach/component-installer)
================================

Allows installation of Components via [Composer](http://getcomposer.org).

Usage
-----

To install a Component with Composer, add the Component to your `composer.json`
`require` key. The following will install [jQuery](http://jquery.com), with
Component Installer, into `components/components-jquery`:

``` json
{
    "require": {
        "components/jquery": "1.9.*"
    }
}
```

To set up a Component to be installed with Component Installer, have it
`require` the package `robloach/component-installer` and set the `type` to
`component`:

``` json
{
    "name": "components/jquery",
    "type": "component",
    "require": {
        "robloach/component-installer": "*"
    }
}
```

### Installation Directory

By default, Components will be installed to the `components` directory, but this
can be overriden by using `component-dir` in `config` of the root package. The
following will install jQuery to `public/components-jquery` rather than
`components/components-jquery`:

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
jQuery to `components/components-myownjquery` rather than `components/components-jquery`:

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

Todo
----

* Put together a list of Components that make use of Component Installer
* Read some of the information from `component.json`
* Set up a [RequireJS](http://requirejs.org) config for installed components

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
