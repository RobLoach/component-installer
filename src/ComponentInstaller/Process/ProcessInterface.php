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

interface ProcessInterface
{
    /**
     * Construct a new process to act on the given Composer and IO.
     */
    public function __construct(Composer $composer, IOInterface $io);

    /**
     * Run through the process.
     */
    public function process();

    /**
     * Initialize the process before its run.
     *
     * @return boolean
     *   Whether or not the process should continue after initialization.
     */
    public function init();

    /**
     * Provides a message summary for the process.
     */
    public function getMessage();
}
