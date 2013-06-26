<?foreach($args[0] as $button):?>
<?$this->put_button($button['class'],$button['name'],$button['icon'])?>
<?endforeach?>
<?=$this?>