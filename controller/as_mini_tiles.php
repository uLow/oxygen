<?if($this->child):?>
<?$this->child->put()?>
<?else:?>
<?foreach($this as $child):?>
<?$child->put_as_mini_tile()?>
<?endforeach?>
<?endif?>