<?php
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
 *  @license   LICENSE.txt
 */

class MergadoCartItem
{
    /**
     * Item name.
     *
     * @var string
     */
    public $productName;

    /**
     * Item identifier.
     *
     * @var string
     */
    public $itemId;

    /**
     * Price per one item (in CZK).
     *
     * @var float
     */
    public $unitPrice;

    /**
     * Number of items ordered.
     *
     * @var int
     */
    public $quantity;
}
