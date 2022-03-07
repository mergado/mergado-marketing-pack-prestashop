<?php

namespace Mergado\Tools;

class DirectoryManager {
    public static function checkAndCreateTmpDataDir()
    {
        DirectoryManager::createDIR([XMLClass::TMP_DIR, XMLClass::XML_DIR]);
    }

    /**
     * Create directory for xml generator if not exist
     *
     * @param array $dirPaths
     */
    public static function createDIR(array $dirPaths)
    {
        foreach ($dirPaths as $item) {
            if (!is_dir($item)) {
                mkdir($item);
            }
        }
    }

    /**
     * Remove all files in directory
     *
     * @param $dir
     */

    public static function removeFilesInDirectory($dir)
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
     * @param $file
     */
    public static function removeFile($file)
    {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
