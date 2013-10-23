var $a = $this.find('a.hit');
var $ul = $this.find('ul.items');

$a.click(function(){
	if($ul.hasClass('expanded')){
		$ul.removeClass('expanded').addClass('collapsed');
		$(this).removeClass('collapse').addClass('expand');
	} else if($ul.hasClass('collapsed')){
		$ul.removeClass('collapsed').addClass('expanded');
		$(this).removeClass('expand').addClass('collapse');
		
		$this.remoteSafe('getItems', {
			nodeTemplate:'node',
			criteria:''
		}, function(res){$ul.embed(res, true); console.log(res)});
	}
    $('.searchNodesController').find('ul.items').eq(0).removeClass('collapsed').addClass('expanded');
});