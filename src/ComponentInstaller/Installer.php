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
use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Script\Event;
use Composer\Json\JsonFile;
use Assetic\Asset\AssetCollection;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Asset\FileAsset;

/**
 * The Component Installer for Composer.
 */
class Installer extends LibraryInstaller
{
    /**
     * Retrieves the Component Directory from a Composer object.
     */
    public static function getConfigOption(Config $config, $option, $default = null)
    {
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
        $componentDir = static::getConfigOption($this->composer->getConfig(), 'component-dir', 'components');

        // Register the post-install/update scripts.
        $this->setUpScripts($this->composer->getPackage());

        return $componentDir . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Retrieves the component name for the component.
     */
    public static function getComponentName($prettyName, $extra)
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

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return (bool) ($packageType === 'component');
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
        $config = $composer->getConfig();
        $packages = $lockData['packages'];
        $io->write('<info>Building require.js</info>');

        // Figure out where all Components should be installed.
        $componentDir = static::getConfigOption($config, 'component-dir', 'components');
        $baseUrl = static::getConfigOption($config, 'component-baseurl', 'components');

        // Construct the require.js and stick it in the destination.
        $json = static::requireJson($packages, $config);
        $requireConfig = static::requireJs($json);
        if (file_put_contents($componentDir . '/require.config.js', $requireConfig) === FALSE) {
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
        if (file_put_contents($componentDir . '/require.js', $requirejs . $requireConfig) === FALSE) {
            $io->write('<error>Error writing require.js to the components directory</error>');

            return false;
        }

        // Build the require.css file.
        $io->write('<info>Building require.css</info>');
        $filters = new \Assetic\Filter\FilterCollection(array(
            new CssRewriteFilter(),
        ));
        $assets = new AssetCollection();
        $styles = static::packageStyles($packages, $config);
        foreach ($styles as $style) {
            $assetPath = realpath($style);
            $sourceRoot = dirname($style);
            $sourcePath = $style;
            $targetPath = $baseUrl;
            $asset = new FileAsset($assetPath, $filters, $sourceRoot, $sourcePath);
            $asset->setTargetPath($targetPath);
            $assets->add($asset);
        }

        $css = $assets->dump();
        if (file_put_contents($componentDir . '/require.css', $css) === FALSE) {
            $io->write('<error>Error writing require.css to destination</error>');

            return false;
        }
    }

    /**
     * Sets up the post-install and post-update scripts for a package.
     */
    public function setUpScripts($rootPackage)
    {
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
     *
     * @param $packages
     *   An array of packages from the composer.lock file.
     * @param $config
     *   The Composer Config object.
     */
    public static function requireJson(array $packages, Config $config)
    {
        $json = array();
        $componentDir = static::getConfigOption($config, 'component-dir', 'components');

        // Construct the packages configuration.
        foreach ($packages as $package) {
            if (isset($package['type']) && $package['type'] == 'component') {
                // Retrieve information from the extra options.
                $extra = isset($package['extra']) ? $package['extra'] : array();
                $options = isset($extra['component']) ? $extra['component'] : array();

                // Construct the base details.
                $name = static::getComponentName($package['name'], $extra);
                $component['name'] = $name;

                // Build the "main" directive.
                $scripts = isset($options['scripts']) ? $options['scripts'] : array();
                if (isset($scripts[0])) {
                    $component['main'] = $scripts[0];
                }

                // Add the package to the scripts.
                $json['packages'][] = $component;

                // Add the shim definition.
                $shim = isset($options['shim']) ? $options['shim'] : array();
                if (!empty($shim)) {
                    $json['shim'][$name] = $shim;
                }

                // Add the config definition.
                $packageConfig = isset($options['config']) ? $options['config'] : array();
                if (!empty($packageConfig)) {
                    $json['config'][$name] = $packageConfig;
                }
            }
        }

        // Provide the baseUrl if it's available.
        if ($baseUrl = static::getConfigOption($config, 'component-baseurl', 'components')) {
            $json['baseUrl'] = $baseUrl;
        }

        return $json;
    }

    /**
     * Constructs the require.js file from the provided require.js JSON array.
     *
     * @param $json
     *   The require.js JSON configuration.
     */
    public static function requireJs(array $json = array())
    {
        // Encode the array to a JSON array.
        $js = JsonFile::encode($json);

        // Construct the JavaScript output.
        $output = <<<EOT
var components = $js;
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}
EOT;

        return $output;
    }

    /**
     * Retrieves an array of styles from a collection of packages.
     *
     * @param $packages
     *   An array of packages from the composer.lock file.
     * @param $config
     *   The Composer Config object.
     */
    public static function packageStyles(array $packages, Config $config)
    {
        $styles = array();
        $componentDir = static::getConfigOption($config, 'component-dir', 'components');

        // Construct the packages configuration.
        foreach ($packages as $package) {
            if (isset($package['type']) && $package['type'] == 'component') {
                // Retrieve information from the extra options.
                $extra = isset($package['extra']) ? $package['extra'] : array();
                $options = isset($extra['component']) ? $extra['component'] : array();

                // Construct the base details.
                $name = static::getComponentName($package['name'], $extra);

                // Build the "main" directive.
                $packageStyles = isset($options['styles']) ? $options['styles'] : array();
                foreach ($packageStyles as $style) {
                    $candidates = array(
                        $componentDir . '/' . $name . '/' . $style,
                        $style,
                    );
                    foreach ($candidates as $candidate) {
                        if (file_exists($candidate)) {
                            $styles[] = $candidate;
                            break;
                        }
                    }
                }
            }
        }

        return $styles;
    }
}
