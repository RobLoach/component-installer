<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Test\Installer;

use Composer\Test\Installer\LibraryInstallerTest;
use ComponentInstaller\Installer;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Composer;
use Composer\Config;

/**
 * Tests Component Installer.
 */
class InstallerTest extends LibraryInstallerTest
{

    /**
     * testSupports
     *
     * @param $type
     *   The type of library.
     * @param $expected
     *   Whether or not the given type is supported by Component Installer.
     *
     * @return void
     *
     * @dataProvider providerComponentSupports
     */
    public function testComponentSupports($type, $expected)
    {
        $installer = new Installer($this->io, $this->composer, 'component');
        $this->assertSame($expected, $installer->supports($type), sprintf('Failed to show support for %s', $type));
    }

    /**
     * providerSupports
     *
     * @see testComponentSupports()
     */
    public function providerComponentSupports()
    {
        $tests[] = array('component', true);
        $tests[] = array('not-a-component', false);
        $tests[] = array('library', false);

        return $tests;
    }

    /**
     * testGetInstallPath
     *
     * @param $name
     *   The name of the package.
     * @param $expected
     *   The expected install path.
     * @param $componentname
     *   The component-name provided by the package.
     * @param $componentdir
     *   The component-dir provided by the root config.
     *
     * @dataProvider providerComponentGetInstallPath
     *
     * @see ComponentInstaller\\Installer::getInstallPath()
     */
    public function testComponentGetInstallPath($name, $expected, $extra = array(), $componentdir = '')
    {
        $installer = new Installer($this->io, $this->composer, 'component');
        $package = new Package($name, '1.0.0', '1.0.0');
        if (!empty($extra)) {
            $package->setExtra($extra);
        }
        if (!empty($componentdir)) {
            $config['config']['component-dir'] = $componentdir;
            $this->composer->getConfig()->merge($config);
        }
        $result = $installer->getInstallPath($package);
        $this->assertEquals($expected, $result, sprintf('Failed to get proper install path for %s', $name));
    }

    /**
     * providerGetInstallPath
     *
     * @see testComponentGetInstallPath()
     */
    public function providerComponentGetInstallPath()
    {
        $tests[] = array('foo/bar1', 'components/bar1');
        $tests[] = array('foo/bar2', 'components/foobar', array(
            'component' => array(
                'name' => 'foobar'
            )
        ));
        $tests[] = array('foo/bar3', 'public/bar3', array(), 'public');
        $tests[] = array('foo/bar4', 'public/foobar', array(
            'component' => array(
                'name' => 'foobar'
            )
        ), 'public');

        return $tests;
    }

    /**
     * testGetConfigOption
     *
     * @dataProvider providerGetConfigOption
     */
    public function testGetConfigOption(array $config, $option, $default = null, $expected = null)
    {
        $configObject = new Config();
        $configObject->merge(array('config' => $config));
        $result = Installer::getConfigOption($configObject, $option, $default);
        $this->assertEquals($result, $expected, sprintf('Fail to get proper config options for %s', $option));
    }

    /**
     * providerGetConfigOption
     *
     * @see testGetConfigOption()
     */
    public function providerGetConfigOption()
    {
        // Get no option.
        $tests[] = array(array('not-wanted' => 3), 'get-no-option');
        // Retrieve the default value.
        $tests[] = array(array(), 'get-default-option', 'default', 'default');
        // Retrieve the correct value.
        $tests[] = array(array('wanted' => 123), 'wanted', 500, 123);

        return $tests;
    }

    /**
     * testRequireJs
     *
     * @dataProvider providerRequireJs
     */
    public function testRequireJs(array $json = array(), $expected = '')
    {
        $result = Installer::requireJs($json);
        $this->assertEquals($result, $expected, sprintf('Fail to get proper expected require.js'));
    }

    public function providerRequireJs()
    {
        // Start with a base RequireJS configuration.
        $js = <<<EOT
var components = %s;
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}
EOT;

        // Tests a basic configuration.
        $tests[] = array(
            array('foo' => 'bar'),
            sprintf($js, "{\n    \"foo\": \"bar\"\n}"),
        );

