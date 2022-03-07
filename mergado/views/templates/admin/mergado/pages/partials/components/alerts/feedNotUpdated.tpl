{assign var=alertDefaultData
value=['alertName' => 'feed_not_updated',
	'alertSection' => $alertData['alertSection'],
	'type' => 'danger',
	'text' => {l s='Check your cron settings. Feed was last updated %s [1][2]Cron URL for external service can be found in the detail of your feed.[/2]' tags=['<br>', '<small>'] sprintf=$feedBoxData['lastUpdate'] mod='mergado'},
	'closable' => false,
	'closableAll' => false]}

{if !$alertClass->isAlertDisabled($alertData['feedName'], $alertDefaultData['alertName'])}
    {assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
