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
    <script type="text/javascript" src="https://c.seznam.cz/js/rc.js"></script>
    <script>
      var conversionConf = {
        id: {$sklikData['conversionCode']},
        value: {$sklikData['conversionValue']},
        consent: {$sklikConsent}
      };

      if (window.rc && window.rc.conversionHit) {
        window.rc.conversionHit(conversionConf);
      }
    </script>
{/if}
