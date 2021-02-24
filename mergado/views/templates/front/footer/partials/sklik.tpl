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

{if $seznam_retargeting_id != ''}
    <script text='text/javascript'>
        /* <![CDATA[ */
        var seznam_retargeting_id = {$seznam_retargeting_id};
        /* ]]> */
    </script>
    <script type='text/javascript' src='//c.imedia.cz/js/retargeting.js'></script>
{/if}