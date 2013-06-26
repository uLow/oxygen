var $dataSet = $('table.dataSet');
var $body = $dataSet.find('tbody');
var $loader = $dataSet.find('img.loader');

var offset = $dataSet.data('offset');
var filter = $dataSet.data('filter');
var order = $dataSet.data('order');

var $search = $this.find('#search');
var tmpSearch = $search.val();
$search.keydown(_.debounce(function(){
    if(tmpSearch!=$search.val()/* && $search.val().length>0*/){
    	$dataSet.data('filter', $search.val());
        $this.remote(
            'find', 
            {
                search: $search.val()
            }, 
            function(err,res){
	            if(err) {
		            o.flash(err,'error');
		        } else {
		            $dataSet.data('limit', $dataSet.data('limit')+res.count);
		            count = res.count;
		            $body.embed(res.embed, true);
		        }
        	}
        );
    }
    tmpSearch = $search.val();
}, ($search.val().length>3)?50:800));

$this.find('.loadAll').click(function(){
    $this.remote(
        'loadAll', 
        {
            search: $dataSet.data('filter')
        }, 
        function(err,res){
            if(err) {
	            o.flash(err,'error');
	        } else {
	            $dataSet.data('limit', res.count);
	            count = res.count;
	            $body.embed(res.embed, true);
	        }
    	}
    );
});