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

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Script\Event;
use Composer\Json\JsonFile;

/**
 * The Component Installer for Composer.
 */
class Installer extends LibraryInstaller
{
    /**
     * Retrieves the Component Directory from a Composer object.
     */
    public static function getConfigOption(Composer $composer, $option, $default = null)
    {
        $config = $composer->getConfig();
        return $config->has($option) ? $config->get($option) : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        // Construct the installation directory for the Component.
        $prettyName = $package->getPrettyName();
        $extra = $package->getExtra();
        $name = static::getComponentName($prettyName, $extra);

        // Get the components directory.
        $componentDir = static::getConfigOption($this->composer, 'component-dir', 'components');

        // Register the post-install/update scripts.
        $this->setUpScripts($this->composer->getPackage());

        return $componentDir . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Retrieves the component name for the component.
     */
    public static function getComponentName($prettyName, $extra) {
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

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return (bool)($packageType === 'component');
    }

    /**
     * Post install script.
     *
     * @param $event
     *   The event executed by the script.
     */
    public static function postInstall(Event $event)
    {
        // Parse through the Event and extract useful information.
        $io = $event->getIO();
        $composer = $event->getComposer();
        $locker = $composer->getLocker();
        $lockData = $locker->getLockData();
        $packages = $lockData['packages'];
        $io->write('<info>Setting up require.js configuration</info>');

        // Figure out where all Components should be installed.
        $destination = static::getConfigOption($composer, 'component-dir', 'components');

        // Construct the require.js and stick it in the destination.
        $config = static::requireJs($packages, $composer);
        if (file_put_contents($destination . '/require.config.js', $config) === FALSE) {
            $io->write('<error>Error writing require.config.js</error>');
            return false;
        }

        // Read in require.js to prepare the final require.js.
        $requirejs = file_get_contents(__DIR__ . '/Resources/require.js');
        if ($requirejs === FALSE) {
            $io->write('<error>Error reading in require.js</error>');
            return false;
        }

        // Append the config to the require.js and write it.
        if (file_put_contents($destination . '/require.js', $requirejs . $config) === FALSE) {
            $io->write('<error>Error writing require.js to destination</error>');
            return false;
        }
    }

    /**
     * Sets up the post-install and post-update scripts for a package.
     */
    protected function setUpScripts($rootPackage) {
        // Only act on the root package if it exists.
        if ($rootPackage) {
            $scripts = $rootPackage->getScripts();
            $scripts['post-install-cmd']['component-installer'] = 'ComponentInstaller\\Installer::postInstall';
            $scripts['post-update-cmd']['component-installer'] = 'ComponentInstaller\\Installer::postInstall';
            $rootPackage->setScripts($scripts);
        }
    }

    /**
     * Creates a require.js configuration from an array of packages.
     */
    public static function requireJs($packages, $composer)
    {
        $json = array();
        $componentDir = static::getConfigOption($composer, 'component-dir', 'components');

        // Construct the packages configuration.
        foreach ($packages as $package) {
            if ($package['type'] == 'component') {
                // Retrieve information from the extra options.
                $extra = isset($package['extra']) ? $package['extra'] : array();
                $options = isset($extra['component']) ? $extra['component'] : array();

                // Construct the base details.
                $name = static::getComponentName($package['name'], $extra);
                $component['name'] = $name;
                $component['location'] = $componentDir . DIRECTORY_SEPARATOR . $name;

                // Build the "main" directive.
                $scripts = isset($options['scripts']) ? $options['scripts'] : array();
                if (isset($scripts[0])) {
                    $component['main'] = $scripts[0];
                }

                // @todo Create the shim definitions.

                // Add the package to the scripts.
                $json['packages'][] = $component;
            }
        }

        // Provide the baseUrl if it's available.
        if ($baseUrl = static::getConfigOption($composer, 'component-baseurl')) {
            $json['baseUrl'] = $baseUrl;
        }

        // Create the components RequireJS definition.
        $json = JsonFile::encode($json);

        // Construct the JavaScript output.
        $output = <<<EOT
var components = $json;
if (typeof require !== "undefined" && require.config) {
    require.config(components);
}
else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}
EOT;
        return $output;
    }
}
