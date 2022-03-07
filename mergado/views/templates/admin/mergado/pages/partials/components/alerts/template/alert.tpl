{*    $alertDefaultData = [*}
{*        'type' => 'success',*}
{*        'text' => 'Some amazing text',*}
{*        'closable' => true,*}
{*        'closableAll' => true,*}
{*         'alertName' => 'congratulation'*}
{*        'alertSection' => 'product'*}
{*    ]*}

<div class="mmp_alert__wrapper mmp_alert__wrapper--{$alertDefaultData['type']}"
     data-mmp-alert='{['section' => $alertDefaultData['alertSection'], 'feed' => $alertData['feedName'], 'name' => $alertDefaultData['alertName']]|@json_encode}'>
    <div class="mmp_alert mmp_alert--{$alertDefaultData['type']}">
		{if $alertDefaultData['closable']}
            <a class="mmp_alert__closer" href="javascript:void(0);"
               data-mmp-hide-alert="{$alertDefaultData['alertName']}">✖</a>
		{/if}

        <svg class="mmp_alert__icon">
            {if $alertDefaultData['type'] === 'success'}
                <use xlink:href="{$mmp['images']['baseImageUrl']}check-inv"></use>
			{elseif $alertDefaultData['type'] === 'warning'}
                <use xlink:href="{$mmp['images']['baseImageUrl']}info"></use>
            {elseif $alertDefaultData['type'] === 'danger'}
                <use xlink:href="{$mmp['images']['baseImageUrl']}error"></use>
            {/if}
        </svg>

        <span>{$alertDefaultData['text']|unescape}</span>
    </div>

	{if $alertDefaultData['closableAll']}
        <div class="mmp_alert__closerFull">
            <a href="javascript:void(0);" data-mmp-disable-all-notifications="">
                {l s='Don\'t show me any more tips.' mod='mergado'}
            </a>
        </div>
    {/if}
</div>
