<div class="<?=$this->getTemplateClass()?>">
<?/*<pre style="width:400px;font-size:10px">
<?print_r($this->scope->SESSION->get('oxygen-flash-messages',array()));
$this->scope->SESSION['oxygen-flash-messages']=array()?>
</pre>*/?>
<h1>Error: <?=$this->getName()?></h1>
<?/*$this->scope->SESSION['Exception_::last_exception'] = */$this->put_details()?>
<?$this->put_stack_trace()?>
</div>
<?if($this->previous):?>
<?$this->previous->put_view()?>
<?endif?>
