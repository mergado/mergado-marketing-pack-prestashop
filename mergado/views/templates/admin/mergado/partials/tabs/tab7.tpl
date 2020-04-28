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

<div class="mergado-tab" data-tab="7">
    <div class="rowmer">
        <div class="col-content">
            {foreach $tab7 as $item}
                <div class="mergado_card {$item['category']}">
                    <div class="mergado_card__header">
                        <h3 class="mergado_card__title">{$item['title']}</h3>
                        <p class="mergado_card__date">{$item['pubDate']}</p>
                    </div>
                    <div class="mergado_card__body">
                        <p class="mergado_card__description">
                            {$item['description']}
                        </p>
                    </div>
                </div>
            {/foreach}

            {if $tab7 == []}
                <div class="mergado_card">
                    <div class="mergado_card__header mergado_card__header--none">
                        <h3 class="mergado_card__title mergado_card__title--none">{$noMessages}</h3>
                    </div>
                </div>
            {/if}
        </div>
        <div class="col-side">
            {$sideAd}
        </div>
    </div>
</div>