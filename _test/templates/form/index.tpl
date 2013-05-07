{form name='form1' method='POST'}
{if form->_done}<h2>Ok!</h2>{/if}
	Form name: {form->name}<br/>
{_if isset(form->elements->text1)}Put here "Quicky": {input join='text1' value=(form->text1)}
{if form->elements->text1->_errormsg neq ''}
	<font color="red">- {form->text1->_errormsg|escape}</font>
{/if}<br/>
	<br/>
{/_if}
{input join='btn1'}

{element type='myinput' arg='value'}

{blockelement type='myblock'}
	test content
{/blockelement}

{/form}