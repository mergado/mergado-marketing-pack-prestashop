<!-- Heureka group PRODUCT DETAIL script -->
<script>
  if (typeof conversionData === 'undefined') {
    let conversionData;
  }

  conversionData = {
    'variableName': '{$variableName|escape:'javascript'}',
    'serviceLang': '{$serviceLang|escape:'javascript'}',
    'sdkUrl': '{$sdkUrl|escape:'javascript'}'
  };

  {literal}
  (function(t, r, a, c, k, i, n, g) {t['ROIDataObject'] = k;
  t[k]=t[k]||function(){(t[k].q=t[k].q||[]).push(arguments)},t[k].c=i;n=r.createElement(a),
          g=r.getElementsByTagName(a)[0];n.async=1;n.src=c;g.parentNode.insertBefore(n,g)
          })(window, document, 'script', conversionData.sdkUrl + '?version=2&page=product_detail', conversionData.variableName, conversionData.serviceLang);
  {/literal}
</script>
<!-- End Heureka group PRODUCT DETAIL script -->
