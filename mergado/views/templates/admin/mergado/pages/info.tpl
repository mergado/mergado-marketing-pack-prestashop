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

<div class="mergado-page" data-page="info">
    <div id="mmpabout">
        <h3>{l s='CONNECT YOUR ONLINE STORE TO THE E-COMMERCE WORLD' mod='mergado'}</h3>

        <img src="{$mmp['url']['module']|escape:'htmlall':'UTF-8'}views/img/info.svg" alt="">

        <div class="mmpabout__content">
            <div class="mmpabout__left">
                <p>{l s='Try MERGADO Editor' mod='mergado'}</p>
                <p><strong>{l s='30 days FREE trial' mod='mergado'}</strong></p>
                <a class="mmpabout__more" target="_blank" href="https://www.mergado.com/mergado-smart-product-feed-manager">{l s='Learn more' mod='mergado'}</a>

                <div class="mmpabout__btnHolder">
                    <a class="mmpabout__btn" href="{$mmp['menu']['left']['feeds-product']['link']}">
                        <svg class="iconsMenu">
                            <use xlink:href="{$mmp['images']['baseImageUrl']}product"></use>
                        </svg>

                        {l s='Start creating feeds' mod='mergado'}</a>
                </div>
            </div>
            <div class="mmpabout__right">
                <p>{l s='Implement Advertising services' mod='mergado'}</p>
                <p>{l s='Into your website' mod='mergado'}</p>
                <div class="mmpabout__spacer"></div>

                <div class="mmpabout__btnHolder">
                    <a class="mmpabout__btn" href="{$mmp['menu']['left']['adsys']['link']}">
                        <svg class="iconsMenu">
                            <use xlink:href="{$mmp['images']['baseImageUrl']}elements"></use>
                        </svg>

                        {l s='Implement ad systems' mod='mergado'}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
