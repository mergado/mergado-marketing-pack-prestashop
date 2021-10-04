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

<div class='mergado-tab' data-tab='5'>
    <div class="rowmer">
        <div class="col-content">
            <div id="mergadoCron">
                <div class="panel " id="mergado_fieldset_mergado_lang">
                    <div class="panel-heading">
                        <i class="icon-time"></i>
                        {l s='Licence' mod='mergado'}
                    </div>
                    <p>{l s='Using the module Mergado marketing pack is at your own risk. The creator of module, the company Mergado technologies, LLC, is not liable for any losses or damages in any form. Installing the module into your store, you agree to these terms.' mod='mergado'}</p>
                    <p>{l s='The module source code cannot be changed and modified otherwise than the user settings in the administration of PrestaShop.' mod='mergado'}</p>
                    <p>{l s='Using the module Mergado marketing pack within PrestaShop is free. Supported versions of PrestaShop are 1.6.0.0 up to 1.7.9.99' mod='mergado'}</p>
                </div>
            </div>
        </div>
        <div class="col-side">
            {if isset($sideAd)}
                {$sideAd}
            {/if}
        </div>
    </div>
    <div class="merwide">
        {if isset($wideAd)}
            {$wideAd}
        {/if}
    </div>
</div>