{*
* NOTICE OF LICENSE
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
*}

<div class="mergado-tab" data-tab="1">
    <div class="rowmer">
        <div class="col-content">
            <h2>{l s='Product export settings' mod='mergado'}</h2>
            {$tab1['exportProducts']}
            <h2>{l s='Category export settings' mod='mergado'}</h2>
            {$tab1['exportCategory']}
            <h2>{l s='Analytical export settings' mod='mergado'}</h2>
            {$tab1['exportStatic']}
            <h2>{l s='Import prices settings' mod='mergado'}</h2>
            {$tab1['importPrices']}
        </div>
        <div class="col-side">
            {$sideAd}
        </div>
    </div>
    <div class="merwide">
        {$wideAd}
    </div>
</div>