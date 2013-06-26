$this.find('#reflectDatabase').click(function(){
	$this.remoteSafe('reflectDB', function(data){
		o.flash("Database reflected");
		location.href=location.href;
		window.location.reload();
	});
});