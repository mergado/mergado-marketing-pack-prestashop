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
        <div class="panel " id="mergado_fieldset_mergado_lang">
            <div class="panel-heading">
                <i class="icon-exchange"></i>
                {l s='XML list' mod='mergado'}
            </div>

            <div class="alert alert-warning">{l s='Insert following XML links to your account on www.mergado.cz' mod='mergado'}</div>

            {foreach from=$xmls item=xmlArray key=k}
                <h4>
                    {if $k == 'category'}
                        {l s='Mergado\'s category XML' mod='mergado'}
                    {/if}

                    {if $k == 'stock'}
                        {l s='Mergado\'s stock XML' mod='mergado'}
                    {/if}

                    {if $k == 'static'}
                        {l s='Mergado\'s static XML' mod='mergado'}
                    {/if}

                    {if $k == 'base'}
                        {l s='Mergado\'s data XML' mod='mergado'}
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
                        <th>
                            {l s='Delete' mod='mergado'}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$xmlArray item=xml}
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
                            <td class="td-center">
                                <form method="post" class="mb-0">
                                    <input type="hidden" name="delete_url" value="{$xml['file']}">
                                    <input type="hidden" name="page" value="3">
                                    <button class="btn btn-sm btn-danger" type="submit" name="submitmergadodelete">{l s='Delete' mod='mergado'}</button>
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <br/>
                <br/>
            {/foreach}
        </div>
    </div>
</div>