{form name='form1' method='POST'}
{if form->done}<h2>Ok!</h2>{/if}
	Form name: {form->name}<br/>
	<br/>
{_if isset(form->elements->captcha1)}
{input type='hidden' name='captcha_id' value=(form->elements->captcha1->_imgid)}
	<img src="captcha_img.php?id={form->elements->captcha1->_imgid|escape:'urlencode'}"/>
	: {input join='captcha1' size=4}
{if form->elements->captcha1->_errormsg neq ''}
	<font color="red">- {form->captcha1->_errormsg|escape}</font>
{/if}<br/>
	<br/>
{/_if}
{input join='btn1'}
{/form}