<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ComponentInstaller;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

/**
 * The Component Installer for Composer.
 */
class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        // Construct the installation directory for the Component.
        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            // Default the vendor name to "component" if it's not provided.
            $vendor = 'component';
            $name = $prettyName;
        }
        $name = $vendor . '-' . $name;

        // Allow the installation directory to be overriden by the root package.
        $extra = $package->getExtra();
        if (isset($extra['component-name'])) {
            $name = $extra['component-name'];
        }

        // Figure out where all Components should be installed.
        $config = $this->composer->getConfig();
        $dest = $config->has('component-dir') ? $config->get('component-dir') : 'component';

        return $dest . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return (bool)($packageType === 'component');
    }
}
