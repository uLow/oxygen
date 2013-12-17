<?='<?'?>

    class <?=$args['className'] ?> extends Oxygen_Common_Forms {
        public function __construct($model = '*') {
            if($model === '*') {
                $model = <?=$this->getClassFor('Entity')?>::all();
            }
            parent::__construct($model);
        }

        public function getListFields() {
            return $model = <?=$this->getClassFor('Entity')?>::__getFields();
        }

        public function __toString() {
            //return '<?=$this->getCollectionName()?>';
            return $this->_("<?=$this->getTranslateKey()?>");
        }

        public function getIcon() {
            return '<?=$this->getIcon()?>';
        }

        public function configure($x) {
            $x['<?=$this->pattern?>']-><?=$this->getClassFor('Form')?>($this->getModel());
        }
    }