$this.find('.addDatabase').live('click', function(){
    var dbAlias = window.prompt('Enter database alias', 'db_alias');
    var dbName = window.prompt('Enter database name', 'db_name');
    var $database = $('<div style="display: block; margin-left: 14px;">'+dbAlias+': <input type="text" class="databaseField" data-alias="'+dbAlias+'" value="'+dbName+'"></div>');
    $(this).before($database);
});
$this.find('.addUse').live('click', function(){
    var useAlias = window.prompt('Enter use alias', 'use_alias');
    var useName = window.prompt('Enter used class name', 'use_name');
    var $use = $('<div style="display: block; margin-left: 14px;">'+useAlias+': <input type="text" class="useField" data-alias="'+useAlias+'" value="'+useName+'"></div>');
    $(this).before($use);
});

$this.find('.addRelation').live('click', function(){
    var useAlias = window.prompt('Enter use alias', 'use_alias');
    var useClass = window.prompt('Enter used entity name', 'use_name');
    var $use = $('<div style="display: block; margin-left: 14px;">'+useAlias+': <input type="text" class="useField" data-alias="'+useAlias+'" value="'+useName+'"></div>');
    $(this).parent().parent().before($use);
});

$this.find('.removeClass').live('click', function(){
    $('#'+$(this).data('id')).remove();
});

$this.find('.saveSchema').live('click', function(){
    var root = $this.find('.rootField').val();
    var databases = {};
    $this.find('.databaseField').each(function(){
        databases[$(this).data('alias')] = $(this).val();
    });
    var uses = {};
    $this.find('.useField').each(function(){
        uses[$(this).data('alias')] = $(this).val();
    });

    var classes = {};
    $this.find('.class').each(function(){
        var class_name = $(this).find('.className').text();
        var source = $(this).find('.source').text();
        var key = $(this).find('.key').text();
        if(key.split(',').length>1){
            key = key.split(',');
        }
        var pattern = $(this).find('.pattern').text();
        /*if(pattern.split('-').length>1){
            pattern = pattern.split('-');
        }*/

        var string = $(this).find('.string').text();
        var fields = {};
        $(this).find('.fieldKey').each(function(){
            var field_key = $(this).text();
            fields[field_key] = {
                type: $(this).parent().find('.fieldsTypesSelect').val()
            };
            if($(this).data('readonly')==1){
                fields[field_key]['readonly'] = true;
            }
        });

        var relations = {};
        var relCount = 0;
        $(this).find('.relationTable').each(function(){
            var relation_key = $(this).data('key');
            var relation_class = $(this).find('[data-key=class]').text();
            var relation_type = $(this).find('[data-key=type]').text();
            var relation_inverse = {
                name: $(this).find('.inverseName').text(),
                type: $(this).find('.inverseType').text()
            };
            var $join = $(this).find('[data-key=join]');
            var join_key = $join.find('.foreign').text();
            var join_value = $join.find('.ours').text();
            var join = {};
            join[join_key] = join_value;

            relations[relation_key] = {
                'class': relation_class,
                'type': relation_type,
                'inverse': relation_inverse,
                'join': join
            };
            relCount++;
        });

        classes[class_name] = {
            source: source,
            key: key,
            pattern: pattern,
            string: string,
            fields: fields
        };
        if($(this).find('.relations').children().length>0){
            classes[class_name]['relations'] = relations;
        }
    });


    var schema = {
        root: root,
        uses: uses,
        databases: databases,
        classes: classes
    };
    $this.remote('saveSchema', {schema:schema}, function(err, res){
        if(err){
            console.log(err);
        }else{
            o.flash('Generated: '+res);
            $this.refresh();
        }
    });
    console.log(schema);
});

$this.find('.editable').live('click', function(){
    //$(this).attr('contenteditable', true);
    var oldValue = $(this).text();
    var value = window.prompt('Enter new value', oldValue);
    if(value && value != null && value != undefined){
        $(this).text(value);
    }
});

