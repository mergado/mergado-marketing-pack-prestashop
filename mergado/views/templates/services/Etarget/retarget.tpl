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

{if $etargetData['id'] !== ''}
    <script type="text/javascript">
      if (window.addEventListener) {
        window.addEventListener('load', loadRetarget{$etargetData['id']});
      } else if (window.attachEvent) {
        window.attachEvent('onload', loadRetarget{$etargetData['id']});
      }

      function loadRetarget{$etargetData['id']}() {
        var scr = document.createElement("script");
        scr.setAttribute("async", "true");
        scr.type = "text/javascript";
        scr.src = "//" + "cz.search.etargetnet.com/j/?h={$etargetData['hash']}";
        ((document.getElementsByTagName("head") || [null])[0] || document.getElementsByTagName("script")[0].parentNode).appendChild(scr);
      }
    </script>
{/if}
