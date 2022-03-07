{if ($wizardFeed === $wizardName && $wizardStep === '4') || ($wizardStep == 4 && $wizardForced) && $wizardFeed === $wizardData['feedName']}
    <div class="mmp_wizard active" data-mmp-wizard-step="4" data-mmp-wizard-type="{$wizardData['feedName']}">
{else}
    <div class="mmp_wizard" data-mmp-wizard-step="4" data-mmp-wizard-type="{$wizardData['feedName']}">
{/if}
    <div class="card full">
        <div class="mmp_wizard__content">
            <h1 class="mmp_wizard__heading">{l s='Dont\'t forget to set up your task scheduler - CRON' mod='mergado'}</h1>

            {assign var=alertData value=['alertSection' => $wizardData['alertSection'], 'feedName' => $wizardData['feedName']]}
            {include file="../alerts/cronInfo.tpl"}

            <div class="mmp_wizard__content_body">
                <div class="mmp_wizard__content_heading">
                    <span>{l s='How to set up your task scheduler for automatic feed updates' mod='mergado'}</span>
                    <a href="{l s='https://pack.mergado.com/support#Export%20XML%20feeds' mod='mergado'}" target="_blank">{l s='Read more on our Support page.' mod='mergado'}</a>
                </div>

                <div class="mmp_wizard__cron">
                    <div class="mmp_wizard__cronItem mmp_wizard__cronItem--first">
                        <h2 class="mmp_wizard__cronItemTop">
                            {l s='Open your task scheduler - Webcron' mod='mergado'}
                            <svg>
                                <use xlink:href="{$mmp['images']['baseImageUrl']}open"></use>
                            </svg>
                        </h2>
                        <p>{l s='Usually cron service is available as part of hosting or you can use an external service.' mod='mergado'}</p>
                    </div>

                    <div class="mmp_wizard__cronArrow">
                        <svg>
                            <use xlink:href="{$mmp['images']['baseMmpImageUrl']}arrow-right"></use>
                        </svg>
                    </div>

                    <div class="mmp_wizard__cronItem mmp_wizard__cronItem--second">
                        <h2 class="mmp_wizard__cronItemTop">
                            {l s='Enter the cron URL and set the time' mod='mergado'}
                            <svg>
                                <use xlink:href="{$mmp['images']['baseImageUrl']}in-progress"></use>
                            </svg>
                        </h2>
                        <p>{l s='Cron will automatically  call the URL at the intervals you specify (eg every hour).' mod='mergado'}</p>
                    </div>

                    <div class="mmp_wizard__cronArrow">
                        <svg>
                            <use xlink:href="{$mmp['images']['baseMmpImageUrl']}arrow-right"></use>
                        </svg>
                    </div>

                    <div class="mmp_wizard__cronItem mmp_wizard__cronItem--third">
                        <div>
                            <h2 class="mmp_wizard__cronItemTop">
                                {l s='The feed will update automatically' mod='mergado'}
                                <svg>
                                    <use xlink:href="{$mmp['images']['baseImageUrl']}refresh"></use>
                                </svg>
                            </h2>
                            <p>{l s='Each time cron calls a cron URL, feed generation is started. This will keep your XML feed up to date.' mod='mergado'}</p>
                        </div>
                    </div>
                </div>

                <div class="mmp_wizard__cronLink">
                    <div class="mmp_wizard__cron_left">
                        <div class="mmp_wizard__cronLink_name">{l s='Cron URL' mod='mergado'}</div>
                        <input type="text" class="mmp_feedBox__url mmp_wizard__cronLink_link" readonly value="{$wizardData['cronUrl']}">
                    </div>
                    <div class="mmp_wizard__cronLink_copy"><a class="mmp_btn__blue--small" data-copy-stash="{$wizardData['cronUrl']}" href="javascript:void(0);">{l s='Copy cron URL' mod='mergado'}</a></div>
                </div>
            </div>

            {if $wizardForced}
                <div class="mmp_wizard__buttons mmp_justify_end">
                    <div></div>
                    <a href="{$wizardData['feedListLink']}" class="mmp_btn__blue">{l s='Go to list of feeds' mod='mergado'}</a>
                </div>
            {else}
                <div class="mmp_wizard__buttons">
                    <a href="javascript:void(0);" class="mmp_btn__white" data-mmp-wizard-go="3">{l s='Back' mod='mergado'}</a>
                    <a href="javascript:void(0);" class="mmp_btn__blue" data-mmp-wizard-go="4" data-mmp-wizard-do-before="setWizardCompletedAndGo" data-go-to-link="{$wizardData['feedListLinkWithCongratulations']}">{l s='Continue to list of feeds' mod='mergado'}</a>
                </div>
            {/if}
        </div>
    </div>
</div>

