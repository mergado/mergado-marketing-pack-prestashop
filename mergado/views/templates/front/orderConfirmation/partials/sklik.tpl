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

{if $sklik === '1' && $sklikCode && $sklikCode !== ''}
    {if $sklikValue == ''}
        <iframe width="119" height="22" frameborder="0" scrolling="no" src="http://out.sklik.cz/conversion?c={$sklikCode}&color=ffffff&v={$totalWithoutShippingAndVat}"></iframe>
    {else}
        <iframe width="119" height="22" frameborder="0" scrolling="no" src="http://out.sklik.cz/conversion?c={$sklikCode}&color=ffffff&v={$sklikValue}"></iframe>
    {/if}
{/if}