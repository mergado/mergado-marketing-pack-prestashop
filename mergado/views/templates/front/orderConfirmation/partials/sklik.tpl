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

{if $sklikData['active'] === '1' && $sklikData['conversionCode'] && $sklikData['conversionCode'] !== ''}
    <!-- Měřicí kód Sklik.cz -->
    <script type="text/javascript">
        var seznam_cId = '{$sklikData['conversionCode']}';
        var seznam_value = {$sklikData['conversionValue']};
    </script>

    <script type="text/javascript" src="https://www.seznam.cz/rs/static/rc.js" async></script>
{/if}
