<!-- Facebook Pixel Code -->
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

    {if $page_name == 'product'}
        fbq('trackCustom', 'ViewProduct', {
            content_name: sharing_name,
            content_type: 'product',
            content_ids: ['{$product->id}']
        });
    {elseif $page_name == 'category'}
        var fbProducts = {$products|json_encode};
        var fbProductsArray = new Array();
        if (fbProductsArray.length > 0) {
            fbProducts.forEach(function (p) {
                fbProductsArray.push(p.id_product);
            });
        }
        fbq('trackCustom', 'ViewCategory', {
            content_name: '{$meta_title}',
            content_type: 'product',
            content_ids: fbProductsArray
        });
    {elseif $page_name == 'search'}
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
            content_name: '{$meta_title}'
        });
    {/if}

        $('#add_to_cart button[type=submit], .ajax_add_to_cart_button').on('click', function (e) {
            fbq('track', 'AddToCart');
        });

</script>
<noscript>
<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={$fbPixelCode}&ev=PageView&noscript=1" />
</noscript>
<!-- DO NOT MODIFY -->
<!-- End Facebook Pixel Code -->
