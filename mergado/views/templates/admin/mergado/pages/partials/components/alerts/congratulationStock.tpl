{assign var=alertDefaultData
value=['alertName' => 'congratulation',
	'alertSection' => $alertData['alertSection'],
	'type' => 'success',
	'text' => {l s='[1]Congratulations![/1] You have just created your %s feed in Mergado Pack. Now you can [1]activate Availability feed in your Heureka account.[/1]' sprintf=$alertData['fullName'] tags=['<strong>'] mod='mergado'},
	'closable' => false,
	'closableAll' => true]}

{if !$alertService->isAlertDisabled($alertData['alertSection'], $alertDefaultData['alertName']) && !$alertService->isSectionDisabled($alertData['alertSection'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