$this.find('.removeField').live('click', function(){
    $(this).parent().parent().remove();
});

$this.find('.removeRelation').live('click', function(){
    $(this).parent().parent().parent().parent().remove();
});

$this.find('.fieldsTypesSelect').live('change', function(){
    $(this).data('value', $(this).val());
});

$this.find('.addField').live('click', function(){
    var key = window.prompt('Enter new key', 'key');
    var $select_box = $this.find('.fieldsTypesSelect').eq(0).clone();
    var tr = $('<tr></tr>');
    var td1 = $('<td><a class="removeField" href="javascript:void(0)" alt="Remove field" title="Remove field" data-class-name="'+$(this).data('class-name')+'"><img src="/oxygen/lib/silk-icons/icons/delete.png"></a></td>');
    var td2 = $('<td class="editable fieldKey" data-readonly="0" data-class-name="'+$(this).data('class-name')+'">'+key+'</td>');
    var td3 = $('<td data-key="'+key+'"></td>');
    td3.append($select_box);
    tr.append(td1).append(td2).append(td3);
    $(this).parent().parent().parent().append(tr);
    dragAndDrops();
});

function dragAndDrops(){
    $this.find(".fieldKey").draggable({
        cursor: 'move',
        opacity: 0.4,
        revert: true,
        helper: 'clone'
    });

    $this.find('.string').droppable({
        accept: ".fieldKey",
        hoverClass: "drop-hover",
        drop: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') == $ui.data('class-name')){
                if($(this).text()=='---'){
                    $(this).text('{'+$ui.text()+'}');
                }else{
                    $(this).text($(this).text()+'{'+$ui.text()+'}');
                }
            }else{
                if($(this).data('class-name') != $ui.data('class-name')){
                    $(this).css({'background-color':''});
                }
            }
        },
        over: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') != $ui.data('class-name')){
                $(this).css({'background-color':'#EA6F6F'});
            }
        },
        out: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') != $ui.data('class-name')){
                $(this).css({'background-color':''});
            }
        }
    });

    $this.find(".fieldKey").droppable({
        accept: ".fieldKey",
        hoverClass: "drop-hover",
        drop: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') != $ui.data('class-name')){
                var foreign = $(this).text();
                var ours = $ui.text();
                var newField = window.prompt('Enter new field name', 'new_field');
                var newInverseField = window.prompt('Enter new inverse field name', 'new_inverse_field');
                var className = $(this).data('class-name');
                var $table = $('<table class="relationTable" data-key="'+newField+'" style="margin-bottom: 12px;"><tr><td style="font-weight: bold">key</td><td data-key="key">'+newField+'</td><td rowspan="5"><a class="removeRelation" href="javascript:void(0)" alt="Remove relation" title="Remove relation"><img src="/oxygen/lib/silk-icons/icons/delete.png"></a></td></tr><tr><td>class</td><td data-key="class">'+className+'</td></tr><tr><td>type</td><td data-key="type">many-to-one</td></tr><tr><td>inverse</td><td data-key="inverse"><span class="inverseName">'+newInverseField+'</span>,<span class="inverseType">one-to-many</span></td></tr><tr><td>join</td><td data-key="join"><span class="ours">'+ours+'</span>-><span class="foreign">'+foreign+'</span></td></tr></table>');
                var $relations = $this.find('#relations'+$ui.data('class-name'));
                $relations.append($table);
                $relations.parent().show();

                var $select_box = $this.find('.fieldsTypesSelect').eq(0).clone();
                $select_box.val('object');
                var tr = $('<tr></tr>');
                var td1 = $('<td><a class="removeField" href="javascript:void(0)" alt="Remove field" title="Remove field" data-class-name="'+className+'"><img src="/oxygen/lib/silk-icons/icons/delete.png"></a></td>');
                var td2 = $('<td class="editable fieldKey" data-readonly="0">'+newField+'</td>');
                var td3 = $('<td data-key="'+newField+'"></td>');
                td3.append($select_box);
                tr.append(td1).append(td2).append(td3);
                $ui.parent().parent().after(tr);
                dragAndDrops();
            }
        },
        over: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') == $ui.data('class-name')){
                $(this).css({'background-color':'#EA6F6F'});
            }
        },
        out: function(e, ui){
            var $ui = $(ui.helper.context);
            if($(this).data('class-name') == $ui.data('class-name')){
                $(this).css({'background-color':''});
            }
        }
    });
}

