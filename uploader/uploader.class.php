<?
    class Oxygen_Uploader extends Oxygen_Object{
        private $uploadPath = null;
        private $uploadPathMin = null;
        private $allowedExt = array("gif", "jpeg", "jpg", "png");
        private $allowedMime = array("image/gif","image/jpeg","image/jpg","image/pjpeg","image/x-png","image/png");
        private $maxSize = 1048576;#1MB
        private $files = array();

        public $errors = array();

        public function __complete(){
            // ja izmantot DIRECTORY_SEPARATOR tad vinsh liek \ nevis /
            //$this->uploadPathMin = DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR;
            $this->uploadPathMin = "/images/uploads/";
            $this->uploadPath = $this->scope->DOCUMENT_ROOT.$this->uploadPathMin;
        }

        public function setUploadPath($path){
            $this->uploadPath = $path.$this->uploadPathMin;
        }

        public function handleFiles(){
            foreach($this->scope->FILES as $key=>$file){
                foreach($file as $k=>$v){
                    if(is_array($v)){
                        foreach($v as $_k=>$_v){
                            $this->files[$key][$_k][$k] = $_v;
                        }
                    }else{
                        $this->files[$key][][$k] = $v;
                    }
                }
            }
            $return = array();
            foreach($this->files as $_file){
                foreach($_file as $file){
                    $return[] = $this->saveFile($file);
                }
            }
            return $return;
        }

        public function saveFile($file, $filename = ""){
            if($filename == ""){
                $filename = sha1("file".microtime().mt_rand(0,100));
            }
            $ext = preg_replace("/.*\.([a-z0-9]+)$/i", "$1", $file["name"]);
            $filename = $filename . "." .$ext;
            $filePath = $this->uploadPath . $filename;
            $filePathMin = $this->uploadPathMin . $filename;
            if($file["size"] < $this->maxSize
                && in_array($file["type"], $this->allowedMime)
                && in_array($ext, $this->allowedExt)
            ){
                if($file["error"] > 0){
                    $this->errors[] = "Return Code: " . $file["error"];
                }else{
                    if (file_exists($filePath)){
                        $this->errors[] = $filePath . " already exists.";
                    }else{
                        file_put_contents($filePath, file_get_contents($file['tmp_name']));
                        //move_uploaded_file($file["tmp_name"], $filePath);
                    }
                }
            }else{
                $this->errors[] = "Invalid file";
            }

            if(empty($this->errors)){return $filePathMin;}else{return "error";}
        }
    }