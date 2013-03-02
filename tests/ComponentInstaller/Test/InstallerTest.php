<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Test\Installer;

use Composer\Test\Installer\LibraryInstallerTest;
use ComponentInstaller\Installer;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Util\Filesystem;
use Composer\Test\TestCase;
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
    function providerComponentSupports()
    {
        return array(
            array('component', true),
            array('not-a-component', false),
            array('library', false),
        );
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
    public function testComponentGetInstallPath($name, $expected, $componentname = '', $componentdir = '')
    {
        $installer = new Installer($this->io, $this->composer, 'component');
        $package = new Package($name, '1.0.0', '1.0.0');
        if (!empty($componentname)) {
            $package->setExtra(array(
                'component-name' => $componentname,
            ));
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
    public function providerComponentGetInstallPath() {
        return array(
            array('foo/bar1', 'components/foo-bar1'),
            array('foo/bar2', 'components/bar2', 'bar2'),
            array('foo/bar3', 'public/foo-bar3', '', 'public'),
            array('foo/bar4', 'public/foobar', 'foobar', 'public'),
        );
    }

}
