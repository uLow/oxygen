<?='<?'?>

# WARNING !!!
# This class has been generated by Oxygen.
# Any changes here will be overwritten on the next genertion.

<?$m = $this->model?>
class <?=$m['className']['page']?>_ extends <?=$this->parent->model['page']?> {
    public function configure($x) {
<?foreach($m['fields'] as $fieldName => $fieldDef):?>
<?switch($fieldDef['type']): case 'object': case 'collection': case 'cross' :?>
        <?/* $x['<?=$fieldName?>']-><?=$fieldDef['class']['form']?>($this->model-><?=$this[$fieldName]->model->nameFor('get')?>());*/?>
<?endswitch?>
<?endforeach?>
    }


}

<?='?>'?>