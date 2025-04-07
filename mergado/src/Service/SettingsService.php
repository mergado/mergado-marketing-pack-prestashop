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


namespace Mergado\Service;

class SettingsService extends AbstractBaseService
{
    public const FEED = [
        'STATIC' => 'static_feed',
        'CATEGORY' => 'category_feed'
    ];

    // Checkboxes saves 'on' instead of '1' as ON/OFF.. Take care!
    public const EXPORT = [
        'BOTH' => 'what_to_export_both',
        'CATALOG' => 'what_to_export_catalog',
        'SEARCH' => 'what_to_export_search',
        'NONE' => 'what_to_export_none',
        'COST' => 'm_export_wholesale_prices',
        'DENIED_PRODUCTS' => 'mmp_export_denied_products',
        'DENIED_PRODUCTS_OTHER' => 'mmp_export_denied_products_other',
    ];

    public const IMPORT = [
//        'ENABLED' => 'import_products',
        'COUNT' => 'import_products_count',
        'LAST_UPDATE' => 'mergado_last_prices_import',
        'URL' => 'import_product_prices_url',
    ];

    // Module options
    public const ENABLED = 1;
    public const DISABLED = 0;
}
