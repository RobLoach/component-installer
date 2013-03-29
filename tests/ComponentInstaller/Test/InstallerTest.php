<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Composer\Test;

use Composer\Installer\LibraryInstaller;
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
    protected $componentDir = 'components';

    protected function setUp()
    {
        parent::setUp();

        $this->componentDir = realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR.'composer-test-component';
        $this->ensureDirectoryExistsAndClear($this->componentDir);

        $this->config->merge(array(
            'config' => array(
                'component-dir' => $this->componentDir,
            ),
        ));
    }

    protected function tearDown()
    {
        $this->fs->removeDirectory($this->componentDir);

        return parent::tearDown();
    }

    public function testInstallerCreationShouldNotCreateComponentDirectory()
    {
        $this->fs->removeDirectory($this->componentDir);
        new Installer($this->io, $this->composer);
        $this->assertFileNotExists($this->componentDir);
    }

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
        // All package types support having Components.
        $tests[] = array('component', true);
        $tests[] = array('not-a-component', false);
        $tests[] = array('library', false);

        return $tests;
    }


    /**
     * testRequireJs
     *
     * @dataProvider providerRequireJs
     *
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
     *
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
     * testPackageStyles
     *
     * @dataProvider providerPackageStyles
     *
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
        */
}
