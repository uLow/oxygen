<?o('li.collapsed')?>
<?if($this->scope->auth->hasRole("admin")){?>
<a href="javascript:void(0)" id="reflectDatabase" class="title"><?=$this->_("reflect_database")?></a>
<?}?>