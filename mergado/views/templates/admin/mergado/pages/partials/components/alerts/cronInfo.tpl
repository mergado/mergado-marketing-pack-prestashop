{assign var=alertDefaultData
value=['alertName' => 'cronInfo',
	'alertSection' => $alertData['alertSection'],
	'type' => 'warning',
	'text' => {l s='Cron is used to periodically call the selected script, in our case to [1]start feed regeneration and keep it up to date.[/1]' tags=['<strong>'] mod='mergado'},
	'closable' => false,
	'closableAll' => false]}

{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
{include file=$url}
