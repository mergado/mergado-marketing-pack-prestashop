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
    var kelkoo_country = "{$kelkooData['country']}";
    var kelkoo_merchant_id = "{$kelkooData['merchantId']}";
</script>

{if $kelkooData['IS_PS_17']}
    <script type="text/javascript">
        _kkstrack = {
            {literal}merchantInfo: [{ country:kelkoo_country, merchantId:kelkoo_merchant_id }],{/literal}
            orderValue: '{$kelkooData['sales']}',
            orderId: '{$kelkooData['orderId']}',
            basket: {$kelkooData['productsJson']}
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
            orderValue: '{$kelkooData['sales']}',
            orderId: '{$kelkooData['orderId']}',
            basket: {$kelkooData['productsJson'] nofilter}
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
