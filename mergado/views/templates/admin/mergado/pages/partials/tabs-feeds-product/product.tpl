{assign var=isAlreadyFinished value=$mmp['pageContent']['feeds-product']['product']['isAlreadyFinished']}


{if (isset($smarty.get['mmp-wizard']) && $smarty.get['mmp-wizard'] === 'product') || (!$isAlreadyFinished || (isset($smarty.get['step']) && $smarty.get['mmp-wizard'] === 'product'))}
    {assign var=wizardData value=$mmp['pageContent']['feeds-product']['product']['feeds']['all']}
    {assign var=wizardDataJson value=$mmp['pageContent']['feeds-product']['product']['feeds']['allJson']}
    {assign var=wizardName value='product'}

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
        {if isset($mmp['pageContent']['feeds-product']['product']['feeds']['active'])}
            <div class="mmp_feedBox__container">
                <h1 class="mmp_h1">{l s='Active product feeds' mod='mergado'}</h1>

                <div class="mmp_feedBox__holder">
                    {assign var="congratulationsActive" value=false}

                    {foreach from=$mmp['pageContent']['feeds-product']['product']['feeds']['active'] item=feed}
                        {assign var="feedBoxData" value=$feed['templateData']}

                        {include file="../components/feedBox/feedBox.tpl"}
                    {/foreach}
                </div>
            </div>
        {/if}

        {if isset($mmp['pageContent']['feeds-product']['product']['feeds']['inactive'])}
            <div class="mmp_feedBox__container">
                <h1 class="mmp_h1">{l s='Inactive product feeds' mod='mergado'}</h1>

                <div class="mmp_feedBox__holder">
                    {foreach from=$mmp['pageContent']['feeds-product']['product']['feeds']['inactive'] item=feed}
                        {assign var="feedBoxData" value=$feed['templateData']}

                        {include file="../components/feedBox/feedBox.tpl"}
                    {/foreach}
                </div>
            </div>
        {/if}
    </div>
{/if}
