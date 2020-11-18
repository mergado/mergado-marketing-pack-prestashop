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
{if in_array($langCode, $bianoLangOptions)}
    <!-- Biano Pixel Code -->
    <script>
        var merchantId = '{$merchantId}';
        {literal}
        !function(b,i,a,n,o,p,x)
                {if(b.bianoTrack)return;o=b.bianoTrack=function(){o.callMethod?
                o.callMethod.apply(o,arguments):o.queue.push(arguments)};
                o.push=o;o.queue=[];p=i.createElement(a);p.async=!0;p.src=n;
                x=i.getElementsByTagName(a)[0];x.parentNode.insertBefore(p,x)
        {/literal}{literal}// }(window,document,'script','https://pixel.biano.{/literal}{strtolower($langCode)}{literal}/min/pixel.js');
                }{/literal}(window,document,'script','https://pixel.biano.{strtolower($langCode)}/debug/pixel.js'); // Debug
        bianoTrack('init', merchantId);
    </script>
    <!-- End Biano Pixel Code -->
{/if}