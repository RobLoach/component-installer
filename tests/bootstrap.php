<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach <http://robloach.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require __DIR__ . '/../src/bootstrap.php';

// Add the Component Installer test paths.
$loader->add('ComponentInstaller\Test', __DIR__);

// Add the Composer Test paths.
$path = $loader->findFile('Composer\\Composer');
$loader->add('Composer\Test', dirname(dirname(dirname($path))) . '/tests');
