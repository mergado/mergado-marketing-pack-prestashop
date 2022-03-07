{assign var="mmpTabsSettings" value=$mmp['tabs']['feeds-other']}

<div class="rowmer">
    <div class="col-content">
        {include file='./partials/components/tabs/tabs.tpl'}
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