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
    document.addEventListener('DOMContentLoaded', function() {
      if (window.mmp.cookies.sections.advertisement.onloadStatus) {
        if (typeof bianoTrack !== 'undefined') {
          if ($('body#product').length > 0) {
            bianoTrack('track', 'product_view', {ldelim}id: '{$productId}'{rdelim});
          }
        }
      } else {
        window.mmp.cookies.sections.advertisement.functions.bianoTrackPageView = function () {
          if (typeof bianoTrack !== 'undefined') {
            if ($('body#product').length > 0) {
              bianoTrack('track', 'product_view', {ldelim}id: '{$productId}'{rdelim});
            }
          }
        };
      }
    });
</script>