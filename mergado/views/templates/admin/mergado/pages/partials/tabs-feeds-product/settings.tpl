<div class="card full">
    <h1 class="mmp_h1">{l s='Global settings for all product feeds' mod='mergado'}</h1>

    {assign var=alertData value=['alertSection' => 'product', 'feedName' => 'product']}

    {include file='../components/alerts/settingsInfo.tpl'}

    <div class="mmp_settings">
        {$mmp['pageContent']['feeds-product']['settings']}
    </div>
</div>