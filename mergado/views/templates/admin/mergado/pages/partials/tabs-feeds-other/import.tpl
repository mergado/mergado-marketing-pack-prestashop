<div class="card full">
    <div class="mmp_import">
        <h1 class="mmp_h1">{l s='Update product prices using an XML file' mod='mergado'}</h1>

        <div class="mmp_wizard__content_body">
            <div class="mmp_wizard__content_heading">
                <span>
                    {l s='Insert URL of XML price import feed from Mergado App' mod='mergado'}
                </span>
            </div>

            <label class="priceImportLabel">
                <div class="priceImportLabel__bottom">
                    <input type="url" id="import_product_prices_url" value="{$mmp['pageContent']['feeds-other']['import']['data']['importUrl']}" placeholder="{l s='Insert price import URL from our Mergado App' mod='mergado'}">
                    <a href="#" class="saveAndImportRecursive mmp_btn__blue mmp_btn__blue--small" data-feed="importPrices">
                        <svg>
                            <use xlink:href="{$mmp['images']['baseImageUrl']}turn-on"></use>
                        </svg>

                        <span>{l s='Save and start import' mod='mergado'}</span>
                    </a>
                </div>
            </label>
        </div>

        <div class="mmp_wizard__content_body">
            <div class="mmp_wizard__content_heading">
                <span>
                    {l s='Set Cron for periodically downloading an XML file.' mod='mergado'}
                </span>
                <a href="{l s='https://pack.mergado.com/support#Export%20XML%20feeds' mod='mergado'}" target="_blank">{l s='Read more on our Support page.' mod='mergado'}</a>
            </div>

            <div class="importTabs" data-import-tab="2">
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
                        <p>{l s='Cron will automatically call the URL at the intervals you specify (eg every hour).' mod='mergado'}</p>
                    </div>

                    <div class="mmp_wizard__cronArrow">
                        <svg>
                            <use xlink:href="{$mmp['images']['baseImageUrl']}arrow-right"></use>
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
                            <p>{l s='Each time cron calls a cron URL, import run starts. This will keep your product prices up to date.' mod='mergado'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mmp_feedBox__line mmp_mt-30">
                <div class="mmp_feedBox__line--left">
                    <p class="mmp_feedBox__name">{l s='Import cron URL' mod='mergado'}</p>
                    <input type="text" class="mmp_feedBox__url" readonly value="{$mmp['pageContent']['feeds-other']['import']['data']['cronUrl']}">
                </div>
                <a class="mmp_feedBox__button mmp_btn__blue mmp_btn__blue--small mmp_feedBox__copy priceImport__copy"
                   data-copy-stash="{$mmp['pageContent']['feeds-other']['import']['data']['cronUrl']}"
                   href="javascript:void(0);">

                    <svg class="mmp_icon">
                        <use xlink:href="{$mmp['images']['baseImageUrl']}copy"></use>
                    </svg>
                    {l s='Copy cron URL' mod='mergado'}
                </a>
            </div>
        </div>
    </div>
</div>
