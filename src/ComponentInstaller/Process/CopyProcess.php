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

use Composer\Composer;
use Composer\Package\Package;
use Composer\Package\Loader\ArrayLoader;
use Composer\Util\Filesystem;

/**
 * Process which copies components from their source to the components folder.
 */
class CopyProcess extends Process
{
    /**
     * The Composer installation manager to find Component vendor directories.
     */
    protected $installationManager;

    /**
     * Initialize the process.
     */
    public function init()
    {
        $output = parent::init();
        $this->installationManager = $this->composer->getInstallationManager();

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return '  <comment>Copying assets to component directory</comment>';
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        return $this->copy($this->packages);
    }

    /**
     * Copy file assets from the given packages to the component directory.
     *
     * @param array $packages
     *   An array of packages.
     */
    public function copy($packages)
    {
        $fs = new Filesystem();
        foreach ($packages as $package) {
            // Retrieve some information about the package.
            $packageDir = $this->getVendorDir($package);
            $name = isset($package['name']) ? $package['name'] : '__component__';
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $componentName = $this->getComponentName($name, $extra);

            // Cycle through each asset type.
            $fileType = array('scripts', 'styles', 'files');
            foreach ($fileType as $type) {
                // Only act on the files if they're available.
                if (isset($extra['component'][$type]) && is_array($extra['component'][$type])) {
                    foreach ($extra['component'][$type] as $file) {
                        // Make sure the file itself is available.
                        $source = $packageDir.DIRECTORY_SEPARATOR.$file;
                        foreach (glob($source, GLOB_BRACE) as $filesource) {
                            // Act on only files.
                            if (!is_dir($filesource)) {
                                // Find the final destination without the package directory.
                                $withoutPackageDir = str_replace($packageDir.DIRECTORY_SEPARATOR, '', $filesource);

                                // Construct the final file destination.
                                $destination = $this->componentDir.DIRECTORY_SEPARATOR.$componentName.DIRECTORY_SEPARATOR.$withoutPackageDir;

                                // Ensure the directory is available.
                                $fs->ensureDirectoryExists(dirname($destination));

                                // Copy the file to its destination.
                                copy($filesource, $destination);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Retrieves the given package's vendor directory, where it's installed.
     */
    public function getVendorDir(array $package)
    {
        // The root package vendor directory is not handled by getInstallPath().
        if (isset($package['is-root']) && $package['is-root'] === true) {
            // @todo Handle cases where the working directory is not where the
            // root package is installed.
            return getcwd();
        }
        $loader = new ArrayLoader();
        $completePackage = $loader->load($package);

        return $this->installationManager->getInstallPath($completePackage);
    }
}
