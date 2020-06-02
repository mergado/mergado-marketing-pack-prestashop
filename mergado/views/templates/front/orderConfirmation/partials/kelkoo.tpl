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
    var kelkoo_country = "{$kelkoo_country}";
    var kelkoo_merchant_id = "{$kelkoo_merchant_id}";
</script>

{if $PS_VERSION < 1.7}
    <script type="text/javascript">
        _kkstrack = {
            {literal}merchantInfo: [{ country:kelkoo_country, merchantId:kelkoo_merchant_id }],{/literal}
            orderValue: '{$kelkoo_sales}',
            orderId: '{$kelkoo_orderId}',
            basket: {$kelkoo_products_json}
        };
        (function() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'https://s.kk-resources.com/ks.js';
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        })();
    </script>
{else}
    <script type="text/javascript">
        _kkstrack = {
            merchantInfo: [{ country:kelkoo_country, merchantId:kelkoo_merchant_id }],
            orderValue: '{$kelkoo_sales}',
            orderId: '{$kelkoo_orderId}',
            basket: {$kelkoo_products_json nofilter}
        };
        (function() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'https://s.kk-resources.com/ks.js';
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        })();
    </script>
{/if}
