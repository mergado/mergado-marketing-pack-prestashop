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
    var saveWarning = '{l s='Before you leave, save your changes or they will be lost. Continue?' mod='mergado'}';
    var checkChanges = false;
    var doNotRedirect = false;
    $(document).ready(function () {
        $('#mergadoController .tabControl a').on('click', function (e) {
            e.preventDefault();
            var tabId = $(this).attr('data-tab');
            var tmpUrl = removeURLParameter(window.location.href, 'mergadoTab') + '&mergadoTab=' + tabId;

            if (!doNotRedirect) {
                doNotRedirect = false;
                window.location.href = tmpUrl;
            }
            return false;
        });
    });
</script>
<div id="mergadoHeader">
    <div id='mergadoLogo'>
        <a href='http://www.mergado.cz' title='Mergado' target='_blank'>
            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/logo.png" alt="Mergado" />
        </a>
    </div>
    <div class="info">
        <h2>{l s='Earn more on price comparator sites' mod='mergado'}</h2>
        <p>
            {l s='We help to shop owners to get more from Heureka, Zbozi.cz and other price comparator sites. Follow 3 buttons below in this text to configure XML feeds, setup cron tasks and get your XML feeds for Mergado services.' mod='mergado'}
        </p>
    </div>
    <div class="tabControl">
        <a href="#" data-tab="1">{l s='Export configuration' mod='mergado'}</a>
        <a href="#" data-tab="6">{l s='Advertising systems' mod='mergado'}</a>
        <a href="#" data-tab="2">{l s='Cron tasks' mod='mergado'}</a>
        <a href="#" data-tab="3">{l s='XML feeds' mod='mergado'}</a>
        <a href="#" data-tab="4">{l s='Support' mod='mergado'}</a>
        <a href="#" data-tab="5">{l s='Licence' mod='mergado'}</a>
    </div>

    <a href="http://www.mergado.cz/audit-xml" title="{l s='Free audit' mod='mergado'}" target="_blank" id='mrmergado'>
        <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/mrmergado.png" alt="Mergado" />
    </a>
</div>
