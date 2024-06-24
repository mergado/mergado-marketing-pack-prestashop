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
  var gtmAnalyticsId = '{$gtm_analytics_id}';
  {if $user_data_gtm}
    var gtmUserData = {$user_data_gtm nofilter};
  {else}
    var gtmUserData = {};
  {/if}
</script>

{literal}
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
          'gtm.start':
              new Date().getTime(), event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            '//www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
      })(window, document, 'script', 'dataLayer', gtmAnalyticsId);
    </script>
    <!-- End Google Tag Manager -->
{/literal}

<script>
  {if $gtm_set_customer_data_for_gtag_services && $user_data_gtag}
    window.document.addEventListener('DOMContentLoaded', function () {
      if (typeof gtag !== 'undefined') {
        gtag('set', 'user_data', {$user_data_gtag nofilter});
      }
    });
  {/if}
</script>
