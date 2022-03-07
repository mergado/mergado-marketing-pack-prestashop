<div class="mergado-page">
    <div class="rowmer">
        <div class="col-content">
            {foreach $mmp['pageContent']['news']['news'] as $item}
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

            {if $mmp['pageContent']['news']['news'] == []}
                <div class="mergado_card">
                    <div class="mergado_card__header mergado_card__header--none">
                        <h3 class="mergado_card__title mergado_card__title--none">{l s='No new messages' mod='mergado'}</h3>
                    </div>
                </div>
            {/if}
        </div>
        <div class="col-side">
            {if isset($mmp['pageContent']['ads']['side'])}
                {$mmp['pageContent']['ads']['side']}
            {/if}
        </div>
    </div>
    <div class="merwide">
        {if isset($mmp['pageContent']['ads']['wide'])}
            {$mmp['pageContent']['ads']['wide']}
        {/if}
    </div>
</div>