<p style="margin-bottom: 20px;">{l s='Every action is logged into logs.' mod='mergado'}</p>

<h4>{l s='Include when contacting support:' mod='mergado'}</h4>
<table class="wp-list-table widefat striped">
    <thead>
    <tr>
        <th>{l s='Report item' mod='mergado'}</th>
        <th>{l s='Value' mod='mergado'}</th>
    </tr>
    </thead>

    <tbody>
    {foreach $mmp['pageContent']['support']['data']['default']['base'] as $item}
        <tr>
            <td>{$item['name']}</td>
            <td>{$item['value']}</td>
        </tr>
    {/foreach}
    </tbody>
</table>

<div class="mmp_logs__buttons">
    <a class="mmp_feedBox__button mmp_btn__blue mmp_btn__blue--small mmp_feedBox__copy priceImport__copy"
       data-copy-stash="{htmlspecialchars($mmp['pageContent']['support']['data']['json'])}"
       href="javascript:void(0);">

        <svg class="mmp_icon">
            <use xlink:href="{$mmp['images']['baseImageUrl']}copy"></use>
        </svg>
        {l s='Copy info to clipboard' mod='mergado'}
    </a>
</div>
