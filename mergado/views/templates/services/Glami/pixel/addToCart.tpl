<script>
  if (typeof $ !== 'undefined') {
    $('.add-to-cart').on('click', function () {
      var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');
      var $_id = $(this).closest('form').find('#product_page_product_id').val();
      var $_name = $('h1[itemprop="name"]').text();
      var $_price = $('.product-price').find('[itemprop="price"]').attr('content');

      if ($_name === '') {
        $_name = $('.modal-body h1').text();
      }

      if ($(this).closest('form').find('#idCombination').length > 0) {
        $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
      }

      glami('track', 'AddToCart', {
        item_ids: [$_id],
        product_names: [$_name],
        value: $_price,
        currency: $_currency,
        consent: window.mmp.cookies.sections.advertisement.onloadStatus,
      });
    });
  }
</script>
