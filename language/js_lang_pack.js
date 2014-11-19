$this.remote('getLangPack', {}, function(err, res){
	window._l = res;
});