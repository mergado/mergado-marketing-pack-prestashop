{assign var=isAlreadyFinished value=$mmp['pageContent']['feeds-other']['category']['isAlreadyFinished']}

{if (isset($smarty.get['mmp-wizard']) && $smarty.get['mmp-wizard'] === 'category') || (!$isAlreadyFinished || (isset($smarty.get['step']) && $smarty.get['mmp-wizard'] === 'category'))}
    {assign var=wizardData value=$mmp['pageContent']['feeds-other']['category']['feeds']['all']}
    {assign var=wizardDataJson value=$mmp['pageContent']['feeds-other']['category']['feeds']['allJson']}
    {assign var=wizardName value='category'}

    <script>
      if (typeof window.mmpWizardData === 'undefined') {
        window.mmpWizardData = {$wizardDataJson};
      } else {
        window.mmpWizardData = Object.assign(window.mmpWizardData, {$wizardDataJson});
      }
    </script>
    {include file='../components/wizard/main.tpl'}

{else}
    <div class="card full">
        {if isset($mmp['pageContent']['feeds-other']['category']['feeds']['active'])}
            <div class="mmp_feedBox__container">
                <h1 class="mmp_h1">{l s='Active category feeds' mod='mergado'}</h1>
                <div class="mmp_feedBox__holder">
                    {foreach from=$mmp['pageContent']['feeds-other']['category']['feeds']['active'] item=feed}
                        {assign var="feedBoxData" value=$feed['templateData']}

                        {include file="../components/feedBox/feedBox.tpl"}
                    {/foreach}
                </div>
            </div>
        {/if}

        {if isset($mmp['pageContent']['feeds-other']['category']['feeds']['inactive'])}
            <div class="mmp_feedBox__container">
                <h1 class="mmp_h1 mmp_mt100">{l s='Inactive category feeds' mod='mergado'}</h1>

                <div class="mmp_feedBox__holder">
                    {foreach from=$mmp['pageContent']['feeds-other']['category']['feeds']['inactive'] item=feed}
                        {assign var="feedBoxData" value=$feed['templateData']}

                        {include file="../components/feedBox/feedBox.tpl"}
                    {/foreach}
                </div>
            </div>
        {/if}
    </div>
{/if}
