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
    var conversionOrderId = '{$heurekaConversionOrderId}';
    var heurekaCode = '{$heurekaCode}';
    var heurekaUrl = '{$heurekaUrl}';
</script>

<script type="text/javascript">
    {literal}
    var _hrq = _hrq || [];
    _hrq.push(['setKey', heurekaCode]);
    _hrq.push(['setOrderId', conversionOrderId]);
    {/literal}
    {foreach from=$heurekaProducts item=product}
    _hrq.push(['addProduct', '{$product['name']|escape:'htmlall':'UTF-8'}', '{$product['unitPrice']|escape:'htmlall':'UTF-8'}', '{$product['qty']|escape:'htmlall':'UTF-8'}', '{$product['id']|escape:'htmlall':'UTF-8'}']);
    {/foreach}
    {literal}
    _hrq.push(['trackOrder']);
    (function () {
            var ho = document.createElement('script');
            ho.type = 'text/javascript';
            ho.async =
                true;
            ho.src = 'https://im9.cz/js/ext/1-roi-async.js';
            var s =
                document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ho, s);
        }
    )();
    {/literal}
</script>
