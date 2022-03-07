{if $wizardData == null}
{*    'NO DATA .. exit here';*}
{/if}

{if isset($smarty.get['mmp-wizard'])}
    {assign var="wizardType" value=$smarty.get['mmp-wizard']}
{else}
    {assign var="wizardType" value=''}
{/if}


{if isset($smarty.get['mmp-wizard-feed'])}
    {assign var="wizardFeed" value=$smarty.get['mmp-wizard-feed']}
{else}
    {assign var="wizardFeed" value=''}
{/if}


{if isset($smarty.get['step'])}
    {assign var="wizardStep" value=$smarty.get['step']}
{else}
    {assign var="wizardStep" value=false}
{/if}

{if isset($smarty.get['force'])}
    {assign var="wizardForced" value=true}
{else}
    {assign var="wizardForced" value=false}
{/if}


<div class="wizard" data-forced="{$wizardForced}" data-mmp-wizard="{$wizardName}">
    {include file='../wizard/step1.tpl'}
    {include file='../wizard/step2.tpl'}

    {foreach from=$wizardData item=wizard}
        {assign var="wizardData" value=$wizard}
            {include file='../wizard/step3.tpl'}
            {include file='../wizard/step4.tpl'}
    {/foreach}
</div>

<div class="mmp_wizardDialog">
    <div class="mmp_dialog"
         id="mmp_dialogAttention"
         data-mmp-content="<h1 class='mmp_dialog__title'>{l s='ATTENTION: Please do not leave or close the page until the entire feed is generated.' mod='mergado'}<br><strong>{l s='This process may take a while depending on the number of products in your eshop.' mod='mergado'}</strong></h1><p>{l s='Otherwise you will have to wait until the cron service generated the whole feed according to the specified frequency. It will take longer.' mod='mergado'}</p>" data-mmp-yes="{l s='Ok, I understand' mod='mergado'}" data-mmp-no="">
    </div>
</div>

<div class="mmp_dialog"
     id="mmp_dialogLeave"
     data-mmp-content="<h1>{l s='Are you sure you want to leave the page?' mod='mergado'}</h1><p>{l s='This interrupts the current feed generation process.' mod='mergado'}</p>"
     data-mmp-no="{l s='Leave page' mod='mergado'}"
     data-mmp-yes="{l s='Stay on page' mod='mergado'}">
</div>

<div class="mmp_dialog"
     id="mmp_dialogAlreadyRunning"
     data-mmp-content="<h1>{l s='We are sorry' mod='mergado'}</h1><p>{l s='It seems, that cron is currently running. Try it again after few minutes.' mod='mergado'}</p>"
     data-mmp-no=""
     data-mmp-yes="Continue"></div>
<div class="mmp_dialog" id="mmp_dialogError" data-mmp-content="<h1>{l s='Something went wrong. Feed can\'t be generated.' mod='mergado'}</h1><p>{l s='Try to change number of products generated in one cron run or contact our support.' mod='mergado'}</p>" data-mmp-no="" data-mmp-yes="{l s='Continue' mod='mergado'}"></div>