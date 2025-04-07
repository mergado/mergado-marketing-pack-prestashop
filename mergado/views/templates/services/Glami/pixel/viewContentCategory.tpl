<script>
    glami('track', 'ViewContent', {
        content_type: 'category',
        item_ids: [{$glami_pixel_productIds nofilter}],
        product_names: [{$glami_pixel_productNames nofilter}],
        category_id: '{$glami_pixel_category->id}',
        category_text: '{$glami_pixel_category->name}',
        consent: window.mmp.cookies.sections.advertisement.onloadStatus
    });
</script>

