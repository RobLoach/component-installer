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
use Composer\Package\Package;

class CopyProcess extends Process
{
    protected $installationManager;

    public function __construct(Composer $composer, IOInterface $io)
    {
        parent::__construct($composer, $io);

        $this->installationManager = $composer->getInstallationManager();
    }

    public function process()
    {
        $this->io->write('  <comment>Copying assets to component directory</comment>');

        foreach ($this->packages as $package) {
            $packageDir = $this->getVendorDir($package);
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $componentName = $this->getComponentName($package['name'], $extra);
            $fileType = array('scripts', 'styles', 'files');
            foreach ($fileType as $type) {
                if (isset($extra['component'][$type]) && is_array($extra['component'][$type])) {
                    foreach ($extra['component'][$type] as $file) {
                        $source = $packageDir.DIRECTORY_SEPARATOR.$file;
                        if (file_exists($source)) {
                            $destination = $this->componentDir.DIRECTORY_SEPARATOR.$componentName.DIRECTORY_SEPARATOR.$file;
                            $dir = dirname($destination);
                            if (!is_dir($dir)) {
                                mkdir(dirname($destination), 0777, true);
                            }
                            copy($source, $destination);
                        }
                    }
                }
            }
        }
    }

    public function getVendorDir(array $package)
    {
        $loader = new \Composer\Package\Loader\ArrayLoader();
        $completePackage = $loader->load($package);
        return $this->installationManager->getInstallPath($completePackage);
    }
}
