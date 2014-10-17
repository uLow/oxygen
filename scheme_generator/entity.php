<?$model = $this->getTableBones($args[0], $args[1])?>
<?//$relations = $this->getRelations($args[0])?>
<?//o('div.class[style="float: left; min-height: 240px; border: 1px solid #B7B9AC; border-radius: 8px; box-shadow: 2px 2px 4px rgba(100,100,100,0.4); margin: 4px; padding: 8px;"]#'.$model['class'].'')?>
<div id="<?=$model['class']?>" class="class">
    <h3 class="className" data-key="class_name"><?=$model['class']?></h3>
    <table class="paramsTable" id="paramsOf<?=$model['class']?>">
        <tr>
            <th>source</th>
            <td class="editable source" data-key="source" data-class-name="<?=$model['class']?>"><?=$model['table_name']?></td>
        </tr>
        <tr>
            <th>key</th>
            <td class="editable key" data-key="key" data-class-name="<?=$model['class']?>"><?=$model['key']?></td>
        </tr>
        <tr>
            <th>pattern</th>
            <td class="editable pattern" data-key="pattern" data-class-name="<?=$model['class']?>">{<?=$model['key']?>:int}</td>
        </tr>
        <tr>
            <th>string</th>
            <td class="editable string" data-key="string" data-class-name="<?=$model['class']?>">---</td>
        </tr>
        <tr>
            <th onclick="$(this).parent().find('.fieldsContainer').toggle()">fields</th>
            <td data-key="fields">
                <div class="fieldsContainer" style="height: 140px; overflow-y: scroll; padding-right: 24px;">
                    <table>
                        <tr>
                            <td colspan="3" align="center">
                                <a class="addField" href="javascript:void(0)" alt="Add field" title="Add field" data-class-name="<?=$model['class']?>">
                                    <img src="/oxygen/lib/silk-icons/icons/add.png">
                                </a>
                            </td>
                        </tr>
                        <?foreach($model['fields'] as $fieldName=>$field){?>
                        <tr>
                            <td><?$this->put_button('removeField','Remove field','delete')?></td>
                            
                        	<?if($model['key'] == $fieldName){?>
                        	<td class="editable fieldKey" data-readonly="1" style="font-weight: bold" data-class-name="<?=$model['class']?>"><?=$model['key']?></td>
                        	<?}else{?>
                        	<td class="editable fieldKey" data-readonly="0" data-class-name="<?=$model['class']?>"><?=$fieldName?></td>
                        	<?}?>

                        	<td data-key="type">
                                <select class="fieldsTypesSelect type">
                                    <option value="integer"<?if($field['type'] == 'integer'){?> selected="selected"<?}?>>integer</option>
                                    <option value="string"<?if($field['type'] == 'string'){?> selected="selected"<?}?>>string</option>
                                    <option value="unixtime"<?if($field['type'] == 'unixtime'){?> selected="selected"<?}?>>unixtime</option>
                                    <option value="minor"<?if($field['type'] == 'minor'){?> selected="selected"<?}?>>minor</option>
                                    <option value="object"<?if($field['type'] == 'object'){?> selected="selected"<?}?>>object</option>
                                    <option value="password"<?if($field['type'] == 'password'){?> selected="selected"<?}?>>password</option>
                                    <option value="set"<?if($field['type'] == 'set'){?> selected="selected"<?}?>>set</option>
                                    <option value="ip"<?if($field['type'] == 'ip'){?> selected="selected"<?}?>>ip</option>
                                    <option value="json"<?if($field['type'] == 'json'){?> selected="selected"<?}?>>json</option>
                                    <option value="text"<?if($field['type'] == 'text'){?> selected="selected"<?}?>>text</option>
                                    <option value="double"<?if($field['type'] == 'double'){?> selected="selected"<?}?>>double</option>
                                    <option value="collection"<?if($field['type'] == 'collection'){?> selected="selected"<?}?>>collection</option>
                                    <option value="cross"<?if($field['type'] == 'cross'){?> selected="selected"<?}?>>cross</option>
                                </select>
                            </td>
                        </tr>
                        <?}?>
                    </table>
                </div>
            </td>
        </tr>
        <?if(false && count($relations) > 0){ // TODO: relation fields?>
        <tr>    
            <th onclick="$(this).parent().find('.relations').toggle()">relations</th>
            <td id="relations<?=$model['class']?>" class="relations main" data-key="relations" style="display: none;">
                <?foreach($relations as $id=>$relation){?>
                <table class="relationTable" data-key="<?=$relation['host']['field']?>" style="margin-bottom: 12px;">
                    <tr>
                        <td style="font-weight: bold">key</td>
                        <td data-key="key"><?=$relation['host']['field']?></td>
                        <td rowspan="5"><?$this->put_button('removeRelation','Remove relation','delete')?></td>
                    </tr>
                    <tr>
                        <td>class</td>
                        <td data-key="class"><?=$relation['guest']['class']?></td>
                    </tr>
                    <tr>
                        <td>type</td>
                        <td data-key="type">many-to-one</td>
                    </tr>
                    <tr>
                        <td>inverse</td>
                        <td data-key="inverse">
                            <span class="inverseName"><?=$relation['guest']['field']?></span>,
                            <span class="inverseType">one-to-many</span>
                        </td>
                    </tr>
                    <tr>
                        <td>join</td>
                        <td data-key="join">
                            <span class="ours"><?=$relation['host']['column']?></span>-><span class="foreign"><?=$relation['guest']['column']?></span>
                        </td>
                    </tr>
                </table>
                <?}?>
            </td>
            <td class="relations">hidden</td>
        </tr>
        <?}else{?>
        <tr style="display: none">
            <th onclick="$(this).parent().find('.relations').toggle()">relations</th>
            <td id="relations<?=$model['class']?>" class="relations main" data-key="relations" style="display: none;"></td>
            <td class="relations">hidden</td>
        </tr>
        <?}?>
    </table>
</div>