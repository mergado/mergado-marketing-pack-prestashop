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

document.addEventListener('DOMContentLoaded', function () {
  let itemQuantity = 1;

  var baseBlock = $('#add_to_cart').closest('form');
  var attribute_id = baseBlock.find('#idCombination').val();

  const mergedId = window.id_product + '-' + attribute_id;

  const { productJSON, prices } = mmp_GA4_helpers.functions.getProductObjectAttribute(mergedId);

  const {
    itemDiscount,
    currency,
    itemIdMerged,
    itemName,
    itemPrice,
    itemVariantId,
    itemCategory
  } = mmp_GA4_helpers.functions.getProductData(productJSON, prices);

  const value = itemPrice * itemQuantity;

  mmp_GA4_helpers.events.sendViewItem(value, currency, itemIdMerged, itemVariantId, itemName, itemCategory, itemQuantity, itemPrice, itemDiscount);
})
