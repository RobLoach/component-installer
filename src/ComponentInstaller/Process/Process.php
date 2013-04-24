<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach (http://robloach.net)
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller\Process;

use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Package\Dumper\ArrayDumper;

/**
 * The base Process type.
 *
 * Processes are initialized, and then run during installation.
 */
class Process implements ProcessInterface
{
    protected $composer;
    protected $io;
    protected $config;
    protected $packages = array();
    protected $componentDir = 'components';

    /**
     * {@inheritdoc}
     */
    public function __construct(Composer $composer = null, IOInterface $io = null)
    {
        $this->composer = isset($composer) ? $composer : new Composer();
        $this->io = isset($io) ? $io : new NullIO();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // Retrieve the configuration variables.
        $this->config = $this->composer->getConfig();
        if (isset($this->config)) {
            if ($this->config->has('component-dir')) {
                $this->componentDir = $this->config->get('component-dir');
            }
        }

        // Get the available packages.
        $locker = $this->composer->getLocker();
        if (isset($locker)) {
            $lockData = $locker->getLockData();
            $this->packages = isset($lockData['packages']) ? $lockData['packages'] : array();
        }

        // Add the root package to the packages list.
        $root = $this->composer->getPackage();
        if ($root) {
            $dumper = new ArrayDumper();
            $package = $dumper->dump($root);
            $package['is-root'] = true;
            $this->packages[] = $package;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        return false;
    }

    /**
     * Retrieves the component name for the component.
     *
     * @param string $prettyName
     *   The Composer package name.
     * @param array $extra
     *   The extra config options sent from Composer.
     *
     * @return string
     *   The name of the component, without its vendor name.
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
