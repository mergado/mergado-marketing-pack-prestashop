{assign var=isAlreadyFinished value=$mmp['pageContent']['feeds-other']['stock']['isAlreadyFinished']}

{if (isset($smarty.get['mmp-wizard']) && $smarty.get['mmp-wizard'] === 'stock') || (!$isAlreadyFinished || (isset($smarty.get['step']) && $smarty.get['mmp-wizard'] === 'stock'))}
    {assign var=wizardData value=$mmp['pageContent']['feeds-other']['stock']['wizardData']}
    {assign var=wizardDataJson value=$mmp['pageContent']['feeds-other']['stock']['wizardDataJson']}
    {assign var=wizardName value='stock'}

    <script>
      if (typeof window.mmpWizardData === 'undefined') {
        window.mmpWizardData = {$wizardDataJson};
      } else {
        window.mmpWizardData = Object.assign(window.mmpWizardData, {$wizardDataJson});
      }
    </script>

    {include file='../components/wizard/main.tpl'}
{else}
    {assign var="feedBoxData" value=$mmp['pageContent']['feeds-other']['stock']['templateData']}

    <div class="card full">
        <div class="mmp_feedBox__container">
            <h1 class="mmp_h1">{l s='Heureka Availability feed' mod='mergado'}</h1>
            <div class="mmp_feedBox__holder">
                {include file="../components/feedBox/feedBox.tpl"}
            </div>
        </div>
    </div>
{/if}
