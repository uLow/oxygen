<?foreach($args[0] as $ri):?>
<li data-class="<?=get_class($ri)?>" data-key="<?=htmlentities(json_encode($ri->__key()), ENT_QUOTES, 'UTF-8')?>"><?$ri->put_select_view(array())?></li>
<?endforeach?>