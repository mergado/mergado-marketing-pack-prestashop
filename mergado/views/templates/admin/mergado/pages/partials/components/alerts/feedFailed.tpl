{assign var=alertDefaultData
value=['alertName' => 'generation_failed',
	'alertSection' => $alertData['alertSection'],
	'type' => 'danger',
	'text' => {l s='The last generation of feed failed.[1]Please start the generation again with the FINISH MANUALLY button.' tags=['<br>'] mod='mergado'},
	'closable' => true,
	'closableAll' => false]}

{if !$alertService->isAlertDisabled($alertData['feedName'], $alertDefaultData['alertName'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
