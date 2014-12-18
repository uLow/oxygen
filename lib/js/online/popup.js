/**
 * GLOBAL POPUP OBJECT
 *
 * @method init()
 * @method open({url: 'popup.html'})
 * @method close()
 * @method error()
 */
window.popup = new function()
{

    var body;
    var overlay;
    var loader;
    var content;
    var self;
    var timer;

    this.settings = {

        timeout: 2000,
        error:  'Unable to load content, try again later.',
        loaderTimeout: 500

    };


    this.init = function(settings)
    {
        var $ = window.oxygen.$;
        setSettings(settings);

        body    = $('body');
        overlay = $('<div id="site-overlay"></div>');
        loader  = $('<div id="site-loader"></div>'); 
        content = $('<div id="site-popup-content" class="site-popup-a"></div>');

        content.appendTo(overlay);                
        loader.appendTo(overlay);
        overlay.appendTo(body);  

        bindKeys();

    }


    this.open = function(e)
    {
        self.close();
        setSettings(e);
        //overlay.fadeIn(1000);
        //overlay.show();
        loadContent(); 
        overlay.fadeIn(1000);
    }


    this.close = function(e)
    {
        overlay.hide();
        content.empty();
    }


    this.error = function()
    {
        alert(self.settings.error);
        self.close();
    }    


    function showContent()
    {
        loader.hide();
        content.show();
    }
    

    function setSettings(settings)
    {
        $.extend(self.settings, settings.data || settings);
        $.ajaxSetup({timeout: self.settings.timeout});
    }


    function loadContent()
    {

        //Show loader if content is loading more than loaderTimeout
        timer = window.setTimeout(function(){

            content.hide();
            loader.show();

        }, self.settings.loaderTimeout);

        body.remote(self.settings.url, {}, function(err, res){
            if(err){
                self.error();
            }else{
                content.embed(res);
                showContent();   
            }
             window.clearTimeout(timer);
        });
        
        /*content.load(self.settings.url, function(data, status){

            if(status != 'success'){
                self.error();
            }else{
                showContent();   
            }

            window.clearTimeout(timer);

        });*/
    }


    function bindKeys()
    {
        //Hide popup if user pressed ESC.
        body.keyup(function(e){

            if(e.which == 27)
            {
                self.close();
            }

        });
    }   


    //Make self-reference
    self = this;

}


$(document).ready(popup.init);