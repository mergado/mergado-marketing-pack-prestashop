{if $glami_pixel_orderId != ''}
    <script>
        {if isset($glami_pixel_orderId) && $glami_pixel_orderId}
        document.onload = function () {
                glami('track', 'Purchase', {
                    item_ids: {$glami_pixel_productIds nofilter},
                    product_names: {$glami_pixel_productNames nofilter},
                    value: {$glami_pixel_value},
                    currency:{if _PS_VERSION_ < 1.7}'{$glami_pixel_currency}'{else}'{$currency.iso_code}'{/if},
                    transaction_id: '{$glami_pixel_orderId}'
                });
            };
        {/if}
    </script>
{/if}