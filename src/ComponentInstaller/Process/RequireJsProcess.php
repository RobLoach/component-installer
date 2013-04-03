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
use Composer\Json\JsonFile;

class RequireJsProcess extends Process
{
    /**
     * The base URL for the require.js configuration.
     */
    protected $baseUrl = 'components';

    public function getMessage()
    {
        return '  <comment>Building require.js</comment>';
    }

    public function init()
    {
        $output = parent::init();
        if ($this->config->has('component-baseurl')) {
            $this->baseUrl = $this->config->get('component-baseurl');
        }

        return $output;
    }

    public function process($message = '')
    {
        // Construct the require.js and stick it in the destination.
        $json = $this->requireJson($this->packages, $this->config);
        $requireConfig = $this->requireJs($json);
        if (file_put_contents($this->componentDir . '/require.config.js', $requireConfig) === FALSE) {
            $this->io->write('<error>Error writing require.config.js</error>');

            return false;
        }

        // Read in require.js to prepare the final require.js.
        $requirejs = file_get_contents(dirname(__DIR__) . '/Resources/require.js');
        if ($requirejs === FALSE) {
            $this->io->write('<error>Error reading in require.js</error>');

            return false;
        }

        // Append the config to the require.js and write it.
        if (file_put_contents($this->componentDir . '/require.js', $requirejs . $requireConfig) === FALSE) {
            $this->io->write('<error>Error writing require.js to the components directory</error>');

            return false;
        }
    }

    /**
     * Creates a require.js configuration from an array of packages.
     *
     * @param $packages
     *   An array of packages from the composer.lock file.
     * @param $config
     *   The Composer Config object.
     *
     * @return array
     *   The built JSON array.
     */
    public function requireJson(array $packages)
    {
        $json = array();

        // Construct the packages configuration.
        foreach ($packages as $package) {
            // Retrieve information from the extra options.
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $options = isset($extra['component']) ? $extra['component'] : array();

            // Construct the base details.
            $name = $this->getComponentName($package['name'], $extra);
            $component = array(
                'name' => $name,
            );

            // Build the "main" directive.
            $scripts = isset($options['scripts']) ? $options['scripts'] : array();
            // @todo Aggregate all "scripts" together into a build.js?
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

        // Provide the baseUrl.
        $json['baseUrl'] = $this->baseUrl;

        return $json;
    }

    /**
     * Constructs the require.js file from the provided require.js JSON array.
     *
     * @param $json
     *   The require.js JSON configuration.
     *
     * @return string
     *   The RequireJS JavaScript configuration.
     */
    public function requireJs(array $json = array())
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
}
