<?
namespace oxygen\redirect_exception;
	use Exception;

    class RedirectException extends Exception {
        public $url = '';
        public function __construct($url)
        {
            $this->url = $url;
        }
    }