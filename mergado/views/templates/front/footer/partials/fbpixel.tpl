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
    {/literal}

    {if $fbPixel_advertisement_consent}
        fbq('consent', 'grant');
    {else}
        fbq('consent', 'revoke');
    {/if}

    {if !$fbPixel_advertisement_consent}
        window.mmp.cookies.sections.advertisement.functions.fbpixel = function () {
          fbq('consent', 'grant');
          fbq('track', 'PageView');
        };
    {/if}

    {literal}
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
        document.addEventListener('DOMContentLoaded', function () {
            var contentName = '';
            if (typeof sharing_name !== 'undefined') {
             contentName = [sharing_name]; {* PS 1.6 *}
            } else {
              contentName = ['{$product->name}']; {* PS 1.7 *}
            }

            var productId = '';

            {if $product === NULL}
              productId = '{$productId}'; {* PS 1.6 *}
              var combination16 = $('#idCombination');
              var combination16Value = combination16.val();

              if (combination16.length > 0 && combination16Value !== '' && combination16Value != 0) {
                productId = productId + '-' + combination16Value;
              }
            {else}
              productId = '{$product->id_product}'; {* PS 1.7 *}

              if ({$product->id_product_attribute} !== 0) {
                productId = productId + '-' + {$product->id_product_attribute};
              }
            {/if}

            fbq('trackCustom', 'ViewContent', {
              content_name: contentName,
              content_type: 'product',
              content_ids: [productId]
            });
        });

    {elseif $p_name == 'category'}
        {if isset($glami_pixel_productIds)}
            {if (float)_PS_VERSION_ >= Mergado::PS_V_17} {* PS 1.7 *}
                var fbProductsArray = {$glami_pixel_productIds nofilter};
            {else}
                var fbProductsArray = {$glami_pixel_productIds};
            {/if}
        {else}
            var fbProductsArray = '';
        {/if}

        fbq('trackCustom', 'ViewCategory', {
            content_name: '{$m_title}',
            content_type: 'product',
            content_ids: fbProductsArray
        });
    {elseif $p_name == 'search'}
    document.addEventListener('DOMContentLoaded', function () {

        var fbProductsArray = [];

        $('[data-id-product]').each(function () {
          var $_id = $(this).attr('data-id-product');
          var $_id_attribute = $(this).attr('data-id-product-attribute');
          if ($_id_attribute) {
            if($_id_attribute && $_id_attribute !== '' && $_id_attribute !== '0') {
              $_id = $_id + '-' + $(this).attr('data-id-product-attribute');
            }
          }

          fbProductsArray.push($_id);
        });

        var query = '';

        if(typeof prestashop != 'undefined') {
            query = '{$smarty.get['s']|default: '""'}';
        } else {
            query = '{$searchQuery}';
        }

        fbq('track', 'Search', {
            search_string: query,
            content_ids: fbProductsArray,
            content_type: 'product'
        });
    });
{*    {else}*}
    // fbq('track', 'ViewContent', {
    {*    content_name: '{$m_title}'*}
    // });
    {/if}
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={$fbPixelCode}&ev=PageView&noscript=1"/>
</noscript>
