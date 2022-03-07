{assign var="feedStatusClass" value="mmp_feedBox__feedStatus--`$feedBoxData['feedStatus']`"}
{assign var="alertData" value=['alertSection' => $feedBoxData['alertSection'], 'feedName' => $feedBoxData['feedName'], 'fullName' => $feedBoxData['feedFullName']]}

{* FEED - HAS NO ERRORS *}
{if $feedBoxData['feedBoxData']['errorCount'] === 0}
    {* WIZARD - COMPLETED *}
    {if $feedBoxData['wizardCompleted']}
        {* FEED - NOT YET FINISHED *}
        {if $feedBoxData['feedStatus'] === 'warning' && $feedBoxData['lastUpdate'] === false}
            {if $feedBoxData['feedName'] === 'stock'}
                {include file='../../components/alerts/congratulationWaitingStock.tpl'}
            {else}
                {include file='../../components/alerts/congratulationWaiting.tpl'}
            {/if}

        {* FEED - FINISHED *}
        {elseif $feedBoxData['feedExist'] && $feedBoxData['feedStatus'] === 'success'}

            {* FEED - IS 2+ NOTICE ABOUT FEED FINISHED *}
            {if $feedBoxData['showCongratulations']}
                {if $feedBoxData['feedName'] === 'stock'}
                    {include file='../../components/alerts/congratulationStock.tpl'}
                {else}
                    {include file='../../components/alerts/congratulation.tpl'}
                {/if}
            {/if}

        {/if}
    {/if}

{* FEED - HAS ERRORS *}
{else}

    {* FEED - ERROR DURING GENERATION *}
    {if $feedBoxData['feedBoxData']['errorDuringGeneration']}
        {if $feedBoxData['feedStatus'] === 'danger'}
            {include file='../../components/alerts/feedFailedBeforeFirstGeneration.tpl'}
        {else}
            {include file='../../components/alerts/feedFailed.tpl'}
        {/if}
    {/if}

    {* FEED - NOT UPDATED ERROR *}
    {if $feedBoxData['feedBoxData']['errorNotUpdated']}
        {include file='../../components/alerts/feedNotUpdated.tpl'}
    {/if}
{/if}

