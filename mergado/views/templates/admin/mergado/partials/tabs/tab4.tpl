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

<div class='mergado-tab' data-tab='4'>
    <div id="mergadoCron">
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-time"></i>
                {l s='Contact' mod='mergado'}
            </div>

            {l s='Do not hesitate to contact us on' mod='mergado'} <a href='mailto:prestashop@mergado.cz'>prestashop@mergado.cz</a> {l s='in case of any question.' mod='mergado'}
        </div>
    </div>

    {if isset($tab4)}
        {$tab4}
    {/if}
</div>