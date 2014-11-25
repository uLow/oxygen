c = 0;
var forget = 0;
var t;

countTimeout = function(a){
    if(a != c){
        c = a;
    }
    if((600-c) <= 0){

        c = 0;
        a = 0;

        $this.remote('isLogged', {}, function(err, res){
            if(err){
                console.log('Error:'+err);
            }else{
                if(res === false){
                    var miniForm = '<div style="text-align: center;"><div>Enter your credentials to continue without losing data.</div><div style="margin: 8px;"><input type="text" class="reLogin" placeholder="Login"></div><div style="margin: 8px;"><input type="password" class="rePassword" placeholder="Password"></div></div>';
                    var winTitle = 'Session expired';

                    jConfirm(miniForm, winTitle, {
                            okButton: window._l.continue,
                            cancelButton: window._l.logout
                        }, function(ok, $alerts){
                            if(ok){
                                c = 0;
                                a = 0;
                                $this.remote(
                                    'reLogin',
                                    {
                                        login:      $('.reLogin').val(),
                                        password:   $('.rePassword').val()
                                    },
                                    function(err, res){
                                        if(err){
                                            console.log('Error:'+err);
                                        }else if(res === true){
                                            $alerts._hide();
                                            clearTimeout(t);
                                            countTimeout(a);
                                        }else{
                                            alert('Login/password is invalid.');
                                            $('.rePassword').val('');
                                        }
                                    }
                                );
                            }else{
                                location.href = '/login&sign-out';
                            }
                            return false;
                        });
                }
            }
        });

        clearTimeout(t);
        countTimeout(a);

    }else{
        c=c+1;
        t=setTimeout("countTimeout(c)", 1000);
    }
}

countTimeout(0);

$(document).on("click", function(e) { 
    if (e.button == 0) {
        c = 0;
    }
});