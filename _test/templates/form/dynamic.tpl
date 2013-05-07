This form has been generated (compiled) according to the form object.<br/><br/>
{form name='form1' method='POST'}
{if form->_done}<h1 style="color: green">OK! :)</h1>{/if}
{_foreach from=(form->elements) key="name" item="item"}{_if $item->type == 'submit'}<br />{input join="_$name" value=(form->elements->_$name->value)}{/_if}{_if $item->type == 'select'}<br />{select join="_$name" value=(form->elements->_$name->getValue())}{/select}
	<br/>
{/_if}{
	}{_if $item->type != 'submit' and $item->type != 'select' and $item->type != 'checkbox'}{_$item->title|escape} {input join="_$name" value=(form->elements->_$name)}
	<br/>
{/_if}{_if $item->type == 'checkbox'}{input join="_$name" checked=(form->elements->_$name->getValue())}{/_if}
{/}{/form}