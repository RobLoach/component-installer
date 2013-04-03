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

use Composer\Test\Installer\LibraryInstallerTest;
use ComponentInstaller\Installer;
use Composer\Package\Package;
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
}
