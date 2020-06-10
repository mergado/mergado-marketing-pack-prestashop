{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    www.mergado.cz
*  @copyright 2016 Mergado technologies, s. r. o.
*  @license   LICENSE.txt
*}

<div class='mergado-tab' data-tab='2'>
    <div>
        {if empty($crons) && empty($categoryCron) && empty($importCron)}
            <div class="alert alert-info">{l s='You have no active cron' mod='mergado'}</div>
        {/if}

        {if !empty($crons) || !empty($categoryCron) || !empty($importCron)}
            <div class="alert alert-info">{l s='Nezapomeňte přidat následující cron odkazy do vašeho plánovače úloh. Plánovač úloh slouží k automatickému spouštění skriptů, v tomto případě k automatickému přegenerování XML feedů. Cron plánovače úloh jsou běžně dostupné například v rámci web hostingu.' mod='mergado'}</div>
        {/if}
    </div>

    <div id="mergadoCron">
        <h2>{l s='Exportní úlohy' mod='mergado'}</h2>

        {if !empty($crons)}
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Seznam cronů - produktové feedy' mod='mergado'}
                </div>

                <p>
                    <span class="mmp-tag mmp-tag--question"></span><strong>{l s='Mergado analytické XML' mod='mergado'}</strong> - {l s='vygenerováním jakéhokoliv produktového feedu dojde i k vygenerování statistického feedu, pokud je povolen.' mod='mergado'}
                </p>

                <table id="mergadoCronList">
                    <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Cron URL' mod='mergado'}
                        </th>
                        <th>
                            {l s='Action' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach from=$crons item=cronCat key=cronCategoryName name=cronCatEach}
                            {if $cronCategoryName !== 'stock'}
                            {foreach from=$cronCat item=cron name=cronEach}
                            <tr>
                                <th>
                                    {$cron['name']|escape:'htmlall':'UTF-8'}
                                    {if isset($staticFeed)}
                                        {if $smarty.foreach.cronEach.first && $staticFeed == "1"}
                                            + {l s='Mergado analytic XML' mod='mergado'}
                                        {/if}
                                    {/if}
                                </th>
                                <td>
                                    <span>{$cron['url']|escape:'htmlall':'UTF-8'}</span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$cron['url']|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i>{l s='Kopírovat cron URL' mod='mergado'}</a>

                                                {if isset($cron['totalFiles']) && isset($cron['currentFiles']) && !in_array($cron['totalFiles'], array(0, 1))}
                                                    {if $cron['totalFiles'] > $cron['currentFiles']}
                                                        <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                                            <i class="icon-play-circle"></i>
                                                        {l s='Ručně generovat feed' mod='mergado'} {$cron['currentFiles'] + 1} / {$cron['totalFiles']}</a>
                                                    {else}
                                                        <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron last" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                                            <i class="icon-play-circle"></i>
                                                            {l s='Merge and create new feed' mod='mergado'}
                                                        </a>
                                                    {/if}
                                                {else}
                                                    <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}""
                                                       title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                                                        <i class="icon-play-circle"></i>
                                                        {l s='Ručně generovat feed' mod='mergado'}
                                                    </a>
                                                {/if}
                                            </td>
                                        </tr>
                                {/foreach}
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}

        {if !empty($categoryCron)}
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Seznam cronů - feedy kategorií' mod='mergado'}
                </div>
                <table id="mergadoCronList">
                    <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Cron URL' mod='mergado'}
                        </th>
                        <th>
                            {l s='Action' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$categoryCron item=cron name=cronEach}
                        <tr>
                            <th>
                                {$cron['name']|escape:'htmlall':'UTF-8'}
                            </th>
                            <td>
                                <span>{$cron['url']|escape:'htmlall':'UTF-8'}</span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$cron['url']|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i> {l s='Kopírovat cron URL' mod='mergado'}</a>
                                <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}"
                                   title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                                    <i class="icon-play-circle"></i>
                                    {l s='Ručně generovat feed' mod='mergado'}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}

        {if !empty($crons)}
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Seznam cronů - heureka dostupnostní feed' mod='mergado'}
                </div>
                <p>
                    <span class="mmp-tag mmp-tag--question"></span><strong>{l s='Mergado analytické XML' mod='mergado'}</strong> - {l s='vygenerováním dostupnostního feedu dojde i k vygenerování statistického feedu, pokud je povolen.' mod='mergado'}
                </p>

                <table id="mergadoCronList">
                    <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Cron URL' mod='mergado'}
                        </th>
                        <th>
                            {l s='Action' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$crons item=cronCat key=cronCategoryName name=cronCatEach}
                        {if $cronCategoryName === 'stock'}
                            {foreach from=$cronCat key=key item=cron name=cronEach}
                                <tr>
                                    <th>
                                        {$cron['name']|escape:'htmlall':'UTF-8'}
                                        {if isset($staticFeed)}
                                            {if $smarty.foreach.cronEach.first && $staticFeed == "1"}
                                                + {l s='Mergado analytic XML' mod='mergado'}
                                            {/if}
                                        {/if}
                                    </th>
                                    <td>
                                        <span>{$cron['url']|escape:'htmlall':'UTF-8'}</span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$cron['url']|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i>{l s='Kopírovat cron URL' mod='mergado'}</a>

                                        {if isset($cron['totalFiles']) && isset($cron['currentFiles']) && !in_array($cron['totalFiles'], array(0, 1))}
                                            {if $cron['totalFiles'] > $cron['currentFiles']}
                                                <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                                    <i class="icon-play-circle"></i>
                                                    {l s='Ručně generovat feed' mod='mergado'} {$cron['currentFiles'] + 1} / {$cron['totalFiles']}</a>
                                            {else}
                                                <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron last" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                                    <i class="icon-play-circle"></i>
                                                    {l s='Merge and create new feed' mod='mergado'}
                                                </a>
                                            {/if}
                                        {else}
                                            <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}""
                                            title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                                            <i class="icon-play-circle"></i>
                                            {l s='Ručně generovat feed' mod='mergado'}
                                            </a>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        {/if}
                    {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}


        {if !empty($importCron)}
            <h2>{l s='Importní úlohy' mod='mergado'}</h2>
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-time mmp-mr-sm"></i>
                    {l s='Seznam cronů - import cen' mod='mergado'}
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Cron URL' mod='mergado'}
                        </th>
                        <th>
                            {l s='Action' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <th>{l s='Cron URL' mod='mergado'}</th>
                        <td>{$importCron}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$importCron|escape:'htmlall':'UTF-8'}">
                            <i class="icon-copy"></i>
                            {l s='Kopírovat cron URL' mod='mergado'}
                        </a>
                        <a class="btn btn-sm btn-default mergado-manual-cron mmp-btn-hover--success" data-generate="import_prices" href="javascript:void(0)"
                           title="
{*{$cron['name']|escape:'htmlall':'UTF-8'}*}
">
                            <i class="icon-play-circle"></i>
                            {l s='Jednorázově importovat' mod='mergado'}
                        </a>
                    </td>
                    </tbody>
                </table>
            </div>
        {/if}
    </div>

    {$wideAd}
</div>