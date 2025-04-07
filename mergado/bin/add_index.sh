#!/bin/bash

# Define the content for the index.php file
index_content="<?php
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
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

header('Location: ../');
exit;"

# Find directories, excluding .git and hidden folders but including valid ones
find . -type d \( ! -path '*/.git*' ! -path '*/node_modules*' ! -path '*/vendor*' \) | while read dir; do
    # Count files in the directory, excluding index.php
    file_count=$(find "$dir" -mindepth 1 -maxdepth 1 -type f ! -name 'index.php' | wc -l)

    # If the directory contains files and index.php is missing, create it
    if [ "$file_count" -gt 0 ] && [ ! -f "$dir/index.php" ]; then
        echo "$index_content" > "$dir/index.php"
        echo "Created index.php in $dir"
    fi

    # If no other files exist and index.php is present, delete it
    if [ "$file_count" -eq 0 ] && [ -f "$dir/index.php" ]; then
        rm "$dir/index.php"
        echo "Deleted index.php from $dir (no other files present)"
    fi
done

echo "Process complete."
