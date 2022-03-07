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
    var moduleVersion = '{$mmp['version']['module']}';
    var remoteVersion = '{$mmp['version']['remote']}';
    var updateAvailable = '{l s='New version available' mod='mergado'}';
    $('.page-head .page-title').append(' v.' + moduleVersion + '');

    {if ($mmp['version']['module'] < $mmp['version']['remote']) && (phpversion() > $mmp['version']['phpMin'])}
    $('.page-head .page-title').append('<br><small>' + updateAvailable + ' ' + remoteVersion + '</small>');
    {/if}
</script>