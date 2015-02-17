<?
namespace oxygen\entity\collection;

    use oxygen\entity\controller\Oxygen_Entity_Controller;

    class Oxygen_Entity_Collection extends Oxygen_Entity_Controller {
        public function plural($n) {
            if($n === 1) return 'item';
            else return 'items';
        }

        public function configure($x) {
            $ks = array();
            foreach($this->model->meta['keys'][0] as $k=>$v){
                $ks[] = '{'.$k.':url}';
            };
            $key = implode('/',$ks);
            $x[$key]->Oxygen_Entity_Controller($this->model);
        }

        public function rpc_getMore($args) {
            $offset = $args->limit;

            $filter = array();
            foreach($this->getHeaders() as $header=>$data){
                $filter[] = $header." like '%".addslashes($args->filter)."%'";
            }

            $this->model = $this->model->where(implode(" or ", $filter))->slice($offset, 25);
            return array(
                'count'=>count($this->section('data')),
                'embed'=>$this->embed_table_rows($this->getHeaders())
            );
        }

        public function rpc_find($args) {
            $filter = array();
            foreach($this->getHeaders() as $header=>$data){
                $filter[] = $header." like '%".addslashes($args->search)."%'";
            }
            
            $this->model = $this->model->where(implode(" or ", $filter))->slice(0, 25);
            return array(
                'count'=>count($this->section('data')),
                'embed'=>$this->embed_table_rows($this->getHeaders())
            );
        }

        public function rpc_loadAll($args) {
            $filter = array();
            foreach($this->getHeaders() as $header=>$data){
                $filter[] = $header." like '%".addslashes($args->search)."%'";
            }
            
            $this->model = $this->model->where(implode(" or ", $filter));
            return array(
                'count'=>count($this->section('data')),
                'embed'=>$this->embed_table_rows($this->getHeaders())
            );
        }

        public function humanize($name) {
            $x = str_replace('_', ' ', $name);
            $x = preg_replace('/\s(en|ru|lv)$/','(\\1)',$x);
            $x = preg_replace('/id$/','ID',$x);
            $x = explode('.',$x);
            array_shift($x);
            return ucfirst(implode(' ', $x));
        }

        public function getHeaders() {
            $result = array();
            foreach ($this->model->meta['keys'] as $key) {
                foreach ($key as $column) {
                    $result[$column] = array(
                        'name' => $this->humanize($column),
                        'mode' => 'link'
                    );
                }
            }
            foreach($this->model->meta['select']['select'] as $column) {
                if(!isset($result[$column])) {
                    $result[$column] = array(
                        'name' => $this->humanize($column),
                        'mode' => 'show'
                    );
                }
            }
            foreach($this->model->meta['select']['update'] as $column) {
                if(isset($result[$column]) && $result[$column]['mode'] === 'show') {
                    $result[$column]['mode'] = 'edit';
                }
            }
            return $result;
        }

    }


?>