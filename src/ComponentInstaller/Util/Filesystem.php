<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach (http://robloach.net)
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller\Util;

use Composer\Util\Filesystem as BaseFilesystem;

/**
 * Provides basic file system operations.
 */
class Filesystem extends BaseFilesystem
{
    /**
     * Performs a recursive glob search with the given pattern.
     *
     * @param string $pattern
     *   The pattern passed to glob().
     * @param int $flags
     *   Flags to pass into glob().
     *
     * @return mixed
     *  An array of files that match the recursive pattern given.
     */
    public function recursiveGlob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->recursiveGlob($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Performs a recursive glob search for files with the given pattern.
     *
     * @param string $pattern
     *   The pattern passed to glob().
     * @param int $flags
     *   Flags to pass into glob().
     *
     * @return mixed
     *  An array of files that match the recursive pattern given.
     */
    public function recursiveGlobFiles($pattern, $flags = 0)
    {
        $files = $this->recursiveGlob($pattern, $flags);

        return array_filter($files, 'is_file');
    }
}
