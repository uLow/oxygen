<?='<?'?>

    <?$this->put_warning()?>

    class <?=$args['className']?> extends Oxygen_Entity {

    private static $data_set = null;
    private static $data_source = null;

<?$sources = explode("/", $this->source);?>
    public $database = '<?=$sources[0]?>';
    public $table = '<?=$sources[1]?>';

<?foreach ($this->fields as $field):?>
    public static $field_<?=$field->name?> = null;
<?endforeach?>

    private static $fields = array();
    public static function __getFields() {
        return self::$fields;
    }

    public function __getPattern() {
        return '<?=$this->pattern?>';
    }

    public function __getPrimaryKey() {
        <?if(is_array($this->key)){?>return array('<?=implode("','", $this->key)?>');<?}else{?>return '<?=$this->key?>';<?}?>
    
    }

    public function __getField($name) {
        return self::$fields[$name];
    }

    public static function all() {
        return self::$data_source;
    }

<?if($this->string):?>
    public function __toString() {
        <?$str = preg_replace("/\{([a-z0-9]+)/e", "'{'.ucfirst('$1')", $this->string);?>
        <?$str2 = preg_replace("/_([a-z0-9])/e", "ucfirst('$1')", $str);?>
        return '<?=preg_replace('/{([A-Za-z0-9_]+)}/','\'.(($x = $this->get$1()) === "" ? "not-set" : $x).\'', $str2)?>';
    }
<?endif?>

    public static function extendedConstructor(){
        // meant to add extra fields (non-database-based) in entity
    }

    public static function __class_construct($scope) {
        self::$data_set = $scope->connection['<?=$this->source?>'];
        self::$data_set->scope->register('Row','<?=$this->schema->moduleClassName . '_Entity_' . $this->name?>');
        <?if(isset($this->yml['order'])):?>
        self::$data_source = self::$data_set->getData('_')->order(<?var_export($this->yml['order'])?>);
        <?else:?>
        self::$data_source = self::$data_set->getData('_');
        <?endif?>
<?foreach ($this->fields as $field):?>    
        self::$fields['<?=$field->name?>'] = self::$field_<?=$field->name?> = $scope-><?$field->put_new()?>;
<?endforeach?>      
        self::extendedConstructor();  
    }


<?foreach ($this->fields as $f) {?><?$f->put_code()?><?}?>

    }

    

