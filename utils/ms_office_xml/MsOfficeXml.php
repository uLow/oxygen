<?
namespace oxygen\utils\ms_office_xml;

    class MsOfficeXml {

        public static function generateXML($tpl, $data=array()) {
            $tpl = preg_replace("/[^-_a-z0-9]+/i", "", $tpl);
            $tplPath = dirname(__FILE__).DIRECTORY_SEPARATOR."tpl".DIRECTORY_SEPARATOR.$tpl.".xml";
            //return $tplPath;
            if(file_exists($tplPath)){
                $xml = file_get_contents($tplPath);
                foreach($data as $k=>$v){
                    $xml = str_replace("{".$k."}", $v, $xml);
                }
                //ob_end_clean();
                /*header('Pragma: public'); // required
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename="test.xml"');*/
                return $xml;
                exit;
            }else{
                throw new \Exception('File "'.$tpl.'.xml" not found');
            }
        }
    }
?>