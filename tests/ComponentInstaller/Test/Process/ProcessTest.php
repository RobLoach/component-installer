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

/**
 * Tests Process.
 */
class ProcessTest extends \PHPUnit_Framework_TestCase
{

    /**
     * testGetComponentName
     *
     * @dataProvider providerGetComponentName
     */
    public function testGetComponentName($prettyName, array $extra, $expected)
    {
        $process = new Process(new Composer());
        $result = $process->getComponentName($prettyName, array('component' => $extra));
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
}
