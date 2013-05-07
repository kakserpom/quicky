Welcome To Quicky. This page for new syntax examples.

{capture name="abc"}
	123
{/capture}
<hr/>
<code>
	<xmp>{literal}
			{?$x = 25}
			{?$y = 123}
			{?$r = sqrt($x) + $y}
			r = {$r}
		{/literal}</xmp>
</code>
Result: {?$x = 25}
{?$y = 123}
{?$r = sqrt($x) + $y}
r = {$r}
<br/>
// {ldelim}math{rdelim} not needed anymore ;)
<hr/>
<code>
	<xmp>{literal}
			{?$html = '
			<b>Bold!</b>
			'}
			{$html|escape}
		{/literal}</xmp>
</code>
{?$html = '<b>Bold!</b>'}
{$html|escape}
<hr/>
Switch:
<code>
	<xmp>{literal}
			{?$val = 2}
			{switch $val}
			{case 1}One{break}
			{case 2}Two{break}
			{case 3}Three{break}
			{default}Default
			{/switch}
		{/literal}</xmp>
</code>
{?$val = 2}
{switch $val}
{case 1}One{break}
{case 2}Two{break}
{case 3}Three{break}
{default}Default
{/switch}
<hr/>
Foreach with Magic constansts
<code>
	<xmp>{literal}
			{foreach name="one" key="key" value="value" from=array('Key1' => 'value1', 'Key2' => 'value2', 'Key3' => 'Value3', 'KeyN' => 'ValueN')}
			{iteration is odd?'~':'-'}{$key} = {$value}
			<br/>
			{/foreach}
		{/literal}</xmp>
</code>
{foreach name="one" key="key" value="value" from=array('Key1' => 'value1', 'Key2' => 'value2', 'Key3' => 'Value3', 'KeyN' => 'ValueN')}
	{iteration is odd?'~':'-'}{$key} = {$value}<br/>
{/foreach}