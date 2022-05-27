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

<div class="mergado-page" data-page="6" data-toggle-fields-json={$mmp['toggleFieldsJSON']}>
    <div class="rowmer">
        <div class="col-content mmp-tabs--horizontal
            {if version_compare(_PS_VERSION_, Mergado::PS_V_16) > 0}
                ps17
            {/if}">
            <ul class="mmp-tabs mmp-tabs__menu">
                {if version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0}
                    {foreach $mmp['pageContent']['adsys'] as $key => $tab}
                        <li class="{if isset($tab['active']) && $tab['active']}active{/if}">
                            <a href="#" data-mmp-tab-button="{$key}">
                        {if isset($tab['icon']) && $tab['icon'] !== ''}
                            <svg class="mmp_icon">
                                <use xlink:href="{$mmp['images']['baseMmpImageUrl']}{$tab['icon']}"></use>
                            </svg>
                        {/if}

                            {$tab['title']}
                            </a>
                        </li>
                    {/foreach}
                {else}
                    {foreach from=$mmp['pageContent']['adsys'] key=$key item=$tab}
                        <li class="{if isset($tab['active']) && $tab['active']}active{/if}">
                            <a href="#" data-mmp-tab-button="{$key}">
                                {if isset($tab['icon']) && $tab['icon'] !== ''}
                                    <svg class="mmp_icon">
                                        <use xlink:href="{$mmp['images']['baseMmpImageUrl']}{$tab['icon']}"></use>
                                    </svg>
                                {/if}
                                    {$tab['title']}
                            </a>
                        </li>
                    {/foreach}
                {/if}
            </ul>

            <div class="mmp-tabs mmp-tabs__content">
                {if version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0}
                    {foreach $mmp['pageContent']['adsys'] as $key => $tab}
                        <div class="mmp-tabs__tab {if isset($tab['active']) && $tab['active']}active{/if}"
                             data-mmp-tab="{$key}">
                            {$tab['form']}
                        </div>
                    {/foreach}
                {else}
                    {foreach from=$mmp['pageContent']['adsys'] key=$key item=$tab}
                        <div class="mmp-tabs__tab {if isset($tab['active']) && $tab['active']}active{/if}"
                             data-mmp-tab="{$key}">
                            {$tab['form']}
                        </div>
                    {/foreach}

                {/if}
            </div>
        </div>
        <div class="col-side">
            {if isset($mmp['pageContent']['ads']['side'])}
                {$mmp['pageContent']['ads']['side']}
            {/if}
        </div>
    </div>
    <div class="merwide">
        {if isset($mmp['pageContent']['ads']['wide'])}
            {$mmp['pageContent']['ads']['wide']}
        {/if}
    </div>
</div>
