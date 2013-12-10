var $a = $this.find('a.hit').eq(0);
var $ul = $this.find('ul.items').eq(0);

$a.click(function(){
	if($ul.hasClass('expanded')){
		$ul.removeClass('expanded').addClass('collapsed');
		$(this).removeClass('collapse').addClass('expand');
	} else if($ul.hasClass('collapsed')){
		$ul.removeClass('collapsed').addClass('expanded');
		$(this).removeClass('expand').addClass('collapse');
		
		$this.remote('getItems', {
			nodeTemplate:'node',
			criteria:''
		}, function(err, res){
			if(err){
				//console.log(err);
			}else{
				$ul.embed(res, true); 
				//console.log(res);
			}			
		});
	}
    $('.searchNodesController').find('ul.items').eq(0).removeClass('collapsed').addClass('expanded');
});