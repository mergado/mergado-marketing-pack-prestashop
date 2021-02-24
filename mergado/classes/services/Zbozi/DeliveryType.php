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
 * @author    www.mergado.cz
 * @copyright 2016 Mergado technologies, s. r. o.
 * @license   LICENSE.txt
 */

namespace Mergado\Zbozi;

class DeliveryType
{

    // Based on https://napoveda.seznam.cz/cz/zbozi/napoveda-pro-internetove-obchody/zbozi-xml-feed/specifikace-xml-feedu/#DELIVERY
    // only: Česká pošta na poštu is different
    public static function getDeliveryType($type)
    {
        switch($type) {
            case 'Česká pošta na poštu':
                return 'CESKA_POSTA_NA_POSTU';
                break;
            case 'DPD Pickup':
                return 'DPD_PICKUP';
                break;
            case 'Geis Point':
                return 'GEIS_POINT';
                break;
            case 'GLS Parcelshop':
                return 'GLS_PARCELSHOP';
                break;
            case 'PPL ParcelShop':
                return 'PPL_PARCELSHOP';
                break;
            case 'Toptrans Depo':
                return 'TOPTRANS_DEPO';
                break;
            case 'Uloženka':
                return 'ULOZENKA';
                break;
            case 'Zásilkovna':
                return 'ZASILKOVNA';
                break;
            case 'Vlastní místa':
                return 'VLASTNI_VYDEJNI_MISTA';
                break;
            case 'Česká pošta':
                return 'CESKA_POSTA';
                break;
            case 'DB_SCHENKER':
                return 'DB_SCHENKER';
                break;
            case 'DPD':
                return 'DPD';
                break;
            case 'DHL':
                return 'DHL';
                break;
            case 'DSV':
                return 'DSV';
                break;
            case 'FOFR':
                return 'FOFR';
                break;
            case 'Gebrüder Weiss':
                return 'GEBRUDER_WEISS';
                break;
            case 'Geis':
                return 'GEIS';
                break;
            case 'GLS':
                return 'GLS';
                break;
            case 'HDS':
                return 'HDS';
                break;
            case 'InTime':
                return 'INTIME';
                break;
            case 'MESSENGER':
                return 'MESSENGER';
                break;
            case 'PPL':
                return 'PPL';
                break;
            case 'TNT':
                return 'TNT';
                break;
            case 'TOPTRANS':
                return 'TOPTRANS';
                break;
            case 'UPS':
                return 'UPS';
                break;
            case 'FedEx':
                return 'FEDEX';
                break;
            case 'Raben Logistics':
                return 'RABEN_LOGISTICS';
                break;
            case 'RHENUS':
                return 'RHENUS';
                break;
            case 'Vlastní přeprava':
                return 'VLASTNI_PREPRAVA';
                break;
            default:
                return $type;
                break;
        }
    }
}
