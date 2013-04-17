<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller\Test\Process;

use ComponentInstaller\Process\Process;
use Composer\Composer;
use ComponentInstaller\Process\RequireCssProcess;
use Composer\Config;

/**
 * Tests Process.
 */
class RequireCssProcessTest extends ProcessTest
{
    protected $process;

    public function setUp()
    {
        parent::setUp();
        $this->process = new RequireCssProcess($this->composer, $this->io);
    }
    /**
     * testPackageStyles
     *
     * @dataProvider providerPackageStyles
     */
    public function testPackageStyles(array $packages, array $config, $expected = null)
    {
        $this->composer->getConfig()->merge(array('config' => $config));
        $this->process->init();
        $result = $this->process->packageStyles($packages);
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
                        __DIR__ . '/../Resources/test.css',
                    ),
                ),
            ),
        );
        $packages = array($package);
        $expected = array(
            'package' => array(
                __DIR__ . '/../Resources/test.css' => __DIR__ . '/../Resources/test.css',
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
                        __DIR__ . '/../Resources/test-not-found.css',
                    ),
                ),
            ),
        );
        $packages = array($package, $package2);
        $expected = array(
            'package' => array(
                __DIR__ . '/../Resources/test.css' => __DIR__ . '/../Resources/test.css',
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
                        __DIR__ . '/../Resources/test2.css',
                    ),
                ),
            ),
        );
        $packages = array($package, $package3);
        $expected = array(
            'package' => array(
                __DIR__ . '/../Resources/test.css' => __DIR__ . '/../Resources/test.css',
                __DIR__ . '/../Resources/test2.css' => __DIR__ . '/../Resources/test2.css',
            )
        );
        $tests[] = array($packages, array(), $expected);

        return $tests;
    }
}
