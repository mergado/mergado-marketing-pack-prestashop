{assign var=alertDefaultData
value=['alertName' => 'settingsInfo',
	'alertSection' => $alertData['alertSection'],
	'type' => 'warning',
	'text' => {l s='These settings are [1]valid for all %s feeds[/1]. After saving the changes, the temporary files will be deleted and the [1]feed creation will start from the beginning with the new settings[/1].' tags=['<strong>'] sprintf=$alertData['alertSection'] mod='mergado'},
	'closable' => true,
	'closableAll' => true]}

{if !$alertService->isAlertDisabled($alertData['feedName'], $alertDefaultData['alertName']) && !$alertService->isSectionDisabled($alertData['alertSection'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
