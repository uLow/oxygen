<?o('li', $this->getModelData())?>
<?if(count($this)>0){?>
<a href="javascript:void(0)" class="hit expand"><?$this->put_icon()?></a>
<?}else{?>
<?$this->put_icon()?>
<?}?>
<?$this->put_node_specific()?>
<?$this->put_node_list()?>
