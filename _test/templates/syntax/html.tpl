<form method="POST">
	{input type="text" name="foo" value=$quicky.requeststring.foo size=10 pattern='/^[a-z0-9_\-]+$/' onunmatch="alert('���� \"foo\" ��������� �������!'); this.focus();"}<br/>
	{input type="checkbox" name="box" value='1' checked=isset($quicky.request.box) default=(!isset($quicky.request.submit))}<br/>
	{input type="radio" name="radio" value=1 checked=$quicky.requeststring.radio default=1}


	{input type="radio" id="radio_2" label='Text...' name="radio" value=2 checked=$quicky.requeststring.radio}<br/>
	{select name="myselect" value=$quicky.requeststring.myselect}
	{optgroup text="Group"}
	{option value='1' text="One"}
	{option value='2' text="Two"}
	{/optgroup}
	{/select}<br/><br/>
	{input type="button" name="submit" type="submit" value="Submit"}<br/>
</form>
<hr/>
1: {input type="text" id="calcfield1"}
2: {input type="text" id="calcfield2"}
{joincalculator name='mycalc' fields='calcfield1 as one,calcfield2 as two' onkeydown=1}
calcfield1 = calcfield2/103*100
calcfield2 = calcfield1/100*103
{/}
<hr/>
Q: {array('test','param' => 1, 'teee' => NULL)|get_query:'a'}
