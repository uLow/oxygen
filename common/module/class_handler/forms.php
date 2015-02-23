<?='<?php'?>

<?$this->put_warning()?>

namespace cache\<?=$args['namespace']?>;
use oxygen\common\forms\Forms;
    class <?=$args['className'] ?> extends Forms {
        public function __construct($model = '*') {
            if($model === '*') {
                $model = \<?=\oxygen\utils\text\Text::ns($this->getClassFor('Entity'))?>::all();
            }
            parent::__construct($model);
        }

        public function getListFields() {
            return $model = \<?=\oxygen\utils\text\Text::ns($this->getClassFor('Entity'))?>::__getFields();
        }

        public function __toString() {
            //return '<?=$this->getCollectionName()?>';
            return $this->_("<?=$this->getTranslateKey()?>");
        }

        public function getIcon() {
            return '<?=$this->getIcon()?>';
        }

        public function configure($x) {
            $x['<?=$this->pattern?>']->{'<?=addslashes(\oxygen\utils\text\Text::ns($this->getClassFor('Form')))?>'}($this->getModel());
        }
    }