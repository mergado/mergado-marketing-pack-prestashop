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

<div id="mergadoController">
        {include file='./partials/header.tpl'}
        {include file='./partials/popup.tpl'}

    {if $mmp['base']['multistoreShopSelected'] || $mmp['base']['moduleEnabled']}


        {if !$mmp['base']['moduleEnabled']} {* Plugin disabled *}
            {include file='./partials/disabledPlugin.tpl'}
        {elseif !$mmp['base']['multistoreShopSelected']} {* Shop not selected *}
            {include file='./partials/disabled.tpl'}
        {else}
            {if !isset($smarty.get.page) || $smarty.get.page === 'info' || !$smarty.get.page} {* If nothing selected set Info as shown *}
                {include file='./pages/info.tpl'}
            {elseif $smarty.get.page === 'feeds-product'}
                {include file='./pages/feeds-product.tpl'}
            {elseif $smarty.get.page === 'feeds-other'}
                {include file='./pages/feeds-other.tpl'}
            {elseif $smarty.get.page === 'adsys' || $smarty.get.page === 'cookies'}
                {include file='./pages/adsys.tpl'}
            {elseif $smarty.get.page === 'news'}
                {include file='./pages/news.tpl'}
            {/if}
        {/if}
    {/if}

    {if isset($smarty.get.page)}
        {if $smarty.get.page === 'licence'}
            {include file='./pages/licence.tpl'}
        {elseif $smarty.get.page === 'support'}
            {include file='./pages/support.tpl'}
        {/if}
    {/if}

    {include file='./partials/footer.tpl'}
</div>