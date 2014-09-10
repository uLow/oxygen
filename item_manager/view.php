<?o('div',array('name'=>$this->name))?>
<table width="100%">
    <tr>
        <td width="40%">
            <ul class="sections">
            <?foreach($this->data as $name => $section):?>
            <li>
                <h5 class="sectionHeader"><span><?=Oxygen_Utils_Text::humanize($name)?></span></h5>
                <ul data-section="<?=$name?>" style="min-height:5px" class="itemsSection<?if(count($section[1])):?> sortable<?endif?>">
                    <?foreach($section[0] as $item):?>
                        <li data-class="<?=get_class($item)?>" data-key="<?=htmlentities(json_encode($item[$item->__getPrimaryKey()]), ENT_QUOTES, 'UTF-8')?>"><?=$item->put_select_view($section[1])?></li>
                    <?endforeach?>
                </ul>
            </li>
            <?endforeach?>
            </ul>
        </td>
        <td width="60%" style="padding-left: 14px"><?$this->picker->put_view()?></td>
    </tr>
</table>