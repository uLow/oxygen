<?='<?php'?>

<?$this->put_warning()?>

namespace cache\<?=$args['namespace']?>;
use oxygen\entity\Entity;

    class <?=$args['className']?> extends Entity {

    /* @var \oxygen\sql\table\Table $data_set */
    private static $data_set = null;
    /* @var \oxygen\sql\data_set\DataSet $data_source */
    private static $data_source = null;

<?$sources = explode("/", $this->source);?>
    public $database = '<?=$sources[0]?>';
    public $table = '<?=$sources[1]?>';

<?foreach ($this->fields as $field):?>
    /* @var \<?=get_class($field)?> $field_<?=$field->name?> */
    public static $field_<?=$field->name?> = null;
<?endforeach?>

private static $fields = array();
    public static function __getFields() {
        return self::$fields;
    }

    public function __getPattern() {
        return '<?=$this->pattern?>';
    }

    public function __getPrimaryKey($pattern = false) {
        if($pattern === false){
            <?if(is_array($this->key)){?>return array('<?=implode("','", $this->key)?>');<?}else{?>return '<?=$this->key?>';<?}?>

        }else{
            return '<?=$this->pattern?>';
        }
    }

    public function __getField($name) {
        return self::$fields[$name];
    }

    public static function all() {
        return self::$data_source;
    }

<?if($this->string):?>
    public function __toString() {<?
        $str = preg_replace_callback("/\{([a-z0-9]+)/", function($m){ return '{'.ucfirst($m[1]);}, $this->string);
        $str2 = preg_replace_callback("/_([a-z0-9])/", function($m){ return ucfirst($m[1]);}, $str);
    ?>

        return '<?=preg_replace('/{([A-Za-z0-9_]+)}/','\'.(($x = $this->get$1()) === "" ? "not-set" : $x).\'', $str2)?>';
    }
<?endif?>

    public static function extendedConstructor(){
        // meant to add extra fields (non-database-based) in entity
    }

    /**
    * @param \oxygen\scope\Scope $scope
    */
    public static function __class_construct($scope) {
        self::$data_set = $scope->connection['<?=$this->source?>'];
        self::$data_set->scope->register('Row','<?=addslashes(\oxygen\utils\text\Text::classToNamespace($this->schema->yml['namespace'] . '\\entity', $this->name).'\\'.$this->name)?>');
        <?if(isset($this->yml['order'])):?>
        self::$data_source = self::$data_set->getData('_')->order(<?var_export($this->yml['order'])?>);
        <?else:?>
        self::$data_source = self::$data_set->getData('_');
        <?endif?>
<?foreach ($this->fields as $field):?>    
        self::$fields['<?=$field->name?>'] = self::$field_<?=$field->name?> = $scope-><?$field->put_new(array('namespace'=>$args['namespace']))?>;
<?endforeach?>      
        self::extendedConstructor();  
    }


<?foreach ($this->fields as $f) {?><?$f->put_code()?><?}?>

    }

    

