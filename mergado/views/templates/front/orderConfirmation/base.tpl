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

<div class="mergadoOrderConfirmation">
    {include file='./partials/zbozi.tpl'}

    {if $advertisementCookieConsent}
        {include file='./partials/heureka.tpl'}
        {include file='./partials/glami.tpl'}
        {include file='./partials/glamiTop.tpl'}
    {/if}
</div>

