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
    var fbPixelCode = {$fbPixelCode};
    {literal}
    !function (f, b, e, v, n, t, s) {
        if (f.fbq)
            return;
        n = f.fbq = function () {
            n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq)
            f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
    }(window,
        document, 'script', '//connect.facebook.net/en_US/fbevents.js');
    fbq('init', fbPixelCode);
    fbq('track', 'PageView');
    {/literal}

    {* PS 1.6 *}
    {if isset($page_name)}
        {$p_name = $page_name}
    {* PS 1.7 *}
    {else}
        {$p_name = $page.page_name}
    {/if}

    {* PS 1.6 *}
    {if isset($meta_title)}
        {$m_title = $meta_title}
    {* PS 1.7 *}
    {else}
        {$m_title = $page.meta.title}
    {/if}

    {if $p_name == 'product'}

    var contentName = '';
    if(typeof sharing_name !== 'undefined') {
        contentName = [sharing_name]; {* PS 1.6 *}
    } else {
        contentName = ['{$product->name}']; {* PS 1.7 *}
    }

    fbq('trackCustom', 'ViewProduct', {

        cotnent_name: contentName,
        content_type: 'product',
        content_ids: ['{$product->id}']
    });
    {elseif $p_name == 'category'}

    {if isset($products)}
        var fbProducts = {$products|json_encode};
    {else}
        var fbProducts = '';
    {/if}

    var fbProductsArray = new Array();
    if (fbProductsArray.length > 0) {
        fbProducts.forEach(function (p) {
            fbProductsArray.push(p.id_product);
        });
    }
    fbq('trackCustom', 'ViewCategory', {
        content_name: '{$m_title}',
        content_type: 'product',
        content_ids: fbProductsArray
    });
    {elseif $p_name == 'search'}
    var fbProducts = {$products|json_encode};
    var fbProductsArray = new Array();
    if (fbProductsArray.length > 0) {
        fbProducts.forEach(function (p) {
            fbProductsArray.push(p.id_product);
        });
    }
    fbq('track', 'Search', {
        search_string: '{$searchQuery}',
        content_ids: fbProductsArray,
        content_type: 'product'
    });
    {else}
    fbq('track', 'ViewContent', {
        content_name: '{$m_title}'
    });
    {/if}
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={$fbPixelCode}&ev=PageView&noscript=1"/>
</noscript>
