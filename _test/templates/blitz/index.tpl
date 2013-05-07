{{ BEGIN test }}
{{ escape($a) }}
{{ BEGIN sub }}
sub_text
{{ BEGIN sub2 }}
sub2_text
{$a}
{{ END sub2 }}
{{ END sub }}
{{ END test }}