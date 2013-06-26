<?o()?>
<?$this->put_header()?>
<?/**/?><a href="javascript:void(0)" class="generate">Generate</a><?/**/?>
<?if($this->child):?>
<?$this->child->put_view()?>
<?else:?>
<?$this->put_as_tiles()?>
<?endif?>