<?if(strlen($args[0])>300){?>
	<?=substr(nl2br(htmlentities($args[0], ENT_QUOTES, 'UTF-8')), 0, 300)?> [..]
<?}else{?>
	<?=nl2br(htmlentities($args[0], ENT_QUOTES, 'UTF-8'))?>
<?}?>