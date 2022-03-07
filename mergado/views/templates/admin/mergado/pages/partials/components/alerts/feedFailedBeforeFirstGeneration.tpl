{assign var=alertDefaultData
value=['alertName' => 'generation_failed',
	'alertSection' => $alertData['alertSection'],
	'type' => 'danger',
	'text' => {l s='The last generation of feed failed.[1]Please start the generation again with the CREATE XML FEED button.' tags=['<br>'] mod='mergado'},
	'closable' => true,
	'closableAll' => false]}

{if !$alertClass->isAlertDisabled($alertData['feedName'], $alertDefaultData['alertName'])}
	{assign var=url value=$mmp['dirs']['alertDir']|cat:"template/alert.tpl"}
	{include file=$url}
{/if}
