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

<script type="text/javascript">
    var heureka_widget_enable_mobile = {(trim($showMobile) === '') ? 0 : $showMobile};
    var heureka_widget_hide_width = {(trim($minWidth) === '') ? 0 : $minWidth};
    var heureka_widget_active = true;

    var widgetId = '{$widgetId}';
    var marginTop = '{(trim($marginTop) === '') ? 60 : $marginTop}';
    var position = '{(trim($position) === '') ? 21 : $position}';
    //<![CDATA[
    var _hwq = _hwq || [];
    _hwq.push(['setKey', widgetId]);
    _hwq.push(['setTopPos', marginTop]);
    _hwq.push(['showWidget', position]);
    (function () {
        var ho = document.createElement('script');
        ho.type = 'text/javascript';
        ho.async = true;
        ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.heureka.{$langIso}/direct/i/gjs.php?n=wdgt&sak=' + widgetId;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ho, s);
    })();
    //]]>
</script>
