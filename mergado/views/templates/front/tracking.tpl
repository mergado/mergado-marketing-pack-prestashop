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
<script>
    var conversionOrderId = '{$conversionOrderId}';
    var conversionZboziShopId = '{$conversionZboziShopId}';
    var conversionZboziTotal = '{$conversionZboziTotal}';
    var heurekaCzCode = '{$heurekaCzCode}';
    var heurekaSkActive = '{$heurekaSkActive}';
    var heurekaSkCode = '{$heurekaSkCode}';
</script>

{if $conversionZboziActive == '1'}
    <script>
        {literal}
            (function (w, d, s, u, n, k, c, t) {
                w.ZboziConversionObject = n;
                w[n] = w[n] || function () {
                    (w[n].q = w[n].q || []).push(arguments)
                };
                w[n].key = k;
                c = d.createElement(s);
                t = d.getElementsByTagName(s)[0];
                c.async = 1;
                c.src = u;
                t.parentNode.insertBefore(c, t)
            })(window, document, "script", "https://www.zbozi.cz/conversion/js/conv.js", "zbozi", conversionZboziShopId);
            // zapnutí testovacího režimu
            // zbozi("useSandbox");
            // nastavení informací o objednávce
            zbozi("setOrder", {
                "orderId": conversionOrderId,
                "totalPrice": conversionZboziTotal
            });
            // odeslání
            zbozi("send");
        {/literal}

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