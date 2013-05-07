{if $action == 'play'}
	{if isset($humantake) && $humantake}You taked {$humantake}.<br/>{/if}
	{if isset($comptake) && $comptake}I taked {$comptake}. Your turn.{/if}
	Number of Rocks:
	<b>{$quicky.session.lastrock_num}</b>
	<br/>
	<br/>
	{form name='play' method='POST'}
	{if isset($errmsg) && ($errmsg neq '')}<font color="red">{$errmsg|escape}</font>{/if}
	{if $quicky.session.lastrock_num >= 1}{input join='btn1' type='submit' value='I take 1'}&nbsp;{/if}
	{if $quicky.session.lastrock_num >= 2}{input join='btn2' type='submit' value='I take 2'}&nbsp;{/if}
	{if $quicky.session.lastrock_num >= 3}{input join='btn3' type='submit' value='I take 3'}&nbsp;{/if}
		<br/>
		<br/>
	{input type='button' onclick='location.href = "?stop=1"' value='Stop'}
	{/form}
{elseif $action == 'intro'}
	{if isset($quicky.session.lastrock_gameover) && $quicky.session.lastrock_gameover}You loose!<br/><br/>{/if}
	{form name='startgame' method='POST'}
		Welcome!
		<br/>
		Enter number of rocks: {input join='num' value=(form->num)}{if form->elements->num->_errormsg neq ''}<font color="red">- {form->num->_errormsg|escape}</font>{/if}
		<br/>
		<br/>
	{input join='startbtn' type='submit' value='Start'}
	{/form}
{/if}