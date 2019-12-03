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
    <div id="mergadoCron">
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-time"></i>
                {l s='Cron generator list' mod='mergado'}
            </div>

            {if empty($crons) && empty($categoryCron) && empty($importCron)}
                <div class="alert alert-info">{l s='You have no active cron' mod='mergado'}</div>
            {/if}

            {if !empty($crons) || !empty($categoryCron) || !empty($importCron)}
                <div class="alert alert-warning">{l s='Do not forget to add following cron links to cron tasks' mod='mergado'}</div>
            {/if}

            {if !empty($crons)}
                <h4>{l s='Data feed' mod='mergado'}</h4>
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
                        {foreach from=$crons item=cron name=cronEach}
                            <tr>
                                <th>
                                    {$cron['name']|escape:'htmlall':'UTF-8'}
                                    {if isset($staticFeed)}
                                        {if $smarty.foreach.cronEach.first && $staticFeed == "1"}
                                            + {l s='Mergado static XML' mod='mergado'}
                                        {/if}
                                    {/if}
                                </th>
                                <td>
                                    <span>{$cron['url']|escape:'htmlall':'UTF-8'}</span>
                                </td>
                                <td>
                                    {if isset($cron['totalFiles']) && isset($cron['currentFiles']) && !in_array($cron['totalFiles'], array(0, 1))}
                                        {if $cron['totalFiles'] > $cron['currentFiles']}
                                            <a class="btn btn-sm btn-warning mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                            {l s='Generate' mod='mergado'} {$cron['currentFiles'] + 1} / {$cron['totalFiles']}</a>
                                        {else}
                                            <a class="btn btn-sm btn-success mergado-manual-cron last" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}">
                                            {l s='Merge and create new feed' mod='mergado'}
                                            </a>
                                        {/if}
                                    {else}
                                        <a class="btn btn-sm btn-success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}""
                                           title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                                            {l s='Generate feed' mod='mergado'}
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}

            {if !empty($categoryCron)}
                <br/>
                <h4>{l s='Category feed' mod='mergado'}</h4>
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
                                <a class="btn btn-sm btn-success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$cron['xml']|escape:'htmlall':'UTF-8'}"
                                   title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                                    {l s='Generate feed' mod='mergado'}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/if}

            {if !empty($importCron)}
                <br/>
                <h4>{l s='Import Mergado prices' mod='mergado'}</h4>
                <table>
                    <tbody>
                        <th>{l s='Cron URL' mod='mergado'}</th>
                        <td>{$importCron}</td>
                    <td>
                        <a class="btn btn-sm btn-success mergado-manual-cron" data-generate="import_prices" href="javascript:void(0)"
                           title="{$cron['name']|escape:'htmlall':'UTF-8'}">
                            {l s='Import prices' mod='mergado'}
                        </a>
                    </td>
                    </tbody>
                </table>
            {/if}
        </div>

        {if isset($tab2Import)}
            {$tab2Import}
        {/if}

        {if isset($tab2)}
            {$tab2}
        {/if}

    </div>
</div>