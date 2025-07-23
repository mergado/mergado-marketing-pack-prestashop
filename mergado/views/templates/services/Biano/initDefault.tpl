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

<!-- Biano Pixel Code -->
<script>
  const bianoPixelConfig = {
    consent: window.mmp.cookies.sections.advertisement.onloadStatus,
    debug: false
  };

  const bianoDomain = '{$domain}';

  {literal}
  !function (b, i, a, n, o, p, x, s) {
    if (b.bianoTrack) return;
    o = b.bianoTrack = function () {
      o.callMethod ?
        o.callMethod.apply(o, arguments) : o.queue.push(arguments)
    };
    o.push = o;
    o.queue = [];
    a = a || {};
    n = a.consent === void (0) ? !0 : !!a.consent;
    o.push('consent', n);
    s = 'script';
    p = i.createElement(s);
    p.async = !0;
    p.src = 'https://' + (n ? 'pixel.biano.' + bianoDomain : 'bianopixel.com') +
      '/' + (a.debug ? 'debug' : 'min') + '/pixel.js';
    x = i.getElementsByTagName(s)[0];
    x.parentNode.insertBefore(p, x);
  }(window, document, bianoPixelConfig);
  {/literal}

</script>
<!-- End Biano Pixel Code -->

