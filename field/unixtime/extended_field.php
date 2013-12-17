<?if(isset($args[1])){?>
<?=date($args[1], $args[0])?>
<?}else{?>
<?=$this->wrap($args[0])?>
<?}?>