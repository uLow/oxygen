<?o('li.collapsed')?>
<?if($this->scope->auth->hasRole("admin")){?>
<a href="javascript:void(0)" id="clearCache" class="title"><?=$this->_("clear_cache")?></a>
<?}?>