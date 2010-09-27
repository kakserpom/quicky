<?php
require_once '../Quicky.class.php';
require_once '../Quicky_BBcode.class.php';
$string = '[url=http://url.tld][b]bold[/b] link[/]
[email]pupkin@mail.ru[/]
[size=20][s]20 strike[/s][/size]
[quote=WP]
<tag>[/quote]
[code]123[/code]
[php]phpinfo();[/php]
[m]phpinfo[/]
[list=a]
[*]one
[*]two
[*]three
[/list]
[g]Quicky[/]
[myblock]123[/myblock]
';
//header('Content-Type: text/plain');
$BBcode = new Quicky_BBcode;
$BBcode->smiles_dir = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'smiles').DIRECTORY_SEPARATOR;
$BBcode->smiles_url = 'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['SCRIPT_NAME'])).'/smiles/';
$BBcode->load($string);
function BBcode_block_myblock($params,$content,$this)
{
 return strrev($content);
}
$BBcode->register_block('myblock','BBcode_block_myblock');
$HTML = $BBcode->getHTML();
if (sizeof($BBcode->errors)) {echo '<pre>'; var_dump($BBcode->errors); echo '</pre>';}
echo $HTML;
