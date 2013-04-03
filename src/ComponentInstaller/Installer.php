<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\Script\Event;

/**
 * The Component Installer for Composer.
 */
class Installer extends LibraryInstaller
{

    /**
     * {@inheritDoc}
     *
     * Components are supported by all packages.
     */
    public function supports($packageType)
    {
        // Components are supported by all package types. We will just act on
        // the root package if it's available.
        $rootPackage = isset($this->composer) ? $this->composer->getPackage() : null;
        if (isset($rootPackage)) {
            $scripts = $rootPackage->getScripts();
            $scripts['post-install-cmd']['component-installer'] = 'ComponentInstaller\\Installer::postInstall';
            $scripts['post-update-cmd']['component-installer'] = 'ComponentInstaller\\Installer::postInstall';
            $rootPackage->setScripts($scripts);
        }

        // Explicitly state support of "component" packages.
        return (bool) ($packageType === 'component');
    }

    /**
     * Post install/update command, called from the Composer scripts.
     */
    public static function postInstall(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $io->write('<info>Compiling Component files</info>');

        // Set up all the processes.
        $processes = array(
            "ComponentInstaller\\Process\\CopyProcess",
            "ComponentInstaller\\Process\\RequireJsProcess",
            "ComponentInstaller\\Process\\RequireCssProcess",
        );

        // Initialize and execute each process in sequence.
        foreach ($processes as $class) {
            $process = new $class($composer, $io);
            // When an error occurs during initialization, end the process.
            if (!$process->init()) {
                $io->write('<error>An error occurred while initializing the process.</info>');
                break;
            }
            $process->process();
        }
    }
}
