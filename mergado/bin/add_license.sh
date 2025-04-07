#!/bin/bash

# License text to add
LICENSE="/**
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
 */"

# Directory to scan (default is current directory)
DIR=${1:-.}

# Find all PHP, JS, and CSS files, excluding node_modules, vendor, .git, translations, and vendor inside views directory
find "$DIR" -type f \( -name "*.php" -o -name "*.js" -o -name "*.css" \) \
  ! -path "*/node_modules/*" \
  ! -path "*/vendor/*" \
  ! -path "*/.git/*" \
  ! -path "*/translations/*" \
  ! -path "*/views/vendors/*" | while read -r file; do
  # Check if the file already contains the license
  if ! grep -q "NOTICE OF LICENSE" "$file"; then
    # For PHP files, check for <?php and insert the license after it
    if [[ "$file" == *.php ]]; then
      # Insert the license after the `<?php` tag
      awk -v license="$LICENSE" '
        NR == 1 && /^<\?php/ {
          print $0 "\n\n" license "\n"
          next
        }
        { print }
      ' "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
      echo "License added to $file"
    # For JS and CSS files, insert the license at the very start without deleting anything
    elif [[ "$file" == *.js || "$file" == *.css ]]; then
      # Prepend the license at the start of JS or CSS files with a newline after the license
      (echo "$LICENSE"; echo ""; cat "$file") > "${file}.tmp" && mv "${file}.tmp" "$file"
      echo "License added to $file"
    fi
  else
    echo "License already exists in $file"
  fi
done

echo "License addition completed."
