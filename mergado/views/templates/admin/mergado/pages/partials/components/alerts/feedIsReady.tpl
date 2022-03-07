{assign var=alertDefaultData value=['alertName' => 'feedIsReady',
	'alertSection' => $alertData['alertSection'],
	'type' => 'success',
	'text' => {l s='[1]The %s feed is ready[/1]. You can now go to the List of feeds and create an export in the Mergado App.' tags=['<strong>'] sprintf=$wizardName mod='mergado'},
	'closable' => false,
	'closableAll' => false]}

{if !$alertClass->isAlertDisabled($alertData['feedName'], $alertDefaultData['alertName']) && !$alertClass->isSectionDisabled($alertData['alertSection'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
