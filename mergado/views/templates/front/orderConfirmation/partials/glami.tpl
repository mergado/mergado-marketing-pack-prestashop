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

{if $glami_orderId !== ''}
    <script>
        {if isset($glami_orderId) && $glami_orderId}
            {if $glami_active == 1}
                // GLAMI PIXEL
                glami('track', 'Purchase', {
                    item_ids: {$glami_productIds nofilter},
                    product_names: {$glami_productNames nofilter},
                    value: {$glami_value},
                    currency: {if _PS_VERSION_ < 1.7}'{$glami_currency}'{else}'{$currency.iso_code}'{/if},
                    transaction_id: '{$glami_orderId}'
                });
            {/if}

            {if $glami_top_active == 1 && $glami_top_lang_active && $glami_top_code !== '' && $lang !== '' && $langIsoCode !== '' && $glami_email !== ''}
                // GLAMI TOP
                var glami_top_url_active = '{$glami_top_url_active}';

                {literal}
                (function (f, a, s, h, i, o, n) {
                    f['GlamiOrderReview'] = i;
                    f[i] = f[i] || function () {(f[i].q = f[i].q || []).push(arguments);};
                    o = a.createElement(s), n = a.getElementsByTagName(s)[0];
                    o.async = 1; o.src = h; n.parentNode.insertBefore(o, n);
                })(window,document,'script','//www.' + glami_top_url_active + '/js/compiled/or.js', 'glami_or');
                {/literal}
                glami_or('addParameter', 'merchant_id','{$glami_top_code}', '{$glami_top_lang_active}'); //cz
                glami_or('addParameter', 'order_id', '{$glami_orderId}');
                glami_or('addParameter', 'email', '{$glami_email}');
                glami_or('addParameter', 'language', '{$langIsoCode}'); //cs
                glami_or('addParameter', 'items', [{$glami_products nofilter}]);

                glami_or('create');
            {/if}
        {/if}
    </script>
{/if}
