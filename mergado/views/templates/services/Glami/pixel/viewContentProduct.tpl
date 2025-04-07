<script>
    glami('track', 'ViewContent', {
        content_type: 'product',
        item_ids: ['{$glami_pixel_product->id}'],
        product_names: ['{$glami_pixel_product->name}'],
        consent: window.mmp.cookies.sections.advertisement.onloadStatus
    });
</script>

