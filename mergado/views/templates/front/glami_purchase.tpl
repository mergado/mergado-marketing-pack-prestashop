{if $glami_pixel_orderId != ''}
    <script>
        {if isset($glami_pixel_orderId) && $glami_pixel_orderId}
            glami('track', 'Purchase', {
                item_ids: [{$glami_pixel_productIds}],
                product_names: [{$glami_pixel_productNames}],
                value: {$glami_pixel_value},
                currency: '{$glami_pixel_currency}',
                transaction_id: '{$glami_pixel_orderId}'
            });
        {/if}
    </script>
{/if}