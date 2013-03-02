Component Installer for Composer
================================

Allows installation of Components via [Composer](http://getcomposer.org).

Usage
-----

To depend on a component, add the component to your `require` key. The following
is an example of installing [jQuery](http://jquery.com) with Component Installer
to `component/components-jquery`:

``` json
{
    "require": {
        "components/jquery": "*"
    }
}
```

Components can be installed with Component Installer by requiring
`robloach/component-installer` and stating the `type` as a `component`:

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
`public/component-jquery` rather than `component/component-jquery`:

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

License
-------

Component Installer is licensed under the MIT License - see the LICENSE file
for details.