        return $tests;
    }

    /**
     * testRequireJson
     *
     * @dataProvider providerRequireJson
     */
    public function testRequireJson(array $packages, array $config, $expected = null)
    {
        $configObject = new Config();
        $configObject->merge(array('config' => $config));
        $result = Installer::requireJson($packages, $configObject);
        $this->assertEquals($result, $expected, sprintf('Fail to get proper expected require.js configuration'));
    }

    public function providerRequireJson()
    {
        // Test a package that doesn't have any extra information.
        $packageWithoutExtra = array(
            'name' => 'components/packagewithoutextra',
            'type' => 'component',
        );
        $packages = array($packageWithoutExtra);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'packagewithoutextra',
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        // Test switching the component directory.
        $packages = array($packageWithoutExtra);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'packagewithoutextra',
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array('component-dir' => 'otherdir'), $expected);

        // Test switching the baseUrl.
        $packages = array($packageWithoutExtra);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'packagewithoutextra',
                ),
            ),
            'baseUrl' => '/another/path',
        );
        $tests[] = array($packages, array('component-baseurl' => '/another/path'), $expected);

        // Test a package with just Scripts.
        $jquery = array(
            'name' => 'components/jquery',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'scripts' => array(
                        'jquery.js'
                    )
                )
            )
        );
        $packages = array($jquery);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'jquery',
                    'main' => 'jquery.js',
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        // Test a pckage with just shim information.
        $underscore = array(
            'name' => 'components/underscore',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'shim' => array(
                        'exports' => '_',
                    ),
                ),
            ),
        );
        $packages = array($underscore);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'underscore',
                ),
            ),
            'shim' => array(
                'underscore' => array(
                    'exports' => '_',
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        // Test a package the has everything.
        $backbone = array(
            'name' => 'components/backbone',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'scripts' => array(
                        'backbone.js'
                    ),
                    'shim' => array(
                        'exports' => 'Backbone',
                        'deps' => array(
                            'underscore',
                            'jquery'
                        ),
                    ),
                ),
            ),
        );
        $packages = array($backbone);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'backbone',
                    'main' => 'backbone.js',
                ),
            ),
            'shim' => array(
                'backbone' => array(
                    'exports' => 'Backbone',
                    'deps' => array(
                        'underscore',
                        'jquery'
                    ),
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        // Test multiple packages.
        $packages = array($backbone, $jquery);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'backbone',
                    'main' => 'backbone.js',
                ),
                array(
                    'name' => 'jquery',
                    'main' => 'jquery.js',
                ),
            ),
            'shim' => array(
                'backbone' => array(
                    'exports' => 'Backbone',
                    'deps' => array(
                        'underscore',
                        'jquery'
                    ),
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        // Package with a config definition.
        $packageWithConfig = array(
            'name' => 'components/packagewithconfig',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'config' => array(
                        'color' => 'blue',
                    ),
                ),
            ),
        );
        $packages = array($packageWithConfig);
        $expected = array(
            'packages' => array(
                array(
                    'name' => 'packagewithconfig',
                ),
            ),
            'config' => array(
                'packagewithconfig' => array(
                    'color' => 'blue',
                ),
            ),
            'baseUrl' => 'components',
        );
        $tests[] = array($packages, array(), $expected);

        return $tests;
    }

    /**
     * testGetComponentName
     *
     * @dataProvider providerGetComponentName
     */
    public function testGetComponentName($prettyName, array $extra, $expected)
    {
        $result = Installer::getComponentName($prettyName, array('component' => $extra));
        $this->assertEquals($result, $expected, sprintf('Fail to get proper component name for %s', $prettyName));
    }

    /**
     * Data provider for testGetComponentName.
     *
     * @see testGetComponentName()
     */
    public function providerGetComponentName()
    {
        return array(
            array('components/jquery', array(), 'jquery'),
            array('components/jquery', array('name' => 'myownjquery'), 'myownjquery'),
            array('jquery', array(), 'jquery'),
        );
    }

    /**
     * Tests setting up the root package scripts.
     */
    public function testSetUpScripts()
    {
        $installer = new Installer($this->io, $this->composer, 'component');
        $package = new RootPackage('foo/bar', '1.0.0', '1.0.0');
        $installer->setUpScripts($package);
        $scripts = $package->getScripts();
        $result = isset($scripts['post-install-cmd']['component-installer']) ? $scripts['post-install-cmd']['component-installer'] : FALSE;
        $this->assertEquals($result, 'ComponentInstaller\\Installer::postInstall', 'The postInstall script handler is registered on install.');
        $result = isset($scripts['post-update-cmd']['component-installer']) ? $scripts['post-update-cmd']['component-installer'] : FALSE;
        $this->assertEquals($result, 'ComponentInstaller\\Installer::postInstall', 'The postInstall script handler is registered on update.');
    }

    /**
     * testPackageStyles
     *
     * @dataProvider providerPackageStyles
     */
    public function testPackageStyles(array $packages, array $config, $expected = null)
    {
        $configObject = new Config();
        $configObject->merge(array('config' => $config));
        $result = Installer::packageStyles($packages, $configObject);
        $this->assertEquals($result, $expected, sprintf('Fail to get proper expected require.css content'));
    }

    public function providerPackageStyles()
    {
        // Test collecting one style.
        $package = array(
            'name' => 'components/package',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'styles' => array(
                        __DIR__ . '/Resources/test.css',
                    ),
                ),
            ),
        );
        $packages = array($package);
        $expected = array(
            'package' => array(
                __DIR__ . '/Resources/test.css' => __DIR__ . '/Resources/test.css',
            )
        );
        $tests[] = array($packages, array(), $expected);

        // Test collecting a style that doesn't exist.
        $package2 = array(
            'name' => 'components/package',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'styles' => array(
                        __DIR__ . '/Resources/test-not-found.css',
                    ),
                ),
            ),
        );
        $packages = array($package, $package2);
        $expected = array(
            'package' => array(
                __DIR__ . '/Resources/test.css' => __DIR__ . '/Resources/test.css',
            )
        );
        $tests[] = array($packages, array(), $expected);

        // Test collecting a style that doesn't exist.
        $package3 = array(
            'name' => 'components/package',
            'type' => 'component',
            'extra' => array(
                'component' => array(
                    'styles' => array(
                        __DIR__ . '/Resources/test2.css',
                    ),
                ),
            ),
        );
        $packages = array($package, $package3);
        $expected = array(
            'package' => array(
                __DIR__ . '/Resources/test.css' => __DIR__ . '/Resources/test.css',
                __DIR__ . '/Resources/test2.css' => __DIR__ . '/Resources/test2.css',
            )
        );
        $tests[] = array($packages, array(), $expected);

        return $tests;
    }
}
