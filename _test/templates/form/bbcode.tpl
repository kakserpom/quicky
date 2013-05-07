{form name='bbform' method='POST'}
{if form->_done}
	{?$result = form->elements->mybbarea->getHTML()}
	{if !sizeof(form->elements->mybbarea->_bbcode->errors)}<font color="green"><h2>Ok!</h2></font>
	{else}<font color="red"><h2>Errors:</h2></font>
		<ul>
			{foreach from=(form->elements->mybbarea->_bbcode->errors) item='errmsg'}
				<li>{$errmsg|escape}</li>
			{/foreach}
		</ul>
	{/if}
	<fieldset>
		<legend>Result</legend>
		{$result}
	</fieldset>
	<br/>
	{if sizeof(form->elements->mybbarea->_bbcode->stat)}
		<fieldset>
			<legend>Statistics</legend>
			Num of blocks: {form->elements->mybbarea->_bbcode->stat.numblocks}<br/>
			Num of tags: {form->elements->mybbarea->_bbcode->stat.numtags}<br/>
			Num of parameters: {form->elements->mybbarea->_bbcode->stat.numparams}<br/>
		</fieldset>
		<br/>
	{/if}
{/if}
	Put BBcode:
	<br/>
{textarea join='mybbarea' cols=80 rows=10 value=(form->elements->mybbarea)}
	<br/>
	<br/>
{input join='btn1'}
{/form}