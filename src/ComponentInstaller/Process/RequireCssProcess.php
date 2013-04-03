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

use Composer\Composer;
use Composer\Config;
use Composer\Package\Package;
use Assetic\Asset\AssetCollection;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterCollection;

class RequireCssProcess extends Process
{
    public function getMessage()
    {
        return '  <comment>Building require.css</comment>';
    }

    public function process($message = '')
    {
        $filters = new FilterCollection(array(
            new CssRewriteFilter(),
        ));
        $assets = new AssetCollection();
        $styles = $this->packageStyles($this->packages);
        foreach ($styles as $package => $packageStyles) {
            foreach ($packageStyles as $style => $path) {
                // The full path to the CSS file.
                $assetPath = realpath($path);
                // The root of the CSS file.
                $sourceRoot = dirname($path);
                // The style path to the CSS file when external.
                $sourcePath = $package . '/' . $style;
                // Where the final CSS will be generated.
                $targetPath = $this->componentDir;
                // Build the asset and add it to the collection.
                $asset = new FileAsset($assetPath, $filters, $sourceRoot, $sourcePath);
                $asset->setTargetPath($targetPath);
                $assets->add($asset);
            }
        }

        $css = $assets->dump();
        if (file_put_contents($this->componentDir . '/require.css', $css) === FALSE) {
            $this->io->write('<error>Error writing require.css to destination</error>');

            return false;
        }
    }

    /**
     * Retrieves an array of styles from a collection of packages.
     *
     * @param $packages
     *   An array of packages from the composer.lock file.
     * @param $config
     *   The Composer Config object.
     *
     * @return array
     *   A set of package styles.
     */
    public function packageStyles(array $packages)
    {
        $styles = array();

        // Construct the packages configuration.
        foreach ($packages as $package) {
            // Retrieve information from the extra options.
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $options = isset($extra['component']) ? $extra['component'] : array();

            // Construct the base details.
            $name = $this->getComponentName($package['name'], $extra);

            // Build the "main" directive.
            $packageStyles = isset($options['styles']) ? $options['styles'] : array();
            foreach ($packageStyles as $style) {
                $candidates = array(
                    $this->componentDir . '/' . $name . '/' . $style,
                    $style,
                );
                foreach ($candidates as $candidate) {
                    if (file_exists($candidate)) {
                        // Provide the package name, style and full path.
                        $styles[$name][$style] = $candidate;
                        break;
                    }
                }
            }
        }

        return $styles;
    }
}
