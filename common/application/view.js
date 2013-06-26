$('input[type=file]').each(function(){
	$input = $(this);
	$val = $input.data('value');
	$inputWidth = $(this).css('width');
	$input.before('<input type="text" style="width:'+$inputWidth+'px;margin-right:-6px;">').before('<input type="button" value="'+$val+'">');
	$input.prev().prev().click(function(){
		$input.click();
		return false;
	});
	$input.prev().click(function(){
		$input.click();
		return false;
	});
	$input.hide();
	$input.change(function(){
		$(this).prev().prev().attr("value", $(this).val());
	});
});