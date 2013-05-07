<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Example</title>
</head>
<body>
<form action="multilang.php">
	{_ HI}.
	{LANG}
	{ru}Вы можете выбрать язык:
	{default}Your can choose your language:
	{/LANG}
	{select name='lang' value=$quicky.requeststring.lang}
	{option text='ru'}
	{option text='en'}
	{/select}
	<input type="submit">
</form>
</body>
</html>