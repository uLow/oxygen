<?o()?>
<a href="javascript:void(0)" class="saveSchema">Save</a>
<div class="root">
    root: <br>
    <input type="text" class="rootField" value="<?=isset($this->yml['root'])?$this->yml['root']:''?>">
</div>
<div class="database">
    databases:  <br>
    <?if(isset($this->yml['databases'])){?>
    <?foreach($this->yml['databases'] as $k=>$db){?>
        <div style="display: block; margin-left: 14px;">
            <?=$k?>: <input type="text" class="databaseField" data-alias="<?=$k?>" value="<?=$db?>">
        </div>
    <?}?>
    <?}else{?>
        <div style="display: block">
            <input type="text" class="databaseField" data-alias="default" value="">
        </div>
    <?}?>
    <a href="javascript:void(0)" class="addDatabase">Add database</a>
</div>
<div class="uses">
    uses:  <br>
    <?if(isset($this->yml['uses'])){?>
        <?foreach($this->yml['uses'] as $k=>$use){?>
            <div style="display: block; margin-left: 14px;">
                <?=$k?>: <input type="text" class="useField" data-alias="<?=$k?>" value="<?=$use?>">
            </div>
        <?}?>
    <?}?>
    <a href="javascript:void(0)" class="addUse">Add use</a>
</div>
<?$this->put_button('addClass','Add class','add', 'javascript:void(0)', 'Add class')?>
<br>
<?$this->put_button('addClassFromDB','Add class from database','add', 'javascript:void(0)', 'Add class from DB')?>
<div class="classes">
    classes: <br>
    <?if(isset($this->yml['classes'])){?>
    <?foreach($this->yml['classes'] as $className=>$schema){?>
        <div id="<?=$className?>" class="class" style="position: relative;">
            <a href="javascript:void(0)" class="removeClass" data-id="<?=$className?>" style="position: absolute; right: 12px; text-decoration: none; color: red; font-size: 12px">X</a>
            <h3 class="className" data-key="class_name"><?=$className?></h3>
            <table class="paramsTable" id="paramsOf<?=$className?>">
                <tr>
                    <th>source</th>
                    <td class="editable source" data-key="source" data-class-name="<?=$className?>"><?=isset($schema['source'])?$schema['source']:'---'?></td>
                </tr>
                <tr>    
                    <th>key</th>
                    <td class="editable key" data-key="key" data-class-name="<?=$className?>"><?=isset($schema['key'])?implode(",",(array)$schema['key']):'---'?></td>
                </tr>
                <tr>    
                    <th>pattern</th>
                    <td class="editable pattern" data-key="pattern" data-class-name="<?=$className?>"><?=isset($schema['pattern'])?implode("-",(array)$schema['pattern']):'---'?></td>
                </tr>
                <tr>    
                    <th>string</th>
                    <td class="editable string" data-key="string" data-class-name="<?=$className?>"><?=isset($schema['string'])?$schema['string']:'---'?></td>
                </tr>
                <tr>    
                    <th onclick="$(this).parent().find('.fieldsContainer').toggle()">fields</th>
                    <td data-key="fields">
                        <span class="fieldsContainer">hidden</span>
                        <div class="fieldsContainer" style="height: 140px; overflow-y: scroll; padding-right: 24px; display: none">
                            <table>
                            <tr>
                                <td colspan="3" align="center">
                                    <a class="addField" href="javascript:void(0)" alt="Add field" title="Add field" data-class-name="<?=$className?>">
                                        <img src="/oxygen/lib/silk-icons/icons/add.png">
                                    </a>
                                </td>
                            </tr>
                            <?foreach($schema['fields'] as $fieldKey=>$fieldValues){?>
                                <tr>
                                    <td><?$this->put_button('removeField','Remove field','delete')?></td>
                                    <td class="editable fieldKey" <?if(isset($fieldValues['readonly'])){?>data-readonly="1" style="font-weight: bold"<?}else{?>data-readonly="0"<?}?> data-class-name="<?=$className?>"><?=$fieldKey?></td>
                                    <?$fieldCount=0?>
                                    <?foreach($fieldValues as $fvk=>$fvv){?>
                                        <?if($fvk!='readonly'){?>
                                        <td data-key="<?=$fvk?>">
                                            <select class="fieldsTypesSelect <?=$fvk?>">
                                                <?foreach($this->field_types as $ft){?>
                                                <option value="<?=$ft?>" <?if($ft == $fvv){?>selected="selected"<?}?>><?=$ft?></option>
                                                <?}?>
                                            </select>
                                        </td>
                                        <?$fieldCount++?>
                                        <?}?>
                                    <?}?>
                                    <?if($fieldCount==0){?>
                                    <td data-key="<?=$fvk?>">
                                        <select class="fieldsTypesSelect <?=$fvk?>">
                                            <?foreach($this->field_types as $k=>$ft){?>
                                            <option value="<?=$ft?>"><?=$ft?></option>
                                            <?}?>
                                        </select>
                                    </td>
                                    <?}?>
                                </tr>
                            <?}?>
                            </table>
                        </div>
                    </td>
                </tr>
                <?if(isset($schema['relations'])){?>
                <tr>    
                    <th onclick="$(this).parent().find('.relations').toggle()">relations</th>
                    <td id="relations<?=$className?>" class="relations main" data-key="relations" style="display: none;">
                        <?foreach($schema['relations'] as $fieldKey=>$fieldValues){?>
                        <table class="relationTable" data-key="<?=$fieldKey?>" style="margin-bottom: 12px;">
                            <tr>
                                <td style="font-weight: bold">key</td>
                                <td data-key="key"><?=$fieldKey?></td>
                                <td rowspan="5"><?$this->put_button('removeRelation','Remove relation','delete')?></td>
                            </tr>
                            <tr>
                                <td>class</td>
                                <td data-key="class"><?=$fieldValues['class']?></td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td data-key="type"><?=$fieldValues['type']?></td>
                            </tr>
                            <tr>
                                <td>inverse</td>
                                <td data-key="inverse">
                                    <span class="inverseName"><?=$fieldValues['inverse']['name']?></span>,
                                    <span class="inverseType"><?=$fieldValues['inverse']['type']?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>join</td>
                                <td data-key="join">
                                    <?foreach($fieldValues['join'] as $foreign=>$ours){?>
                                    <span class="ours"><?=$ours?></span>-><span class="foreign"><?=$foreign?></span>
                                    <?}?>
                                </td>
                            </tr>
                        </table>
                        <?}?>
                    </td>
                    <td class="relations">hidden</td>
                </tr>
                <?}else{?>
                <tr style="display: none;">
                    <th onclick="$(this).parent().find('.relations').toggle()">relations</th>
                    <td id="relations<?=$className?>" class="relations main" data-key="relations" style="display: none;"></td>
                    <td class="relations">hidden</td>
                </tr>
                <?}?>

                <?/*<tr>
                    <td colspan="3">
                        <a href="javascript:void(0)" class="addRelation" style="font-size: 12px;">Add relation to foreign scheme</a>
                    </td>
                </tr>*/?>
            </table>
        </div>
    <?}?>
    <?}?>
</div>