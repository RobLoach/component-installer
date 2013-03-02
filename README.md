Component Installer for Composer
================================

Allows installation of Components via [Composer](http://getcomposer.org).

Usage
-----

To install a Component with Composer, add the component to your `composer.json`
`require` key. The following will install [jQuery](http://jquery.com), with
Component Installer, into `component/components-jquery`:

``` json
{
    "require": {
        "components/jquery": "*"
    }
}
```

To set up a Component to be installed with Component Installer, have it
`require` the package `robloach/component-installer` and stating the `type` as
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

Component Directory
-------------------

The root package can state where components should be installed by leveraging
`component-dir` in `config`. The following will install jQuery to
`public/components-jquery` rather than `component/components-jquery`:

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

Component Name
--------------

Components can override their own installation name. The following will install
jQuery to `component/myownjquery` rather than `component/components-jquery`:

``` json
{
    "name": "components/jquery",
    "type": "component",
    "extra": {
        "component-name": "myownjquery"
    }
}
```

Todo
----

* Write tests
* Set up a [RequireJS](http://requirejs.org) config for installed components

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
