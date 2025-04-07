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
 *  @license   license.txt
 */
?>

<?php echo $issue ?>

<br><br>

<table>
    <tbody>
        <tr>
            <td>
                <?php echo $this->formattedTable($settingsData['base']); ?>
            </td>
            <td>
                <?php echo $this->formatAds($settingsData['adsystems']); ?>

            </td>
        </tr>
    </tbody>
</table>

<style>
    table td {
        vertical-align: top;
    }

    table.special {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        font-size: 12px;
    }

    table.special td, table th {
        border: 1px solid #ddd;
        padding: 6px;
    }

    table.special tr:nth-child(even){background-color: #f2f2f2;}

    table.special tr:hover {background-color: #ddd;}

    table.special thead th {
        padding-top: 8px;
        padding-bottom: 8px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
        font-weight: 600;
    }

    tr.active td,
    tr.active th {
        background-color: #e4ffc3;
    }

    table.special tbody th {
        font-weight: 500;
        text-align: left;
    }
</style>
