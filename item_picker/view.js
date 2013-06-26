var $search = $this.find('.search');
var $items = $this.find('.items0');
var $name = $this.data('name');

//console.log($items);

var tmpSearch = $search.val();
$search.keydown(_.debounce(function(){
    //$result.text($search.val());
    if(tmpSearch!=$search.val() && $search.val().length>0){
        $items.html('<loader>');
        $items.removeClass('collapsed').addClass('expanded');       
        $this.remoteSafe(
            'componentRequest', {
                component: $name,
                method: 'getData',
                args: $search.val()
            }, function(res){
            if($items.html()==res.body){
                return false;
            }
            if(res.body != ''){
                $items.embed(res, true); 
                //console.log(res);
            }else{
                $items.html('<span class="disabled">empty result</span>');
            }
        });
    }
    tmpSearch = $search.val();
}, ($search.val().length>3)?50:800));