{assign
var=alertDefaultData
value=['alertName' => 'longTime',
'alertSection' => $alertData['alertSection'],
'type' => 'warning',
'text' => {l s='Creating XML feed takes a while based on how many products you have in the feed. Large feeds with thousands of items are divided into several generation steps. [1]This process can take up to half an hour.[/1]' tags=['<strong>'] mod='mergado'},
'closable' => false,
'closableAll' => false
]}

{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
{include file=$url}
