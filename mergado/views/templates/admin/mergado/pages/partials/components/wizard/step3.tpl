{if (($isAlreadyFinished && !$wizardStep) || ($wizardStep == 3 && $wizardForced)) && $wizardFeed === $wizardData['feedName']}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
          var $ = jQuery;


          if ($('[data-mmp-tab-button="{$wizardType}"]').closest('li').hasClass('active')) {
              window.mmpWizard.startFeedGenerating('{$wizardData['feedName']}');
          }
        });
    </script>
{/if}


{if ($wizardFeed === $wizardData['feedName'] && $wizardStep === '3') || ($wizardFeed === $wizardData['feedName'] && $isAlreadyFinished && !$wizardStep)}
    <div class="mmp_wizard active" data-mmp-wizard-step="3" data-mmp-wizard-type="{$wizardData['feedName']}">
{else}
    <div class="mmp_wizard" data-mmp-wizard-step="3" data-mmp-wizard-type="{$wizardData['feedName']}">
{/if}
        <div class="card full">
            <div class="mmp_wizard__content">

                {if ($wizardForced)}
                    <h1 class="mmp_wizard__heading" data-feed-finished="false">{l s='Wait until your %s feed is created' sprintf=$wizardData['feedFullName'] mod='mergado'}</h1>
                    <h1 class="mmp_wizard__heading" data-feed-finished="true">{l s='Your %s feed is ready' sprintf=$wizardData['feedFullName'] mod='mergado'}</h1>
                {else}
                    <h1 class="mmp_wizard__heading" data-feed-finished="false">{l s='Wait until your first %s feed is created' sprintf=$wizardData['feedFullName'] mod='mergado'}</h1>
                    <h1 class="mmp_wizard__heading" data-feed-finished="true">{l s='Your first %s feed is ready. Now please continue to the Cron settings to set up automatic feed updates.' sprintf=$wizardData['feedFullName'] mod='mergado'}</h1>
                {/if}

                {assign var=alertData value=['alertSection' => $wizardData['alertSection'], 'feedName' => $wizardData['feedName']]}

                <div data-feed-finished="false">
                    {include file='../alerts/longTime.tpl'}
                </div>

                <div data-feed-finished="true">
{*                    {include file='../alerts/feedIsReady.tpl'}*}
                </div>

                <div class="mmp_wizard__content">
                    <div class="mmp_wizard__content_body">
                        <div class="mmp_wizard__generate">
                            <form>
                                <div class="mmp_wizard__generating" data-status="inactive">
                                    <div class="mmp_wizard__generating_status">

                                    </div>

                                    <div style="position: relative;"
                                         class="rangeSlider rangeSlider-{$wizardData['feedName']}"
                                         data-range-index="{$wizardData['feedName']}">
                                        <span class="rangeSliderPercentage"
                                        {if ($wizardData['percentage'] > 52)}
                                            style="color: white;"
                                        {/if}
                                        >{$wizardData['percentage'] }%</span>
                                        <span class="rangeSliderBg" style="width: {$wizardData['percentage']}%;"></span>
                                    </div>

                                    <svg class="mmp_wizard__generating_svg">
                                        <use xlink:href="{$mmp['images']['baseImageUrl']}refresh"></use>
                                    </svg>

                                    <svg class="mmp_wizard__generating_done_svg">
                                        <use xlink:href="{$mmp['images']['baseImageUrl']}check-inv"></use>
                                    </svg>
                                </div>

                                <input type="hidden" name="feed" value="{$wizardData['cronAction']}"/>
                                <input type="hidden" name="feedName" value="{$wizardData['feedName']}"/>
                                <input type="hidden" name="action"
                                       value="{$wizardData['ajaxGenerateAction']}"/>
                            </form>
                        </div>

                    </div>

                    {if $wizardForced}
                        <div class="mmp_wizard__buttons mmp_justify_end">
                            {if $wizardStep === '3'}
                                <div></div>
                            {/if}

                            <a href="javascript:void(0);" class="mmp_btn__blue"
                               data-3-generate="start"
                               data-mmp-wizard-go="3"
                               data-mmp-wizard-do-before="generateMulti"
                               data-go-to-link="{$wizardData['feedListLink']}">{l s='Start feed generation' mod='mergado'}</a>
                            <a href="javascript:void(0);" class="mmp_btn__blue" data-3-generate="skip"
                               style="display: none;" data-mmp-wizard-go="4"
                               data-mmp-wizard-do-before="mmpSkipWizard"
                               data-go-to-link="{$wizardData['feedListLink']}">{l s='Skip to list of feeds' mod='mergado'}</a>
                            <a href="javascript:void(0);" data-go-to-link="{$wizardData['feedListLinkWithCongratulations']}" class="mmp_btn__blue" style="display: none;"
                               data-3-generate="done"
                               data-mmp-wizard-go="3"
                               data-mmp-wizard-do-before="mmpGoToLink"
                            >
                                {l s='Continue to list of feeds' mod='mergado'}</a>
                        </div>
                    {else}
                        <div class="mmp_wizard__buttons mmp_justify_end">
                            {if !$isAlreadyFinished}
                            <a href="javascript:void(0);" class="mmp_btn__white" data-mmp-wizard-go="2"
                               data-mmp-wizard-do-before="mmpStopProgress">{l s='Back' mod='mergado'}</a>
                            {else}
                                <div></div>
                            {/if}
                            <a href="javascript:void(0);" class="mmp_btn__blue"
                               data-mmp-wizard-go="4"
                               data-mmp-wizard-do-before="mmpSkipWizard"
                               data-3-generate="skip">{l s='Skip to cron settings' mod='mergado'}</a>
                            <a href="javascript:void(0);" class="mmp_btn__blue" style="display: none;"
                               data-mmp-wizard-do-before="setWizardCompleted"
                               data-mmp-wizard-go="4"
                               data-3-generate="done">
                                {l s='Continue to cron settings' mod='mergado'}</a>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
