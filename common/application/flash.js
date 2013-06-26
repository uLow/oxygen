$this.flash = function(d) {
    if(d.at) {
        console.log(d.type + ' at '+ d.at);
    }
    var $w = $(document).width();
	var $h = $(window).scrollTop();
	$w = parseInt($w/2-$this.width()/2);
	//var blinkBgColor = (d.type=='error')?'#F2DEDE':'#DFF0D8';
	//var blinkTextColor = (d.type=='error')?'#B94A48':'#000';
    if(d.type != 'debug') {
        var blinkBgColor = ({info:'#D9EDF7',error:'#ED7B7B',warning:'#FCF8E3',success:'#DFF0D8'})[d.type] || '#FFF';
        var blinkTextColor = '#000';//({info:'#1E485A',error:'#5A2423',warning:'#5A4727',success:'#2E5A2F'})[d.type] || '#000';
		//.css({color:blinkTextColor})
        $li = $('<li>').html(d.message).appendTo($this);
        $this.css('top', $h);
		$this.css('left', $w);
		$this.hideIt($li,d);
    } else {
        o.log(d.message);
    }
}

$this.hideIt = function($obj,d) {
	var blinkBgColor = ({info:'#D9EDF7',error:'#ED7B7B',warning:'#FCF8E3',success:'#DFF0D8'})[d.type] || '#FFF';
    var blinkTextColor = '#000';//({info:'#1E485A',error:'#5A2423',warning:'#5A4727',success:'#2E5A2F'})[d.type] || '#000';
	$obj.stop().hide()
            .fadeIn(200)
            .delay(500)
            .animate({backgroundColor:blinkBgColor})
            .animate({backgroundColor:'#FED'})
            .animate({backgroundColor:blinkBgColor})
            .delay(2000)
            .slideUp(200, function(){
				$(this).remove();
			});
}

o.flash = function(message,type){
    $this.flash({type:type,message:message});
}

$this.updateFlash = function () {
    $this.remote('getFlash', function(err,data){
        var clear = false;
        if(err) {
            $this.flash({message:err,type:'error'});
            clear = true;
        } else {
			len = data.length;
			_.each(data,function(d){
                clear = true;
                $this.flash(d);
            });
        }

        /*if(clear){
			$this.remote('clearFlash',function(){}, false);
		}*/
		if(len>=10){
			$this.updateFlash();
		}else if(clear===true){
			$this.remote('clearFlash',function(){}, false);
		}
    }, false);
}

o.on('remote-call-complete',function(){
    $this.updateFlash();
});

$this.updateFlash();

