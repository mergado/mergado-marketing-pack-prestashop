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

        $('#mergadoController [data-tab-link]').on('click', function (e) {
            e.preventDefault();
            var tabId = $(this).attr('data-tab-link');
            var tmpUrl = removeURLParameter(window.location.href, 'mergadoTab') + '&mergadoTab=' + tabId;

            if (!doNotRedirect) {
                doNotRedirect = false;
                window.location.href = tmpUrl;
            }
            return false;
        });
    });
</script>

<div id="mmpheader">
    <div class="mmp-header-top">
        <h1>
            <a href="https://pack.mergado.{$domain_type|strtolower}/?utm_source=mp&utm_medium=logo&utm_campaign=mergado_pack">
                <img class="header-logo-mmp" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/mergado_pack_logo_white.svg" alt="Mergado marketing pack" />
            </a>
        </h1>

        <ul class="menu">
            <li>
                <a class="" href="https://mergado.{$domain_type|strtolower}/?utm_source=mp&utm_medium=logo&utm_campaign=mergado" title="{l s='Mergado' mod='mergado'}" target="_blank">
                    <img class="header-logo-mergado" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/mergado_logo.png" alt="{l s='Mergado Marketing Pack' mod='mergado'}" />
                </a>
            </li>
            <li>
                <a class="btn btn-header-primary" href="https://accounts.mergado.com/register/?utm_source=mp&utm_medium=button&utm_campaign=register" title="{l s='New account' mod='mergado'}" target="_blank">
                    {l s='New account' mod='mergado'}
                </a>
            </li>
            <li>
                <a class="btn btn-header-secondary" href="https://accounts.mergado.com/login/?utm_source=mp&utm_medium=button&utm_campaign=login" title="{l s='Sign In' mod='mergado'}" target="_blank">
                    {l s='Sign In' mod='mergado'}
                </a>
            </li>
        </ul>
    </div>
    <div class="mmp-header-bot" style="display: none;">
        <h2>{l s='Connect your ecommerce to the online marketing world.' mod='mergado'}</h2>
        <ul class="mmp-nav-links tabControl">
            <li><a href="#" data-tab="0" style="display: none;"></a></li>
            <li><a href="#" data-tab="1">{l s='Settings' mod='mergado'}</a></li>
            <li><a href="#" data-tab="2">{l s='Cron tasks' mod='mergado'}</a></li>
            <li><a href="#" data-tab="3">{l s='XML feeds' mod='mergado'}</a></li>
            <li><a href="#" data-tab="6">{l s='Advertising systems' mod='mergado'}</a></li>
            <li><a href="#" data-tab="4">{l s='Support' mod='mergado'}</a></li>
            <li><a href="#" data-tab="7">{l s='News' mod='mergado'}</a></li>
            <li><a href="#" data-tab="5">{l s='Licence' mod='mergado'}</a></li>
        </ul>
    </div>
</div>


{*{if ($moduleVersion < $remoteVersion) && (phpversion() > $phpMinVersion) && $isPs16}*}
{*    <div class="alert alert-warning">*}
{*        {l s='New version of module is available' mod='mergado'} -*}
{*        <form method="post" class="text-link">*}
{*            <input type="submit" class="text-warning mergado" name="upgradeModule" value="{l s='Upgrade' mod='mergado'} {$remoteVersion}" />*}
{*        </form>*}
{*    </div>*}
{*{/if}*}

{if $unreadedTopNews}
    {foreach $unreadedTopNews as $item}
        <div class="alert alert-info">
            <strong>
                <a href="#" class="text-info mergado" data-tab-link="7">
                    {$item['title']}
                </a>
            </strong>
        </div>
    {/foreach}
{/if}

{if $unreadedNews && ($cookieNews <= $now)}
    <div class="panel">
        <div class="mergado-updated-notice news">

            <div class="mmp-news__holder">
                {if $unreadedUpdates}
                    <div style="border-right: 1px solid #ddd; padding-right: 20px; margin-right: 20px;">
                        {foreach from=$unreadedUpdates item=item}
                            <a href="#" data-tab-link="7" class="mergado-link mmp-news__item">
                                <p class="mmp-news__title">{$item['title']}</p>
                                <p><span class="mmp-badge mmp-badge--{$item['category']}">{$item['category']}</span> <span class="mmp-news__date">
                                {$item['pubDate']|date_format:$formattedDate}
                            </span></p>
                            </a>
                        {/foreach}
                    </div>
                {/if}
                <div>
                {foreach from=$unreadedNews item=item}
                    <a href="#" data-tab-link="7" class="mergado-link mmp-news__item">
                        <p class="mmp-news__title">{$item['title']}</p>
                        <p><span class="mmp-badge mmp-badge--{$item['category']}">{$item['category']}</span> <span class="mmp-news__date">
                        {$item['pubDate']|date_format:$formattedDate}
                    </span></p>
                    </a>
                {/foreach}
                </div>
            </div>

            <span data-cookie="mmp-cookie-news" class="mmp-cross mmp-close-cross">🞩</span>
        </div>
    </div>
{/if}

<!-- MMP POPUP -->

<div class="mmp-popup" data-500="{l s='Error occurred during generating feed. Please contact our support.' mod='mergado'}">
    <div class="mmp-popup__background">
        <div class="mmp-popup__box">
            <div class="mmp-popup__box_top">
                <span class="mmp-popup__title">{l s='Feed generation' mod='mergado'}</span>
            </div>
            <div class="mmp-popup__box_body">
                <div class="mmp-popup__content">
                    <div class="mmp-popup__loader">
                        <div class="sk-chase">
                            <div class="sk-chase-dot"></div>
                            <div class="sk-chase-dot"></div>
                            <div class="sk-chase-dot"></div>
                            <div class="sk-chase-dot"></div>
                            <div class="sk-chase-dot"></div>
                            <div class="sk-chase-dot"></div>
                        </div>
                        <div>{l s='Generating feed. Please wait...' mod='mergado'}</div>
                    </div>
                    <p class="mmp-popup__output"></p>
                </div>
            </div>
            <div class="mmp-popup__box_foot">
                <a href="#" class="mmp-popup__button btn btn-sm btn-success">{l s='Close' mod='mergado'}</a>
            </div>
        </div>
    </div>
</div>