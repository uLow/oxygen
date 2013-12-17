$this.find('button.generate').click(function(){
    $this.remoteSafe('Generate', function(res) {
        o.flash(res);
    });
});