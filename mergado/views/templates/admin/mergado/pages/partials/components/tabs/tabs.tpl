<ul class="mmp_tabs mmp_tabs__menu">
    {foreach $mmpTabsSettings as $key => $tab }
        {if $tab['active']}
            <li class="active">
        {else}
            <li>
        {/if}
            {if isset($tab['icon']) && $tab['icon'] !== ''}
                <a href="#" data-mmp-tab-button="{$key}" class="hasIcon">
                    <svg>
                        <use xlink:href="{$mmp['images']['baseImageUrl']}{$tab['icon']}"></use>
                    </svg>
            {else}
                <a href="#" data-mmp-tab-button="{$key}">
            {/if}

                {if isset($tab['title']) && $tab['title'] !== ''}
                    <div class="mmp_tabs__title">
                        {$tab['title']}
                    </div>
                {/if}
            </a>
        </li>
    {/foreach}
</ul>

<div class="mmp_tabs mmp_tabs__content">
    {foreach $mmpTabsSettings as $key => $tab}
        {if $tab['active']}
            <div class="mmp_tabs__tab active" data-mmp-tab="{$key}">
        {else}
            <div class="mmp_tabs__tab" data-mmp-tab="{$key}">
        {/if}
            {include file=$tab['contentPath']}
        </div>
    {/foreach}
</div>
