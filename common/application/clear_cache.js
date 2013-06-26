$this.find('#clearCache').click(function(){
	$this.remoteSafe('clearCache', function(data){
		o.flash("Cache cleared");
		location.href=location.href;
		window.location.reload();
	});
});