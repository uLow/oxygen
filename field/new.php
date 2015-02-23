{'<?=addslashes(get_class($this))?>'}(
            '<?=addslashes($args[0]['namespace'].'\\'.$this->owner->name)?>','<?=$this->name?>',
 <?=preg_replace('/^/m','            ',var_export($this->yaml,true))?>  
        )