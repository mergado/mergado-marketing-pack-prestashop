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

{if ($heurekaCzActive && count($heurekaCzProducts) && $heurekaCzCode) || ($heurekaSkActive && count($heurekaSkProducts) && $heurekaSkCode)}
    <script>
        var conversionOrderId = '{$conversionOrderId}';
        var heurekaCzCode = '{$heurekaCzCode}';
        var heurekaSkActive = '{$heurekaSkActive}';
        var heurekaSkCode = '{$heurekaSkCode}';
    </script>
{/if}

{if $heurekaCzActive && count($heurekaCzProducts) && $heurekaCzCode}
    <script type="text/javascript">
        {literal}
        var _hrq = _hrq || [];
        _hrq.push(['setKey', heurekaCzCode]);
        _hrq.push(['setOrderId', conversionOrderId]);
        {/literal}
        {foreach from=$heurekaCzProducts item=product}
        _hrq.push(['addProduct', '{$product['name']|escape:'htmlall':'UTF-8'}', '{$product['unitPrice']|escape:'htmlall':'UTF-8'}', '{$product['qty']|escape:'htmlall':'UTF-8'}', '{$product['id']|escape:'htmlall':'UTF-8'}']);
        {/foreach}
        {literal}
        _hrq.push(['trackOrder']);
        (function () {
                var ho = document.createElement('script');
                ho.type = 'text/javascript';
                ho.async =
                    true;
                ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') +
                    '.heureka.cz/direct/js/ext/1-roi-async.js';
                var s =
                    document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ho, s);
            }
        )();
        {/literal}
    </script>
{/if}

{if $heurekaSkActive && count($heurekaSkProducts) && $heurekaSkCode}
    <script type="text/javascript">
        {literal}
        var _hrq = _hrq || [];
        _hrq.push(['setKey', heurekaSkCode]);
        _hrq.push(['setOrderId', conversionOrderId]);
        {/literal}
        {foreach from=$heurekaSkProducts item=product}
        _hrq.push(['addProduct', '{$product['name']|escape:'htmlall':'UTF-8'}', '{$product['unitPrice']|escape:'htmlall':'UTF-8'}', '{$product['qty']|escape:'htmlall':'UTF-8'}']);
        {/foreach}
        {literal}
        _hrq.push(['trackOrder']);
        (function () {
                var ho = document.createElement('script');
                ho.type = 'text/javascript';
                ho.async =
                    true;
                ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') +
                    '.heureka.sk/direct/js/ext/1-roi-async.js';
                var s =
                    document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ho, s);
            }
        )();
        {/literal}
    </script>
{/if}
