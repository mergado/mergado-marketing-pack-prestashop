<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Manager;

use Mergado\Service\Feed\Base\BaseFeed;

class DirectoryManager {
    public static function checkAndCreateTmpDataDir(): void
    {
        self::createDIR([BaseFeed::TMP_DIR, BaseFeed::XML_DIR]);
    }

    /**
     * Create directory for xml generator if not exist
     */
    public static function createDIR(array $dirPaths): void
    {
        foreach ($dirPaths as $item) {
            if (!is_dir($item)) {
                mkdir($item, 0777, true);
            }
        }
    }

    /**
     * Remove all files in directory
     */
    public static function removeFilesInDirectory($dir): void
    {
        $files = glob($dir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                array_map('unlink', glob("$file/*.*"));
            }
        }
    }

    /**
     * Remove file in path
     */
    public static function removeFile($file): void
    {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
