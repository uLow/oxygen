$this.dialog({
    title: $this.data('title'),
    modal: true,
    close: function() {
        o.result(false, false);
    },
    buttons: {
        "Submit" : function() {
            o.result(false, $this.find('.field').val());
            $this.dialog('close');
        },
        "Cancel" : function(){
            o.result(false, false);
            $this.dialog('close');
        }
    }
})