<?='<?'?>
use oxygen\common\form\Form;
    class <?=$args['className'] ?> extends Form {
        public function configure($x) {
            <?foreach($this->fields as $f):?>
                <?$f->put_config()?>
                
            <?endforeach?>
        }

    }