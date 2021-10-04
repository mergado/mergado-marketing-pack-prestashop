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
    //Dont call on product page (product view triggered there)
        if ($('body#product').length == 0) {
            {literal}
                bianoTrack('track', 'page_view');
            {/literal}
        }
    });
</script>