{$myFunc(1,2,3+5)}
{*{helper myhelper($a,$b,$c)}
{?$d = $b*$b-4*$a*$c}
{return array((-$b+sqrt($d))/2*$a,(-$b-sqrt($d))/2*$a)}
{/helper}
{?$x = myhelper(5,10,2)}
x<sub>1</sub> = {$x.0}<br />x<sub>2</sub> = {$x.1}*}