{assign var=alertDefaultData
value=['alertName' => 'congratulation',
	'alertSection' => $alertData['alertSection'],
	'type' => 'success',
	'text' => {l s='[1]Congratulations![/1] You have just created your feed in Mergado Pack. Once the feed is ready (the indicator will be green), you can [1]activate Availability feed in your Heureka account.[/1]' tags=['<strong>'] sprintf=$alertData['fullName'] mod='mergado'},
	'closable' => true,
	'closableAll' => true]}

{if !$alertService->isAlertDisabled($alertData['alertSection'], $alertDefaultData['alertName']) && !$alertService->isSectionDisabled($alertData['alertSection'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
