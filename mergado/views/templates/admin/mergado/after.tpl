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

<div class='mergado-tab' data-tab='2'>
    <div id="mergadoCron">
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-time"></i>
                {l s='Cron list' mod='mergado'}
            </div>

            {if !empty($crons)}
                <div class="alert alert-warning">{l s='Do not forget to add following cron links to cron tasks' mod='mergado'}</div>
                <table id="mergadoCronList">
                    <thead>
                        <tr>
                            <th>
                                {l s='Feed' mod='mergado'}
                            </th>
                            <th>
                                {l s='Cron URL' mod='mergado'}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$crons item=cron}
                            <tr>
                                <th>
                                    {$cron['name']|escape:'htmlall':'UTF-8'}
                                </th>
                                <td>
                                    <a href="{$cron['url']|escape:'htmlall':'UTF-8'}" title="{$cron['name']|escape:'htmlall':'UTF-8'}">{$cron['url']|escape:'htmlall':'UTF-8'}</a>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {else}
                <div class="alert alert-info">{l s='You have no active cron' mod='mergado'}</div>
            {/if}

        </div>
    </div>
</div>
<div class='mergado-tab' data-tab='3'>
    <div id="mergadoXml">
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-exchange"></i>
                {l s='XML list' mod='mergado'}
            </div>

            <div class="alert alert-warning">{l s='Insert following XML links to your account on www.mergado.cz' mod='mergado'}</div>
            <table id="mergadoCronList">
                <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Last change' mod='mergado'}
                        </th>
                        <th>
                            {l s='XML URL' mod='mergado'}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$xmls item=xml}
                        <tr>
                            <th>
                                {$xml['language']|escape:'htmlall':'UTF-8'}
                            </th>
                            <td>
                                {$xml['date']|date_format:'d.m.Y H:m:s'|escape:'htmlall':'UTF-8'}
                            </td>
                            <td>
                                <a href='{$xml['url']|escape:'htmlall':'UTF-8'}' target='_blank' title='{$xml['language']|escape:'htmlall':'UTF-8'}'>{$xml['url']|escape:'htmlall':'UTF-8'}</a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
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
        
    {$tab4}
</div>
<div class='mergado-tab' data-tab='5'>
    <div id="mergadoCron">
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-time"></i>
                {l s='Licence' mod='mergado'}
            </div>
            <p>{l s='Using the module Mergado marketing pack is at your own risk. The creator of module, the company Mergado technologies, LLC, is not liable for any losses or damages in any form. Installing the module into your store, you agree to these terms.' mod='mergado'}</p>
            <p>{l s='The module source code cannot be changed and modified otherwise than the user settings in the administration of PrestaShop.' mod='mergado'}</p>
            <p>{l s='Using the module Mergado marketing pack within PrestaShop is free. Supported versions of PrestaShop are starting 1.5.0.0 above.' mod='mergado'}</p>
        </div>
    </div>
</div>

<script>
    var moduleVersion = '{$moduleVersion}';
    $('.page-head .page-title').append(' (v. ' + moduleVersion + ')');
</script>