<?
namespace oxygen\utils\yaml;
    class YAML {
        public function load($input) {
            return sfYaml::load($input);
        }
        public function __class_construct($scope) {
        	$scope->lib->load('yaml-php/lib/sfYaml.php');
        }
    }


?>