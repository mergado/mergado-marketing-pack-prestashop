<!-- Heureka group THANK YOU PAGE script -->
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
              }) (window, document, 'script', conversionData.sdkUrl + '?version=2&page=thank_you', conversionData.variableName, conversionData.serviceLang);
  {/literal}

  window[conversionData.variableName]('authenticate', '{$apiKey}');

  window[conversionData.variableName]('set_order_id', '{$orderId}');

  {foreach from=$products item=product}
      window[conversionData.variableName]('add_product', '{$product['id']}', '{$product['name']}', '{$product['unitPriceWithVat']}', '{$product['quantity']}');
  {/foreach}

  window[conversionData.variableName]('set_total_vat', '{$totalPriceWithVat}');
  window[conversionData.variableName]('set_currency', '{$currency}');
  window[conversionData.variableName]('send', 'Order');
</script>
<!-- End Heureka group THANK YOU PAGE script -->
