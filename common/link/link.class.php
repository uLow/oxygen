<?
namespace oxygen\common\link;

    use oxygen\controller\Oxygen_Controller;

    class Oxygen_Common_Link extends Oxygen_Controller {
        public $title = '';
        public $icon = 'bullet_green';

        public function __construct($title, $link='', $icon='bullet_green')
        {
            parent::__construct($link);
            $this->title = $title;
            $this->icon = $icon;
        }

        public function go(){
            return $this->model;
        }

        public function __toString()
        {
            return $this->title;
        }

        public function getIcon()
        {
            return $this->icon;
        }
    }