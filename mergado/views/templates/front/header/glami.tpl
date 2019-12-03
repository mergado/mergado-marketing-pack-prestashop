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

{if $glami_pixel_code !== ''}
    <script>{literal}

        (function (f, a, s, h, i, o, n) {
            f['GlamiTrackerObject'] = i;
            f[i] = f[i] || function () {
                (f[i].q = f[i].q || []).push(arguments)
            };
            o = a.createElement(s),
                n = a.getElementsByTagName(s)[0];
            o.async = 1;
            o.src = h;
            n.parentNode.insertBefore(o, n)
        })(window, document, 'script', '//www.glami.cz/js/compiled/pt.js', 'glami');{/literal}
        glami('create', '{$glami_pixel_code}', '{$glami_lang}');
        glami('track', 'PageView');

        {if isset($glami_pixel_category) && $glami_pixel_category}
        glami('track', 'ViewContent', {
            content_type: 'category',
            item_ids: [{$glami_pixel_productIds nofilter}],
            product_names: [{$glami_pixel_productNames nofilter}],
            category_id: '{$glami_pixel_category->id}',
            category_text: '{$glami_pixel_category->name}'
        });
        {/if}

        {if isset($glami_pixel_product) && $glami_pixel_product}
        glami('track', 'ViewContent', {
            content_type: 'product',
            item_ids: ['{$glami_pixel_product->id}'],
            product_names: ['{$glami_pixel_product->name}'],
        });
        {/if}
    </script>
{/if}