<div class="oxy-content">
	<?if($this->child):?>
		<?$this->child->put()?>
	<?else:?>
		<?$this->put_as_tiles()?>
	<?endif?>
</div>