{if ( $feedBoxData['feedStatus'] === 'danger' )}
    <div class="mmp_feedBox">
        <div class="mmp_feedBox__top">
            <div class="mmp_feedBox__status">
                <div class="mmp_feedBox__feedStatus {$feedBoxData['feedBoxData']['statusClass']}"></div>
                {if isset($feedBoxData['feedFullName'])}
                    <div class="mmp_feedBox__feedName">
                        {$feedBoxData['feedFullName']}
                    </div>
                {/if}
                <p class="mmp_feedBox__date">{l s='Waiting for first generation' mod='mergado'}</p>
            </div>
            <div class="mmp_feedBox__actions">
                <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__createXmlFeed"
                   href="{$feedBoxData['wizardUrl']}">
                    {l s='Create xml feed' mod='mergado'}
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseMmpImageUrl']}plus"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>
{else}
    <div class="mmp_feedBox">
        <div class="mmp_feedBox__top">
            <div class="mmp_feedBox__status">
                <div class="mmp_feedBox__feedStatus {$feedBoxData['feedBoxData']['statusClass']}"></div>

                {if isset($feedBoxData['feedFullName'])}
                    <div class="mmp_feedBox__feedName">
                        {$feedBoxData['feedFullName']}
                    </div>
                {/if}

                {if $feedBoxData['feedStatus'] === 'success'}
                    <p class="mmp_feedBox__date">{l s='Last update: ' mod='mergado'} {$feedBoxData['lastUpdate']}</p>
                {elseif $feedBoxData['feedStatus'] === 'warning'}
                    <p class="mmp_feedBox__date">{$feedBoxData['percentageStep']} %
                        - {l s='Waiting for next cron start' mod='mergado'}</p>
                {/if}
            </div>
            <div class="mmp_feedBox__actions">
                {if $feedBoxData['feedStatus'] === 'warning' || $feedBoxData['errorDuringGeneration']}
                    <a class="mmp_feedBox__button mmp_feedBox__finishManually"
                    href="{$feedBoxData['generateUrl']}"
                    data-tippy-content="{l s='Manually finish feed creating.' mod='mergado'}"
                    >
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}turn-on"></use>
                    </svg>
                    {l s='Finish manually' mod='mergado'}
                    </a>
                {elseif $feedBoxData['feedStatus'] === 'success'}
                    <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__openXmlFeed"
                       data-tippy-content="{l s='Open XML feed in new window.' mod='mergado'}"
                       href="{$feedBoxData['feedUrl']}" target="_blank">
                        <svg class="mmp_icon">
                            <use xlink:href="{$mmp['images']['baseImageUrl']}open"></use>
                        </svg>
                    </a>
                {/if}

                {if $feedBoxData['createExportInMergadoUrl'] === false}
                    <a class="mmp_feedBox__button mmp_feedBox__copyUrl" href="javascript:void(0);" data-copy-stash='{$feedBoxData['feedUrl']}'
                    data-tippy-content="{l s='Copy feed URL address to clipboard.' mod='mergado'}"
                    >
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}copy"></use>
                    </svg>
                    {l s='Copy feed URL' mod='mergado'}
                    </a>
                {else}
                <a class="mmp_feedBox__export
                    {if $feedBoxData['feedStatus'] !== 'success'}
                        disabled" href="javascript:void(0);">
                    {else}
                    " target="_blank" href="{$feedBoxData['createExportInMergadoUrl']}"
                    data-tippy-content="{l s='Click to redirect to Mergado where you can start creating exports for hundereds of different channels. <br><br> Mergado App will open in a new window.' mod='mergado'}"
                    >
                    {/if}
                    <p class="mmp_feedBox__button mmp_feedBox__mergadoExport">{l s='Create export in Mergado' mod='mergado'}</p>
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}service-mergado"></use>
                    </svg>
                </a>
                {/if}
                <a class="mmp_feedBox__toggler" href="javascript:void(0);">
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseMmpImageUrl']}chevron-down"></use>
                    </svg>
                </a>
            </div>
        </div>
        <div class="mmp_feedBox__bottom">
            <div class="mmp_feedBox__line">
                <div class="mmp_feedBox__line--left">
                    <p class="mmp_feedBox__name">{l s='Feed URL' mod='mergado'}</p>
                    <input type="text" class="mmp_feedBox__url" readonly value="{$feedBoxData['feedUrl']}" />
                </div>
                <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__copy
                                {if $feedBoxData['feedStatus'] !== 'success'}
                                   disabled"
                {else}
                " data-copy-stash='{$feedBoxData['feedUrl']}'
                data-tippy-content="{l s='Copy feed URL address to clipboard.' mod='mergado'}"
                {/if}
                href="javascript:void(0);">

                <svg class="mmp_icon">
                    <use xlink:href="{$mmp['images']['baseImageUrl']}copy"></use>
                </svg>
                </a>
            </div>
            <div class="mmp_feedBox__line">
                <div class="mmp_feedBox__line--left">
                    <p class="mmp_feedBox__name">{l s='Cron URL' mod='mergado'}</p>
                    <input type="text" class="mmp_feedBox__url" readonly value="{$feedBoxData['cronGenerateUrl']}">
                </div>
                <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__copy
                        " data-copy-stash='{$feedBoxData['cronGenerateUrl']}'
                data-tippy-content="{l s='Copy cron URL address to clipboard.' mod='mergado'}"
                href="javascript:void(0);">

                <svg class="mmp_icon">
                    <use xlink:href="{$mmp['images']['baseImageUrl']}copy"></use>
                </svg>
                </a>
            </div>
            <div class="mmp_feedBox__actionsBottom">
                <a class="mmp_feedBox__button mmp_feedBox__cronSetup"
                   href="{$feedBoxData['cronSetUpUrl']}"
                   data-tippy-content="{l s='Schedule when and how often is your feed going to be updated.' mod='mergado'}"
                >
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}in-progress"></use>
                    </svg>
                    {l s='Cron set up' mod='mergado'}
                </a>
                <a class="mmp_feedBox__button mmp_feedBox__generate"
                   href="{$feedBoxData['generateUrl']}"
                   data-tippy-content="{l s='Manually start feed creating.' mod='mergado'}"
                >
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}turn-on"></use>
                    </svg>
                    {l s='Generate manually' mod='mergado'}</a>
                <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__download
                                {if $feedBoxData['feedStatus'] !== 'success'}
                                   disabled" href="javascript:void(0);">
                    {else}
                    " href="{$feedBoxData['downloadUrl']}"
                    data-tippy-content="{l s='Download the feed to your computer.' mod='mergado'}"
                    download
                    >
                    {/if}

                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}download"></use>
                    </svg>
                </a>
                <a class="mmp_feedBox__button mmp_feedBox__button--square mmp_feedBox__button--danger mmp_feedBox__delete
                        {if $feedBoxData['feedStatus'] === 'danger'}
                           disabled" href="javascript:void(0)">
                    {else}
                    " href="{$feedBoxData['deleteUrl']}"
                    data-tippy-content="{l s='Deletes the product feed and all links.' mod='mergado'}"
                    >
                    {/if}
                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}delete"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>
{/if}
