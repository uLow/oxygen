        public function <?=$this->nameFor('put')?>($tpl='short', $args=array()) {
        	array_unshift($args, self::$field_<?=$this->name?>[$this]);
        	self::$field_<?=$this->name?>->put_($tpl, $args);
        }  

        public function _<?=$this->nameFor('get')?>($tpl='short', $args=array()) {
            array_unshift($args, self::$field_<?=$this->name?>[$this]);
            return self::$field_<?=$this->name?>->get_($tpl, $args);
        } 

        public function <?=$this->nameFor('ext')?>($args=array()) {
            if(!is_array($args)){
                $args = (array)$args;
            }
        	array_unshift($args, $this['<?=$this->name?>']);
        	return self::$field_<?=$this->name?>->get_('extended_field', $args);
        }        

        public function <?=$this->nameFor('get')?>() {
            return self::$field_<?=$this->name?>[$this];
        }
