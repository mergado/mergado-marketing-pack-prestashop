<!-- BEGIN GCR Opt-in Module Code -->

{if $googleReviewsFunctionalCookies}
    <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
{else}
    <script>
      window.mmp.cookies.sections.functional.functions.googleReviewsOptIn = function () {
        $('body').append('<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer><\/script>');
      };
    </script>
{/if}

<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
          {
            "merchant_id": "{$googleReviewsOptIn['MERCHANT_ID']}",
            "order_id": "{$googleReviewsOptIn['ORDER']['ID']}",
            "email": "{$googleReviewsOptIn['ORDER']['CUSTOMER_EMAIL']}",
            "delivery_country": "{$googleReviewsOptIn['ORDER']['COUNTRY_CODE']}",
            "estimated_delivery_date": "{$googleReviewsOptIn['ORDER']['ESTIMATED_DELIVERY_DATE']}",
            {if $googleReviewsOptIn['ORDER']['PRODUCTS']}
            "products": {$googleReviewsOptIn['ORDER']['PRODUCTS'] nofilter},
            {/if}
            "opt_in_style": "{$googleReviewsOptIn['POSITION']}"
          });
    });
  }
</script>
<!-- END GCR Opt-in Module Code -->

<!-- BEGIN GCR Language Code -->
{if $googleReviewsOptIn['LANGUAGE'] !== 'automatically'}
    <script>
      window.___gcfg = {
        lang: "{$googleReviewsOptIn['LANGUAGE']}"
      };
    </script>
{/if}
<!-- END GCR Language Code -->