<?$str = array()?>
<?$langPack = $this->getLangArray()?>
<?foreach($langPack as $k=>$v){?>
	<?$str[] = "'$k':'$v'"?>
<?}?>
<?o("div", array("lang"=>$str))?>