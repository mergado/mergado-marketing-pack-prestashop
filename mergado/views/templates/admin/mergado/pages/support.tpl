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

<div class='mergado-page'>
    <div class="rowmer">
        <div class="col-content">
            <div class="panel " id="mergado_fieldset_mergado_lang">
               <h1 class="mmp_h1">{l s='Contact support' mod='mergado'}</h1>

                <div class="mmp_support">
                    <div class="mmp_support__form">
                        {include file='./partials/support/form/form.tpl'}
                    </div>
                    <div class="mmp_support__links">
                        {include file='./partials/support/links.tpl'}
                    </div>
                </div>
            </div>

            <div class="panel " id="mergado_fieldset_mergado_lang">
                <h1 class="mmp_h1">{l s='Logs' mod='mergado'}</h1>
                {include file='./partials/support/logs.tpl'}
            </div>

            {if isset($tab4)}
                {$tab4}
            {/if}
        </div>
        <div class="col-side">
        </div>
    </div>
</div>