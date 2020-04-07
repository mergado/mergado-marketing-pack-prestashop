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
        {include file='./partials/before.tpl'}
    {if !$disableFeatures || !$disablePlugin}
        {if $disablePlugin}
            {include file='./partials/disabledPlugin.tpl'}
        {elseif $disableFeatures}
            {include file='./partials/disabled.tpl'}
        {else}
            {include file='./partials/tabs/tab0.tpl'}
            {include file='./partials/tabs/tab1.tpl'}
            {include file='./partials/tabs/tab2.tpl'}
            {include file='./partials/tabs/tab3.tpl'}
            {include file='./partials/tabs/tab4.tpl'}
            {include file='./partials/tabs/tab6.tpl'}
        {/if}
    {/if}
        {include file='./partials/tabs/tab5.tpl'}
        {include file='./partials/tabs/tab7.tpl'}
        {include file='./partials/after.tpl'}
</div>