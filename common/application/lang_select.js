$this.find(".selectLang").click(function(){
	var language = $(this).data("lang");
	$this.remote(
		"selectLanguege", 
		{
			lang: language, 
			page: location.href
		}, 
		function(err, res){
			if(err){
				console.log(err);
			}else{
				if(location.href == res){
					window.location.reload();
				}else{
					location.href = res;
				}			
			}
		}
	);
});