$this.find('.addClass').live('click', function(){
    var className = window.prompt('Enter new class name', 'class_name');
    var dg_className = className.replace(/([a-z])([A-Z])/g,"$1_$2").toLowerCase();
    var source = dg_className;//window.prompt('Enter new source', dg_className);
    var key = source+'_id';//window.prompt('Enter primary key', source+'_id');
    var pattern = "{"+key+":int}";
    $this.find('.classes').append('<div id="'+className+'" class="class" style="float: left; min-height: 240px; border: 1px solid #B7B9AC; border-radius: 8px; box-shadow: 2px 2px 4px rgba(100,100,100,0.4); margin: 4px; padding: 8px;"><h3 class="className" data-key="class_name">'+className+'</h3><table class="paramsTable" id="paramsOf'+className+'"><tr><th>source</th><td class="editable source" data-key="source" data-class-name="'+className+'">'+source+'</td></tr><tr><th>key</th><td class="editable key" data-key="key" data-class-name="'+className+'">'+key+'</td></tr><tr><th>pattern</th><td class="editable pattern" data-key="pattern" data-class-name="'+className+'">'+pattern+'</td></tr><tr><th>string</th><td class="editable string" data-key="string" data-class-name="'+className+'">---</td></tr><tr><th onclick="$(this).parent().find(\'.fieldsContainer\').toggle()">fields</th><td data-key="fields"><span class="fieldsContainer">hidden</span><div class="fieldsContainer" style="height: 140px; overflow-y: scroll; padding-right: 24px;"><table><tr><td colspan="3" align="center"><a class="addField" href="javascript:void(0)" alt="Add field" title="Add field" data-class-name="'+className+'"><img src="/oxygen/lib/silk-icons/icons/add.png"></a></td></tr><tr><td><a class="removeField" href="javascript:void(0)" alt="Remove field" title="Remove field"><img src="/oxygen/lib/silk-icons/icons/delete.png"></a></td><td class="editable fieldKey" data-readonly="1" style="font-weight: bold" data-class-name="'+className+'">'+key+'</td><td><select class="fieldsTypesSelect type"><option value="integer" selected="selected">integer</option><option value="string">string</option><option value="unixtime">unixtime</option><option value="minor">minor</option><option value="object">object</option><option value="password">password</option><option value="set">set</option><option value="ip">ip</option><option value="json">json</option><option value="text">text</option><option value="double">double</option><option value="collection">collection</option><option value="cross">cross</option></select></td></tr></table></div></td></tr><tr style="display: none"><th onclick="$(this).parent().find(\'.relations\').toggle()">relations</th><td id="relations'+className+'" class="relations main" data-key="relations" style="display: none;"></td><td class="relations">hidden</td></tr></table></div>');
    dragAndDrops();
});

$this.find('.addClassFromDB').live('click', function(){
    var dbName = window.prompt('Enter database', 'db_name');
    var tableName = window.prompt('Enter table name', 'table_name');
    $this.remote(
        'buildEntityFromDB',
        {
            db: dbName,
            table: tableName
        },
        function(err, res){
            if(err){
                console.log(err);
            }else{
                $this.find('.classes').append(res.body);
            }
        }
    );
    dragAndDrops();
});

dragAndDrops();