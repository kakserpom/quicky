{config_load file='test.conf' section="setup"}
{include file='simple/header.tpl' title='foo'}
<PRE>{* bold and title are read from the config file *}
	{if #bold#}<b>{/if}
	{* capitalize the first letters of each word of the title *}
	Title: {#title#|capitalize}
	{if #bold#}</b>{/if}

	The current date and time is {$quicky.now|date_format:"%Y-%m-%d %H:%M:%S"}

	The value of global assigned variable $SCRIPT_NAME is {$SCRIPT_NAME}

	Example of accessing server environment variable SERVER_NAME: {$quicky.server.SERVER_NAME}

	The value of {ldelim}$Name{rdelim} is <b>{$Name}</b>

variable modifier example of {ldelim}$Name|upper{rdelim}

<b>{$Name|upper}</b>


An example of a section loop:

	{section name='outer' loop=$FirstName} {($quicky.section.outer.index is odd by 2)?'.':'*'} {$quicky.section.outer.rownum} * {$FirstName[outer]} {$LastName[outer]}
		{sectionelse}
		none
	{/section}

	An example of section looped key values:
	{section name='sec1' loop=$contacts}
		phone: {$contacts[sec1].phone}
		fax: {$contacts[sec1].fax}
		cell: {$contacts[sec1].cell}
	{/section}
	<p>testing strip spaces
		{strip}
<table border=0>
	<tr>
		<td>
			<A HREF="{$SCRIPT_NAME}">
				<font color="red">This is a test </font>
			</A>
		</td>
	</tr>
</table>
	{/strip}

</PRE>
{include file="simple/footer.tpl"}
