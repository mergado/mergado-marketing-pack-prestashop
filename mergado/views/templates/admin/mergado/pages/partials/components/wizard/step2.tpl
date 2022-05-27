{if $wizardName !== 'static' && $wizardName !== 'stock'}
    <div class="mmp_wizard" data-mmp-wizard-step="2">
        <div class="card full">
            <div class="mmp_wizard__content">
                <h1 class="mmp_wizard__heading">{l s='Select which export you want to create' mod='mergado'}</h1>

                <div data-feed-finished="false">
                    {assign var=alertData value=['alertSection' => 'global', 'feedName' => 'global']}

                    {include file='../alerts/languageAndCurrency.tpl'}
                </div>

                <div class="mmp_wizard__radio">
                    {foreach from=$wizardData item=wizard name=feedTypeLoop}
                        <label for="{$wizard['feedName']}" class="mmp_radio">{$wizard['feedFullName']}

                            {if $smarty.foreach.feedTypeLoop.first}
                                <input type="radio" id="{$wizard['feedName']}" name="feed" value="{$wizard['feedName']}"
                                       checked>
                            {else}
                                <input type="radio" id="{$wizard['feedName']}" name="feed" value="{$wizard['feedName']}">
                            {/if}

                            <span class="mmp_radio__checkmark"></span>

                        </label>
                        <br>
                    {/foreach}
                </div>

                <div class="mmp_wizard__buttons mmp_justify_end">
                    <a href="javascript:void(0);" class="mmp_btn__white"
                       data-mmp-wizard-go="1">{l s='Back' mod='mergado'}</a>
                    <a href="javascript:void(0);" class="mmp_btn__blue"
                       data-mmp-wizard-do-before="setActiveAndGenerateMulti"
                       data-mmp-wizard-go="3">{l s='Generate feed' mod='mergado'}</a>
                </div>
            </div>
        </div>
    </div>
{/if}
