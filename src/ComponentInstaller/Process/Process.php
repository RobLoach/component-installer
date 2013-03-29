<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller\Process;

use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\IO\NullIO;

class Process implements ProcessInterface
{
    protected $config;
    protected $componentDir = 'components';
    protected $composer;
    protected $packages = array();
    protected $io;

    public function __construct(Composer $composer = null, IOInterface $io = null)
    {
        $this->composer = isset($composer) ? $composer : new Composer();
        $this->io = isset($io) ? $io : new NullIO();
        $this->config = $composer->getConfig();
        if (isset($this->config) && $this->config->has('component-dir')) {
            // Default the component directory to 'components'.
            $this->componentDir = $this->config->get('component-dir');
        }

        // Get the available packages.
        $locker = $this->composer->getLocker();
        if (isset($locker)) {
            $lockData = $locker->getLockData();
            $this->packages = isset($lockData['packages']) ? $lockData['packages'] : array();
        }
    }

    public function process()
    {
        $this->io->write('  <error>The given process is an in complete implementation.</error>');

        return false;
    }

    /**
     * Retrieves the component name for the component.
     */
    public function getComponentName($prettyName, array $extra = array())
    {
        // Parse the pretty name for the vendor and name.
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            // Vendor wasn't found, so default to the pretty name instead.
            $name = $prettyName;
        }

        // Allow the component to define its own name.
        $component = isset($extra['component']) ? $extra['component'] : array();
        if (isset($component['name'])) {
            $name = $component['name'];
        }

        return $name;
    }
}
