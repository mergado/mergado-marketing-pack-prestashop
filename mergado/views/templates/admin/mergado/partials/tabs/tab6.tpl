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

<div class="mergado-tab" data-tab="6" data-toggle-fields-json={$toggleFieldsJSON}>
    <div class="rowmer">
        <div class="col-content">
            <ul class="mmp_tabs mmp_tabs__menu">
                {if version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0}
                    {foreach $tab6 as $key => $tab}
                        <li class="{if isset($tab['active']) && $tab['active']}active{/if}">
                            <a href="#" data-mmp-tab-button="{$key}">
                                {$tab['title']}
                            </a>
                        </li>
                    {/foreach}
                {else}
                    {foreach from=$tab6 key=$key item=$tab}
                        <li class="{if isset($tab['active']) && $tab['active']}active{/if}">
                            <a href="#" data-mmp-tab-button="{$key}">
                                {$tab['title']}
                            </a>
                        </li>
                    {/foreach}
                {/if}
            </ul>

            <div class="mmp_tabs mmp_tabs__content">
                {if version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0}
                    {foreach $tab6 as $key => $tab}
                        <div class="mmp_tabs__tab {if isset($tab['active']) && $tab['active']}active{/if}"
                             data-mmp-tab="{$key}">
                            {$tab['form']}
                        </div>
                    {/foreach}
                {else}
                    {foreach from=$tab6 key=$key item=$tab}
                        <div class="mmp_tabs__tab {if isset($tab['active']) && $tab['active']}active{/if}"
                             data-mmp-tab="{$key}">
                            {$tab['form']}
                        </div>
                    {/foreach}

                {/if}
            </div>
        </div>
        <div class="col-side">
            {$sideAd}
        </div>
    </div>
</div>