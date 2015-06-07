<?
namespace oxygen\uploader;

    use oxygen\object\Object;

    class Uploader extends Object {
        private $uploadPath = null;
        private $uploadPathMin = null;
        private $hasExtentionRestriction = true;
        private $hasMimeTypeRestriction = true;
        private $allowedExt = array("gif", "jpeg", "jpg", "png");
        private $allowedMime = array("image/gif","image/jpeg","image/jpg","image/pjpeg","image/x-png","image/png");
        private $maxSize = 21474836480;#20MB
        private $files = array();

        public $errors = array();

        public function __complete(){
            $this->uploadPathMin = "/uploads/";
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
                    $filenameParts = explode('.', $file["name"]);
                    unset($filenameParts[count($filenameParts)-1]);
                    $filename = implode('.', $filenameParts);
                    $return[] = $this->saveFile($file, $filename.'-'.substr(sha1(microtime().mt_rand(0,100)), -6));
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
                && $this->isAllowedMimeType($file["type"])
                && $this->isAllowedExtension($ext)
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

        /**
         * @param array $allowedExt
         */
        public function setAllowedExt($allowedExt)
        {
            $this->allowedExt = $allowedExt;
        }

        /**
         * @param array $allowedMime
         */
        public function setAllowedMime($allowedMime)
        {
            $this->allowedMime = $allowedMime;
        }

        /**
         * @param int $maxSize
         */
        public function setMaxSize($maxSize)
        {
            $this->maxSize = $maxSize;
        }

        /**
         * @param boolean $hasExtentionRestriction
         */
        public function setHasExtentionRestriction($hasExtentionRestriction)
        {
            $this->hasExtentionRestriction = $hasExtentionRestriction;
        }

        /**
         * @param boolean $hasMimeTypeRestriction
         */
        public function setHasMimeTypeRestriction($hasMimeTypeRestriction)
        {
            $this->hasMimeTypeRestriction = $hasMimeTypeRestriction;
        }

        /**
         * @param string $extension
         * @return boolean
         */
        public function isAllowedExtension($extension){
            return $this->hasExtentionRestriction === false || in_array($extension, $this->allowedExt);
        }

        /**
         * @param string $mime
         * @return boolean
         */
        public function isAllowedMimeType($mime){
            return $this->hasMimeTypeRestriction === false || in_array($mime, $this->allowedMime);
        }
    }