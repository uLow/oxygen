<?=get_class($this)?>(
            '<?=$this->owner->name?>','<?=$this->name?>',
 <?=preg_replace('/^/m','            ',var_export($this->yaml,true))?>  
        )