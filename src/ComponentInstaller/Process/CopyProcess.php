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
use Composer\Package\Package;
use Composer\Package\Loader\ArrayLoader;
use Composer\Util\Filesystem;

class CopyProcess extends Process
{
    protected $installationManager;

    public function init()
    {
        $output = parent::init();
        $this->installationManager = $this->composer->getInstallationManager();

        return $output;
    }

    public function getMessage()
    {
        return '  <comment>Copying assets to component directory</comment>';
    }

    public function process($message = '')
    {
        // Mirror each package's assets into the component directory.
        $fs = new Filesystem();
        foreach ($this->packages as $package) {
            $packageDir = $this->getVendorDir($package);
            $name = isset($package['name']) ? $package['name'] : '__component__';
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $componentName = $this->getComponentName($name, $extra);
            $fileType = array('scripts', 'styles', 'files');
            foreach ($fileType as $type) {
                // Only act on the files if they're available.
                if (isset($extra['component'][$type]) && is_array($extra['component'][$type])) {
                    foreach ($extra['component'][$type] as $file) {
                        // Make sure the file itself is available.
                        $source = $packageDir.DIRECTORY_SEPARATOR.$file;
                        if (file_exists($source)) {
                            // Find where the file destination should be.
                            $destination = $this->componentDir.DIRECTORY_SEPARATOR.$componentName.DIRECTORY_SEPARATOR.$file;

                            // Ensure the directory is available.
                            $fs->ensureDirectoryExists(dirname($destination));

                            // Delete the file before creating its mirror.
                            $fs->remove($destination);

                            // Symlink the file using a relative path from the destination.
                            $cwd = getcwd();
                            $fullDestination = $cwd.DIRECTORY_SEPARATOR.$destination;
                            $relative = $fs->findShortestPath($fullDestination, realpath($source));
                            try {
                                chdir(dirname($destination));
                                symlink($relative, $fullDestination);
                            } catch (\ErrorException $e) {
                                // If Symlinking failed, try copying.
                                if (!copy($relative, $fullDestination)) {
                                    $this->io->write(sprintf('<error>Failed to produce %s as %s.</error>', $fullDestination, $relative));

                                    return false;
                                }
                            }
                            chdir($cwd);
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
