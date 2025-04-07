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
    $('#mergadoController .pageControl a').on('click', function (e) {
      e.preventDefault();
      var tmpUrl = $(this).attr('href');

      if (!doNotRedirect) {
        doNotRedirect = false;
        window.location.href = tmpUrl;
      }
      return false;
    });

    $('#mergadoController [data-page-link]').on('click', function (e) {
      e.preventDefault();
      var tmpUrl = $(this).attr('href');

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
            <a href="https://pack.mergado.{$mmp['domains']['pack']|strtolower}/?utm_source=mp&utm_medium=logo&utm_campaign=mergado_pack">
                <img class="header-logo-mmp"
                     src="{$mmp['url']['module']|escape:'htmlall':'UTF-8'}/views/img/mergado_pack_logo_white.svg"
                     alt="Mergado marketing pack"/>
            </a>
        </h1>

        <ul class="menu">
            <li>
                <a class=""
                   href="https://mergado.{$mmp['domains']['mergado']|strtolower}/?utm_source=mp&utm_medium=logo&utm_campaign=mergado"
                   title="{l s='Mergado' mod='mergado'}" target="_blank">
                    <img class="header-logo-mergado"
                         src="{$mmp['url']['module']|escape:'htmlall':'UTF-8'}/views/img/mergado_logo.png"
                         alt="{l s='Mergado Marketing Pack' mod='mergado'}"/>
                </a>
            </li>
            <li>
                <a class="btn btn-header-primary"
                   href="https://accounts.mergado.com/register/?utm_source=mp&utm_medium=button&utm_campaign=register"
                   title="{l s='New account' mod='mergado'}" target="_blank">
                    {l s='New account' mod='mergado'}
                </a>
            </li>
            <li>
                <a class="btn btn-header-secondary"
                   href="https://www.mergado.com/mergado-smart-product-feed-manager/?utm_source=mp&utm_medium=button&utm_campaign=visit_web"
                   title="{l s='Visit website' mod='mergado'}" target="_blank">
                    {l s='Visit website' mod='mergado'}
                </a>
            </li>
        </ul>
    </div>

    <div class="mmp-header-bot">
        <h2>
            {l s='Connect your e-commerce to MERGADO Multichannel Marketing' mod='mergado'}
        </h2>

        <div class="mmp-nav-links">
            <ul class="menu-nav-links--left pageControl">

                {foreach from=$mmp['menu']['left'] key=key item=item }
                    <li>
                        <a href="{$item['link']}" {if isset($smarty.get.page) && $smarty.get.page === $item['page']} class="active" {/if}>
                            {if $item['icon'] === 'other_feeds'}
                                <svg class="iconsMenu" viewBox="0 0 69 57">
                                    <path d="M28.676,17.164l-0.009,-3.662c-0.002,-0.894 0.915,-1.498 1.74,-1.147l16.604,7.075c0.458,0.195 0.757,0.644 0.758,1.142l0.043,18.18c0.002,0.893 -0.915,1.498 -1.74,1.146l-3.355,-1.429l0,-6.113c-0.158,-7.202 0.448,-8.605 -2.247,-9.827c-12.074,-5.478 -11.794,-5.365 -11.794,-5.365Z"/>
                                    <path d="M67.373,12.165c0.658,-0 1.25,0.527 1.25,1.244l0.044,18.176c0,0.497 -0.295,0.948 -0.753,1.145l-16.57,7.154c-0.163,0.07 -0.33,0.103 -0.494,0.104c-0.658,-0 -1.25,-0.528 -1.25,-1.244l-0.044,-18.18c-0.004,-0.497 0.292,-0.947 0.75,-1.145l16.57,-7.153c0.163,-0.07 0.33,-0.104 0.494,-0.105l0.003,0.004Z"/>
                                    <path d="M48.541,0c0.173,0 0.346,0.035 0.507,0.106l17.469,7.674c0.99,0.437 0.994,1.837 0.006,2.277l-17.433,7.758c-0.16,0.072 -0.334,0.108 -0.506,0.11c-0.173,-0 -0.346,-0.036 -0.507,-0.107l-17.47,-7.676c-0.99,-0.435 -0.993,-1.836 -0.005,-2.276l17.433,-7.758c0.16,-0.072 0.334,-0.108 0.506,-0.108Z"/>
                                    <path d="M0.043,48.637l-0.043,-18.18c-0.002,-0.894 0.915,-1.498 1.74,-1.147l16.604,7.075c0.458,0.195 0.757,0.644 0.758,1.142l0.043,18.18c0.002,0.893 -0.915,1.498 -1.74,1.146l-16.605,-7.074c-0.458,-0.196 -0.756,-0.644 -0.757,-1.142Zm38.663,-19.517c0.658,0 1.25,0.527 1.25,1.244l0.044,18.176c0,0.497 -0.295,0.948 -0.753,1.145l-16.57,7.154c-0.163,0.07 -0.33,0.103 -0.494,0.104c-0.658,0 -1.25,-0.528 -1.25,-1.244l-0.044,-18.18c-0.004,-0.497 0.292,-0.947 0.75,-1.145l16.57,-7.153c0.163,-0.07 0.33,-0.104 0.494,-0.105l0.003,0.004Zm-18.832,-12.165c0.173,0 0.346,0.035 0.507,0.106l17.469,7.674c0.99,0.437 0.994,1.837 0.006,2.277l-17.433,7.758c-0.16,0.072 -0.334,0.108 -0.506,0.11c-0.173,0 -0.346,-0.036 -0.507,-0.107l-17.47,-7.676c-0.99,-0.435 -0.993,-1.836 -0.005,-2.276l17.433,-7.758c0.16,-0.072 0.334,-0.108 0.506,-0.108Z"
                                          style="fill-rule:nonzero;"/>
                                </svg>
                            {else}
                                <svg class="iconsMenu">
                                {if isset($item['file'])}
                                    <use xlink:href="{$mmp['images'][$item['file']]}{$item['icon']}"></use>
                                {else}
                                    <use xlink:href="{$mmp['images']['baseImageUrl']}{$item['icon']}"></use>
                                {/if}
                                </svg>
                            {/if}

                            {$item['text']}
                        </a>
                    </li>
                {/foreach}
            </ul>

            <ul class="menu-nav-links--right">
                {foreach from=$mmp['menu']['right'] key=key item=item }
                    <li>
                        <a href="{$item['link']}" {if isset($smarty.get.page) && $smarty.get.page === $item['page']} class="active" {/if}>
                            <svg class="iconsMenu">
                                {if isset($item['file'])}
                                    <use xlink:href="{$mmp['images'][$item['file']]}{$item['icon']}"></use>
                                {else}
                                    <use xlink:href="{$mmp['images']['baseImageUrl']}{$item['icon']}"></use>
                                {/if}
                            </svg>

                            {$item['text']}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>

        {*        {if $mmp['news']['unreadTopNews'}*}
        {*            {foreach $mmp['news']['unreadTopNews'] as $item}*}
        {*                <div class="alert alert-info">*}
        {*                    <strong>*}
        {*                        <a href="#" class="text-info mergado" data-page-link="7">*}
        {*                            {$item['title']}*}
        {*                        </a>*}
        {*                    </strong>*}
        {*                </div>*}
        {*            {/foreach}*}
        {*        {/if}*}
    </div>
</div>

{*     NEWS     *}
{if isset($smarty.get.page) && $smarty.get.page !== 'info' && $smarty.get.page !== 'news' && $smarty.get.page}
    {if $mmp['news']['unreadNews'] && !$mmp['hideNews']}
        <div class="panel">
            <div class="mergado-updated-notice news">

                <div class="mmp-news__holder">
                    {if $mmp['news']['unreadUpdates']}
                        <div style="border-right: 1px solid #ddd; padding-right: 20px; margin-right: 20px;">
                            {foreach from=$mmp['news']['unreadUpdates'] item=item}
                                <div class="mmp-news__item mmp-news__itemUpdate">
                                    <div class="mmp-news__itemUpdateContent">
                                        <a href="#" data-page-link="7" class="mergado-link">
                                            <p class="mmp-news__title">{$item['title']}</p>
                                        </a>
                                            <p>
                                                <span class="mmp-badge mmp-badge--{$item['category']}">{$item['category']}</span>
                                                <span class="mmp-news__date">
                                {$item['pubDate']|date_format:$mmp['formatting']['date']}
                            </span></p>
                                    </div>

                                        {if $item['link'] && $item['link'] !== ''}
                                                <div class="mmp-news__linkContainer">
                                                    <a class="mmp-news__link--update" href="{$item['link']}" target="_blank">
                                                        {l s='Download latest version' mod='mergado'}</a>
                                                </div>
                                        {/if}
                                    </a>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                    <div>
                        {foreach from=$mmp['news']['unreadNews'] item=item}
                            <div class="mmp-news__item">
                                <a href="#" data-page-link="7" class="mergado-link">
                                    <p class="mmp-news__title">{$item['title']}</p>
                                </a>
                                    <p><span class="mmp-badge mmp-badge--{$item['category']}">{$item['category']}</span>
                                        <span class="mmp-news__date">
                    {$item['pubDate']|date_format:$mmp['formatting']['date']}
                </span></p>

                                    {if $item['link'] && $item['link'] !== ''}
                                            <div class="mmp-news__linkContainer">
                                                <a class="mmp-news__link--common" href="{$item['link']}" target="_blank">{l s='Continue reading...' mod='mergado'}</a>
                                            </div>
                                    {/if}
                            </div>
                        {/foreach}
                    </div>
                </div>

                <span data-cookie="mmp-cookie-news" class="mmp-cross mmp-close-cross">🞩</span>
            </div>
        </div>
    {/if}
{/if}
