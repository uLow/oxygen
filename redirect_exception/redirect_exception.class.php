<?
	class Oxygen_RedirectException extends Exception {
        public $url = '';
        public function __construct($url)
        {
            $this->url = $url;
        }
    }