{if $googleBadge['IS_INLINE']}
    <script src="https://apis.google.com/js/platform.js" async defer></script>
{else}
    <!-- BEGIN GCR Badge Code -->
    <script src="https://apis.google.com/js/platform.js?onload=renderBadge"
            async defer>
    </script>

    <script>
      window.renderBadge = function() {
        var ratingBadgeContainer = document.createElement("div");
        document.body.appendChild(ratingBadgeContainer);
        window.gapi.load('ratingbadge', function() {
          window.gapi.ratingbadge.render(
              ratingBadgeContainer, {
                "merchant_id": {$googleBadge['MERCHANT_ID']},
                "position": "{$googleBadge['POSITION']}"
              });
        });
      }
    </script>
    <!-- END GCR Badge Code -->
{/if}

{if $googleBadge['LANGUAGE'] !== 'automatically'}
    <!-- BEGIN GCR Language Code -->
    <script>
      window.___gcfg = {
        lang: "{$googleBadge['LANGUAGE']}"
      };
    </script>
    <!-- END GCR Language Code -->
{/if}