$name = $this.data('name');
$sortable = $this.find("ul.sortable");
$items = $this.find("ul.sortable.itemsSection");
$picker = $this.find("ul.sortable.pickerSection");
$sortable.sortable({
    connectWith:'.sortable', 
    placeholder: 'sortablePlaceholder', 
    tolerance: 'pointer',
});
$items.sortable({
    receive: function(event, ui) {
        var entityClass = ui.item.data('class');
        var key = ui.item.data('key');
        var section = $(this).data('section');
        $this.remote('componentRequest',{
            component: $name,
            method: 'Add',
            args: {
                section: section,
                key: key,
                entityClass: entityClass
            }
        },function(err, res){
            if (err) {
                $(ui.sender).sortable('cancel');
                o.flash(err);
            } else {
            }
        })
    },
    remove: function(event, ui) {
        var entityClass = ui.item.data('class');
        var key = ui.item.data('key');
        var section = $(this).data('section');
        $this.remote('componentRequest',{
            component: $name,
            method: 'Delete',
            args: {
                section: section,
                key: key,
                entityClass: entityClass
            }
        },function(err, res){
            if (err) {
                $(ui.sender).sortable('cancel');
                o.flash(err);
            } else {
            }
        })
    }
});

$sortable.mousedown(function(){
		$border = $sortable.css('border');
		$paddingBottom = $sortable.css('padding-bottom');
        $sortable.css({paddingBottom: '20px', border: '1px dashed red'});
    }).mouseup(function(){
        $sortable.css({paddingBottom: $paddingBottom, border: $border});
    });