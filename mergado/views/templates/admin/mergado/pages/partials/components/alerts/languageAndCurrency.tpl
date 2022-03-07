{assign
var=alertDefaultData
value=['alertName' => 'languageAndCurrency',
'alertSection' => $alertData['alertSection'],
'type' => 'warning',
'text' => {l s='Here you choose which language and currency combination you want to use for export now. [1]You will be able to create other export combinations after completing the wizard.' tags=['<br>'] mod='mergado'},
'closable' => false,
'closableAll' => false
]}

{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
{include file=$url}
