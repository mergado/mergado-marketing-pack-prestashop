{if $etarget_id != ''}
    <script type="text/javascript">
        if (window.addEventListener) {
            window.addEventListener('load', loadRetarget{$etarget_id});
        } else if (window.attachEvent) {
            window.attachEvent('onload', loadRetarget{$etarget_id});
        }
        function loadRetarget{$etarget_id}() {
            var scr = document.createElement("script");
            scr.setAttribute("async", "true");
            scr.type = "text/javascript";
            scr.src = "//" + "cz.search.etargetnet.com/j/?h={$etarget_hash}";
            ((document.getElementsByTagName("head") || [null])[0] || document.getElementsByTagName("script")[0].parentNode).appendChild(scr);
        }
    </script>
{/if}