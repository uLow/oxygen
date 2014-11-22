var isAuth = $this.find("._authenticated").data("auth");

if(isAuth==1){
    c=0;
    var forget = 0;
    var t;
    function continueSessionPromt(c){
        var timeleft = 0;
        var timeleft_v = '';
        if(600-c > 60){
            timeleft = Math.ceil((600-c)/60);
            timeleft_v = window._l.minutes_short;
        }else{
            timeleft = 600-c;
            timeleft_v = window._l.seconds_short;
        }
        jConfirm(window._l.continue_session, window._l.session_is_ending+' '+timeleft+' '+timeleft_v, {
            okButton: window._l.continue,
            cancelButton: window._l.logout
        }, function(ok){
            if(ok){
                c=0;
                a=0;
                $this.remote('consess', {}, function(err, res){});
                clearTimeout(t);
                countTimeout(a);
            }else{
                //forget+=60;
                location.href='/login?sign-out';
            }
        });
    }

    countTimeout = function(a){
        if(a!=c){
            c=a;
        }
        if(c>300+forget){
            continueSessionPromt(c);
        }
        if((600-c)<=0){
            location.href='/login?sign-out';
        }else{
            c=c+1;
            t=setTimeout("countTimeout(c)", 1000);
        }
    }

    countTimeout(0);
}

$(document).click(function(e) { 
    if (e.button == 0) {
        c=0;
    }
});