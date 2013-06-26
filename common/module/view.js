$this.find('.generate').click(function(){
    $this.remoteSafe('Generate',function(res){
        o.flash('Generated:' + res);
    })
})