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

{if $conversionZboziActive == '1'}
    <script>
        var conversionOrderId = '{$conversionOrderId}';
        var conversionZboziShopId = '{$conversionZboziShopId}';
        var useSandbox = {$useSandbox};

        // Set cookie to prevent sending same order for zbozi.cz multiple times
        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = getCookieName(conversionZboziShopId, conversionOrderId) + "=" + cname + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        function getCookieName(cshop, cid) {
            return "_" + cshop + "_" + cid;
        }

    </script>

    {if $conversionZboziAdvancedActive == '1'}
        <script>
            {literal}
            if (getCookie(getCookieName(conversionZboziShopId, conversionOrderId)) === "") {
                (function (w, d, s, u, n, k, c, t) {
                    w.ZboziConversionObject = n;
                    w[n] = w[n] || function () {
                        (w[n].q = w[n].q || []).push(arguments)
                    };
                    w[n].key = k;
                    c = d.createElement(s);
                    t = d.getElementsByTagName(s)[0];
                    c.async = 1;
                    c.src = u;
                    t.parentNode.insertBefore(c, t)
                })
                (window, document, "script", "https://www.zbozi.cz/conversion/js/conv-v3.js", "zbozi", conversionZboziShopId);

                if(useSandbox) {
                    zbozi("useSandbox");
                }

                zbozi("setOrder", {
                    "orderId": conversionOrderId,
                });

                zbozi("send");
                setCookie(conversionOrderId, conversionZboziShopId, 15);
            }
            {/literal}
        </script>
    {else}
        <script>
            {literal}
            if (getCookie(getCookieName(conversionZboziShopId, conversionOrderId)) === "") {
                (function (w, d, s, u, n, k, c, t) {
                    w.ZboziConversionObject = n;
                    w[n] = w[n] || function () {
                        (w[n].q = w[n].q || []).push(arguments)
                    };
                    w[n].key = k;
                    c = d.createElement(s);
                    t = d.getElementsByTagName(s)[0];
                    c.async = 1;
                    c.src = u;
                    t.parentNode.insertBefore(c, t)
                })(window, document, "script", "https://www.zbozi.cz/conversion/js/conv.js", "zbozi", conversionZboziShopId);

                if(useSandbox) {
                    zbozi("useSandbox");
                }

                zbozi("setOrder", {
                    "orderId": conversionOrderId
                });

                zbozi("send");
                setCookie(conversionOrderId, getCookieName(conversionZboziShopId, conversionOrderId), 15);
            }

            {/literal}
        </script>
    {/if}
{/if}