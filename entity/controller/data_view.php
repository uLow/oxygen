<?if($this->child):?>
<?$this->child->put_view()?>
<?else:?>
<?o()?>
<?$this->put_header()?>
<?$this->put_data_content()?>
<?endif?>