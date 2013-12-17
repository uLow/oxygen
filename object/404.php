<?o()?>
<h1 style="color: #308BC0; text-shadow: 1px 1px 1px #ccc; text-align: center"><?=$args[0]?></h1>
<hr style="border:none; border-top: 1px solid #ddd; border-bottom: 1px solid #666; box-shadow: 1px 3px 4px #ddd; margin-bottom: 12px;">
<div style="font-size: 16px">
	<img src="<?=$this->scope->lib->url('images/warning.jpg')?>" style="float:left;margin-right: -54px">
	<?=$args[1]?> 
	<?=$this->_("404return_to")?> <a href="<?=$this->scope->ROOT_URI?>"><?=$this->_("404_index_page")?></a>.
</div>