<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Composer\Test\Process;

use ComponentInstaller\Process\Process;
use Composer\Composer;
use ComponentInstaller\Process\RequireJsProcess;
use Composer\Config;
use Composer\IO\NullIO;

/**
 * Tests Process.
 */
class RequireJsProcessTest extends \PHPUnit_Framework_TestCase
{
    protected $process;
    protected $composer;
    protected $io;

    public function setUp()
    {
        $this->composer = new Composer();
        $this->io = new NullIO();
        $this->process = new RequireJsProcess($this->composer, $this->io);
    }

    /**
     * testRequireJs
     *
     * @dataProvider providerRequireJs
     */
    public function testRequireJs(array $json = array(), $expected = '')
    {
        $result = $this->process->requireJs($json);
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
        $this->composer->setConfig($configObject);
        $this->process->init();
        $result = $this->process->requireJson($packages);
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
            'baseUrl' => 'components',
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

}
