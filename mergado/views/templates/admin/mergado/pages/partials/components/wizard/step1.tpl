{if $wizardType !== $wizardName || $wizardStep === '1' || ($wizardStep === false && !$isAlreadyFinished)}
    <div class="mmp_wizard active" data-mmp-wizard-step="1">
{else}
    <div class="mmp_wizard" data-mmp-wizard-step="1">
{/if}
    <div class="card full">
        <h1 class="mmp_wizard__heading">{l s='Start creating your feeds with Mergado Pack' mod='mergado'}</h1>

        <div class="mmp_wizard__content">
            {if $wizardName === 'static' || $wizardName === 'stock'}
                <a href="javascript:void(0);" class="mmp_btn__blue"
                   data-mmp-wizard-go="3"
                   data-mmp-wizard-do-before="generateNormal"
                >
                    <span>{l s='Run the setup wizard' mod='mergado'}</span>
                    <svg fill="white" class="mmp_wizard__plusIcon">
                        <use xlink:href="{$mmp['images']['baseMmpImageUrl']}plus"></use>
                    </svg>
                </a>
            {else}
                <a href="javascript:void(0);" class="mmp_btn__blue"
                   data-mmp-wizard-go="2"
                >
                    <span>{l s='Run the setup wizard' mod='mergado'}</span>
                    <svg fill="white" class="mmp_wizard__plusIcon">
                        <use xlink:href="{$mmp['images']['baseMmpImageUrl']}plus"></use>
                    </svg>
                </a>
            {/if}
        </div>
    </div>
</div>

<style>
    .mmp_wizard[data-mmp-wizard-step="1"] .mmp_wizard__content {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 150px;
    }

    .mmp_wizard__plusIcon {
        height: 14px;
        width: 15px;
        margin-left: 8px;
    }
</style>