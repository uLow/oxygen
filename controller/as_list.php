<?if($this->child){?>
<?$this->child->put()?>
<?}else{?>
<h3><?=ucfirst($this)?> (<?=count($this)?>)</h3>
<ul class="oxygen-inner-list">
<?foreach($this as $child){?>
<?$child->put_as_list_item()?>
<?}?>
</ul>
<?}?>