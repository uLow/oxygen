<?o('ul.items.collapsed')?>
<?if(isset($args[0])){?>
<?$this->put_nodes($args[0])?>
<?}else{?>
<img src="<?=$this->scope->assets->getIcon('ajax_loader','gif')?>">
<?}?>