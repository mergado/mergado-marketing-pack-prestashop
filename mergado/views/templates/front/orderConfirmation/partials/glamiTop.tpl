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

{if isset($glamiTopData['orderId']) && $glamiTopData['orderId'] !== '' && $glamiTopData['orderId']}
    {if $glamiTopData['active'] == 1 && $glamiTopData['active'] && $glamiTopData['code'] !== '' && $lang !== '' && $langIsoCode !== '' && $glamiTopData['email'] !== ''}
        <script>
            var glami_top_url_active = '{$glamiTopData['url_active']}';

            {literal}
            (function (f, a, s, h, i, o, n) {
                f['GlamiOrderReview'] = i;
                f[i] = f[i] || function () {(f[i].q = f[i].q || []).push(arguments);};
                o = a.createElement(s), n = a.getElementsByTagName(s)[0];
                o.async = 1; o.src = h; n.parentNode.insertBefore(o, n);
            })(window,document,'script','//www.' + glami_top_url_active + '/js/compiled/or.js', 'glami_or');
            {/literal}
            glami_or('addParameter', 'merchant_id','{$glamiTopData['code']}', '{$glamiTopData['lang_active']}'); //cz
            glami_or('addParameter', 'order_id', '{$glamiTopData['orderId']}');
            glami_or('addParameter', 'email', '{$glamiTopData['email']}');
            glami_or('addParameter', 'language', '{$langIsoCode}'); //cs
            glami_or('addParameter', 'items', [{$glamiTopData['products'] nofilter}]);

            glami_or('create');
        </script>
    {/if}
{/if}
