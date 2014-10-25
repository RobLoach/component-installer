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
use Composer\Util\Filesystem;

/**
 * Process which copies components from their source to the components folder.
 */
class BuildIncluderProcess extends Process
{
    /**
     * {@inheritdoc}
     */
    public function process()
    {
		return $this->build($this->packages);
    }

    /**
     * Copy file assets from the given packages to the component directory.
     *
     * @param array $packages
     *   An array of packages.
     */
    public function build($packages)
    {
		$includerPackages = [];
		// Iterate over each package that should be processed.
        foreach ($packages as $package) {
            // Retrieve some basic information about the package.
            $type = isset($package['type']) ? $package['type'] : 'library';
            $root = isset($package['is-root']) ? $package['is-root'] : false;

            // Retrieve some information about the package.
            $packageDir = $this->getVendorDir($package);
            $name = isset($package['name']) ? $package['name'] : '__component__';
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $componentName = $this->getComponentName($name, $extra);
			$package['name'] = $componentName;
			$package['path'] = realpath($this->componentDir.DIRECTORY_SEPARATOR.$componentName);
			$package['url'] = $this->config->get('component-url') . '/' . $componentName;
			if (!$root) {
				$includerPackages[$componentName] = $package;
			}
			// Cycle through each asset type.
            $fileType = array('scripts', 'styles', 'files');
            foreach ($fileType as $type) {
                // Only act on the files if they're available.
				if (isset($extra['component'][$type]) && is_array($extra['component'][$type])) {
                    foreach ($extra['component'][$type] as $file) {
                        // Make sure the file itself is available.
                        $source = $packageDir.DIRECTORY_SEPARATOR.$file;

                        // Perform a recursive glob file search on the pattern.
                        foreach ($this->fs->recursiveGlobFiles($source) as $filesource) {
                            // Find the final destination without the package directory.
							$withoutPackageDir = str_replace($packageDir.DIRECTORY_SEPARATOR, '', $filesource);
							
							$package[$type][] = $withoutPackageDir;
                        }
                    }
                }
            }
			if (!$root) {
				$includerPackages[$componentName] = $package;
			}
			
        }

		$template = file_get_contents(__DIR__ . '/../Resources/IncluderTemplate.php');
		ob_start();
			var_export($includerPackages);
		$class = strtr($template, ["'{{packages}}'" => ob_get_clean()]);
		file_put_contents(__DIR__ . '/../Includer.php', $class);
		
        return true;
    }
}
