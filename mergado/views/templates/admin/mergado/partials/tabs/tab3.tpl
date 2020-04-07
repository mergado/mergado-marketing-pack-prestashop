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
            <div class="alert alert-info">{l s='Vložte následující XML odkazy do vašeho účtu na [1][/1]. V Mergado aplikaci můžete váš feed dále modifikovat a převádět na stovky dalších formátů.' tags=['<a href="https://accounts.mergado.com/login/?utm_source=mp&utm_medium=button&utm_campaign=login" style="font-weight: bold;" target="_blank">www.mergado.cz</a>'] mod='mergado'}</div>

            {foreach from=$xmls item=xmlArray key=k name=mainforeach}
            <div class="panel " id="mergado_fieldset_mergado_lang">
                <div class="panel-heading">
                    <i class="icon-exchange"></i>
                    {if $k == 'category'}
                        {l s='XML export - feedy kategorií' mod='mergado'}
                    {/if}

                    {if $k == 'stock'}
                        {l s='XML export - heureka dostupnostní feedy' mod='mergado'}
                    {/if}

                    {if $k == 'static'}
                        {l s='XML export - analytické feedy' mod='mergado'}
                    {/if}

                    {if $k == 'base'}
                        {l s='XML export - produktové feedy' mod='mergado'}
                    {/if}
                </div>
                <h4>
                    {if $k == 'category'}
                        {l s='Mergado kategorie XML' mod='mergado'}
                    {/if}

                    {if $k == 'static'}
                        {l s='Mergado analytické XML' mod='mergado'}
                    {/if}

                    {if $k == 'base'}
                        {l s='Mergado produktové XML' mod='mergado'}
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
                                            <div class="rangeSlider rangeSlider-{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-range-index="{$smarty.foreach.mainforeach.index}{$smarty.foreach.itemforeach.index}" data-percentage="{round(($crons[$k][$key]['currentFiles'] / ($crons[$k][$key]['totalFiles'] + 1)) * 100, 2)}">
                                                <span>{round(($crons[$k][$key]['currentFiles'] / ($crons[$k][$key]['totalFiles'] + 1)) * 100, 2)}%</span>
                                            </div>
                                    </td>
                                {/if}
                            <td class="cron-btn-td">
                                <a href="#" class="btn btn-sm btn-default mmp-btn-hover--info" data-copy-stash="{$xml['url']|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i>{l s='Kopírovat URL feedu' mod='mergado'}</a>

                                {if $k == 'stock' || $k == 'category'}
                                    <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}"
                                       title="{$key|escape:'htmlall':'UTF-8'}">
                                        <i class="icon-play-circle"></i>
                                        {l s='Ručně generovat feed' mod='mergado'}
                                    </a>
                                {elseif $k == 'base'}
                                    {if isset($crons['base'][$key]['totalFiles']) && isset($crons['base'][$key]['currentFiles']) && !in_array($crons['base'][$key]['totalFiles'], array(0, 1))}
                                        {if $crons['base'][$key]['totalFiles'] > $crons['base'][$key]['currentFiles']}
                                            <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}">
                                                <i class="icon-play-circle"></i>
                                                {l s='Ručně generovat feed' mod='mergado'} {$crons['base'][$key]['currentFiles'] + 1} / {$crons['base'][$key]['totalFiles']}</a>
                                        {else}
                                            <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron last" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}">
                                                <i class="icon-play-circle"></i>
                                                {l s='Merge and create new feed' mod='mergado'}
                                            </a>
                                        {/if}
                                    {else}
                                        <a class="btn btn-sm btn-default mmp-btn-hover--success mergado-manual-cron" href="javascript:void(0)" data-generate="generate_xml" data-cron="{$key|escape:'htmlall':'UTF-8'}""
                                        title="{$crons['base'][$key]['name']|escape:'htmlall':'UTF-8'}">
                                        <i class="icon-play-circle"></i>
                                        {l s='Ručně generovat feed' mod='mergado'}
                                        </a>
                                    {/if}
                                {/if}

                                <form method="post" class="mb-0 d-inline">
                                    <input type="hidden" name="delete_url" value="{$xml['file']}">
                                    <input type="hidden" name="page" value="3">
                                    <button class="btn btn-sm btn-default mmp-btn-hover--danger" type="submit" name="submitmergadodelete" data-confirm-message="{l s='Opravdu smazat export %s?' mod='mergado' sprintf=[$xml['language']]}"><i class="icon-trash"></i>{l s='Smazat feed' mod='mergado'}</button>
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
        </div>
            {/foreach}
    </div>
</div>