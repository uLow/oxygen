<?='<?'?>

    <?$this->put_warning()?>

    class <?=$args['className']?> extends Oxygen_Entity {

    private static $data_set = null;
    private static $data_source = null;

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

    public function __getField($name) {
        return self::$fields[$name];
    }

    public static function all() {
        return self::$data_source;
    }

<?if($this->string):?>
    public function __toString() {
        return '<?=preg_replace('/{([A-Za-z0-9_]+)}/','\'.(($x = self::$field_$1[$this]) === "" ? "not-set" : $x).\'', $this->string)?>';
    }
<?endif?>

    public static function __class_construct($scope) {
        // HERE

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
    }


<?foreach ($this->fields as $f) {?><?$f->put_code()?><?}?>

    }

    

