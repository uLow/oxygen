<?='<?'?>

    class <?=$args['className'] ?> extends Oxygen_Common_Form {
        public function configure($x) {
            <?foreach($this->fields as $f):?>
                <?$f->put_config()?>
                
            <?endforeach?>
        }

    }