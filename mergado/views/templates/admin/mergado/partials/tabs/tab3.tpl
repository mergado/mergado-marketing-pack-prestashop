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

<div class='mergado-tab' data-tab='3'>
    <div id="mergadoXml">
        <div class="alert alert-info">{l s='Paste the following XML links into your account at [1]Mergado account[/1]. In the Mergado App you can further modify your feeds and convert them into to hundreds of other formats.' tags=['<a href="https://accounts.mergado.com/login/?utm_source=mp&utm_medium=button&utm_campaign=login" style="font-weight: bold;" target="_blank">'] mod='mergado'}</div>

            {foreach from=$xmls item=xmlArray key=k name=mainforeach}
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-exchange"></i>
                    {if $k == 'category'}
                        {l s='XML export - Category feed' mod='mergado'}
                    {/if}

                    {if $k == 'stock'}
                        {l s='XML export - Heureka Availability feed' mod='mergado'}
                    {/if}

                    {if $k == 'static'}
                        {l s='XML export - Analytic feed' mod='mergado'}
                    {/if}

                    {if $k == 'base'}
                        {l s='XML export - Products feed' mod='mergado'}
                    {/if}
                </div>
                <h4>
                    {if $k == 'category'}
                        {l s='Mergado Category feed' mod='mergado'}
                    {/if}

                    {if $k == 'static'}
                        {l s='Mergado Analytic feed' mod='mergado'}
                    {/if}

                    {if $k == 'base'}
                        {l s='Mergado Products feed' mod='mergado'}
                    {/if}

                </h4>
                <table id="mergadoCronList">
                    <thead>
                    <tr>
                        <th>
                            {l s='Feed' mod='mergado'}
                        </th>
                        <th>
                            {l s='Last change' mod='mergado'}
                        </th>
                        <th>
                            {l s='XML URL' mod='mergado'}
                        </th>
                        {if $cronsPartial}

                            <th>
                                {l s='Status of creating' mod='mergado'}
                            </th>
                        {/if}
                        <th>
                            {l s='Actions' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$xmlArray key=key item=xml name=itemforeach}
                        <tr>
                            <th>
                                {$xml['language']|escape:'htmlall':'UTF-8'}
                            </th>
                            <td>
                                {$xml['date']|date_format:'d.m.Y H:i:s'|escape:'htmlall':'UTF-8'}
                            </td>
                            <td>
                                <a href='{$xml['url']|escape:'htmlall':'UTF-8'}' target='_blank'
                                   title='{$xml['language']|escape:'htmlall':'UTF-8'}'>{$xml['url']|escape:'htmlall':'UTF-8'}</a>
                            </td>
                                {if $cronsPartial}
                                    <td class="cron-status-td">
                                        {if $crons[$k][$key]['currentFiles']}
                                            <div class="rangeSlider rangeSlider-{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-range-index="{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-percentage="{round(($crons[$k][$key]['currentFiles'] / ($crons[$k][$key]['totalFiles'] + 1)) * 100, 2)}">
                                                <span>{round(($crons[$k][$key]['currentFiles'] / ($crons[$k][$key]['totalFiles'] + 1)) * 100, 2)}%</span>
                                            </div>
                                        {else}
                                            <div class="rangeSlider rangeSlider-{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-range-index="{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-percentage="100">
                                                <span>100%</span>
                                            </div>
                                        {/if}
                                    </td>
                                {/if}
                            <td class="cron-btn-td">
                                <div style="padding-bottom: 5px; display: inline-block">
                                    <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$xml['url']|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i>{l s='Copy feed URL' mod='mergado'}</a>
                                    <a href='{$xml['url']|escape:'htmlall':'UTF-8'}' target='_blank' title='{$xml['language']|escape:'htmlall':'UTF-8'}' class="btn btn-sm btn-default mmp-btn-hover--info">{l s='Download XML feed' mod='mergado'}</a>
                                </div>
                                <div style="display: inline-block">
                                    {if $k === 'stock' || $k === 'base' || $k === 'category'}
                                        {if isset($crons[$k][$key]['totalFiles']) && isset($crons[$k][$key]['currentFiles']) && !in_array($crons[$k][$key]['totalFiles'], array(0, 1))}
                                            {if $crons[$k][$key]['totalFiles'] > $crons[$k][$key]['currentFiles']}
                                                <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}">
                                                    <i class="icon-play-circle"></i>
                                                    {l s='Manually generate feed' mod='mergado'} {$crons[$k][$key]['currentFiles'] + 1} / {$crons[$k][$key]['totalFiles']}</a>
                                            {else}
                                                <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron last" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}">
                                                    <i class="icon-play-circle"></i>
                                                    {l s='Merge and create new feed' mod='mergado'}
                                                </a>
                                            {/if}
                                        {else}
                                            <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}""
                                            title="">
                                            <i class="icon-play-circle"></i>
                                            {l s='Manually generate feed' mod='mergado'}
                                            </a>
                                        {/if}
                                    {/if}

                                <form method="post" class="mb-0 d-inline">
                                    <input type="hidden" name="delete_url" value="{$xml['file']}">
                                    <input type="hidden" name="page" value="3">
                                    <button class="btn btn-sm btn-default mmp-btn-hover--danger" type="submit" name="submitmergadodelete" data-confirm-message="{l s='Really delete export %s?' mod='mergado' sprintf=[$xml['language']]}"><i class="icon-trash"></i>{l s='Delete export' mod='mergado'}</button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
        </div>
            {/foreach}
    </div>

    {$wideAd}
</div>