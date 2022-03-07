{assign var=alertDefaultData
value=['alertName' => 'congratulationSimple',
	'alertSection' => $alertData['alertSection'],
	'type' => 'success',
	'text' => {l s='[1]Well done.[/1] You have just create your %s feed in Mergado Pack.' tags=['<strong>'] sprintf=$alertData['fullName'] mod='mergado'},
	'closable' => false,
	'closableAll' => true]}

{if !$alertClass->isAlertDisabled($alertData['alertSection'], $alertDefaultData['alertName']) && !$alertClass->isSectionDisabled($alertData['alertSection'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
