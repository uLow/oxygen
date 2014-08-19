<?o('div', array('title'=>$args[0]))?>
<?if(count($args)===2){?>
<?$message = $args[0]?>
<?$fieldName = $args[1]?>
<?}elseif(count($args)===3){?>
<?$message = $args[1]?>
<?$fieldName = $args[2]?>
<?}?>

<div style="font-size: 14px; margin-bottom: 8px; padding: 4px;">
    <?=$message?>
</div>
<div style="font-size: 14px; margin-bottom: 8px; padding: 4px;">
    <?=$fieldName?>
    <input type="text" class="field" value="" placeholder="Enter <?=$fieldName?>" style="border: 1px solid #88AACC;
    border-radius: 4px 4px 4px 4px;
    box-shadow: 0 0 2px #88AACC inset;
    font-family: sans-serif;
    font-size: 14px;
    margin: 0;
    padding: 4px;">
</div>