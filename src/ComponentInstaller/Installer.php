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

        // Allow the component to define its own name.
        $extra = $package->getExtra();
        $component = isset($extra['component']) ? $extra['component'] : array();
        if (isset($component['name'])) {
            $name = $component['name'];
        }
        $name = $vendor . '-' . $name;

        // Figure out where all Components should be installed.
        $config = $this->composer->getConfig();
        $dest = $config->has('component-dir') ? $config->get('component-dir') : 'components';

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
