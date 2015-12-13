<?php
 class ShearPhoto {
    public $erro = false;
    public $imagesuffix = null;
    public $filename;

     public function __construct($filename){
         $this->filename = $filename;
     }
     /**
      * 根据旋转角度生成图片资源
      */
    protected function rotate($src, $R) {
        $arr = array(
            -90, 
            -180, 
            -270
        );
        if (in_array($R, $arr)) {
			 $rotatesrc = imagerotate($src, $R, 0);
            imagedestroy($src);
        } else {
            return $src;
        }
      return $rotatesrc;
    }

     /**
      * 验证参数
      */
    public function run($JSconfig, $PHPconfig) {
         if (!isset($JSconfig["url"]) || !isset($JSconfig["R"]) || !isset($JSconfig["X"]) || !isset($JSconfig["Y"]) || !isset($JSconfig["IW"]) || !isset($JSconfig["IH"]) || !isset($JSconfig["P"]) || !isset($JSconfig["FW"]) || !isset($JSconfig["FH"]) ) {
         $this->erro = "服务端接收到的数据缺少参数";
                return false;
         }
        foreach ($JSconfig as $k => $v) { 
            if ($k !== "url") {
				if (!is_numeric($v)) {
                    $this->erro = "传递的参数有误";
                    return false;
                }
            }
        } //验证是否为字除了url
        if ($PHPconfig["proportional"] !== $JSconfig["P"]) {
            $this->erro = "JS设置的比例和PHP设置不一致";
            return false;
        }
        list($w, $h, $type) = getimagesize($JSconfig["url"]); //验证是否真图片！
        $this->imagesuffix = image_type_to_extension($type);

        $type_array = array(
            ".jpeg",
            ".gif",
            ".png",
            ".jpg"
        );
        if (!in_array(strtolower( $this->imagesuffix) , $type_array)) {
            $this->erro = "无法读取图片";
            return false;
        }  
		if($JSconfig["R"]==-90 || $JSconfig["R"]==-270){ list($w,$h)= array($h,$w);}
		return $this->createshear($w,$h,$type,$JSconfig);
    }

    protected function createshear($w,$h,$type,$JSconfig) {
        switch ($type) {
            case 1:  
                $src = @imagecreatefromgif($JSconfig["url"]);
                break;

            case 2:  
                $src = @imagecreatefromjpeg($JSconfig["url"]);
				
                break;

            case 3:  
                $src = @imagecreatefrompng($JSconfig["url"]);
                break;

            default:
                return false;
                break;
        }
        $src = $this->rotate($src, $JSconfig["R"]);
        $dest = imagecreatetruecolor($JSconfig["IW"], $JSconfig["IH"]);
        imagecopy($dest, $src, 0, 0, $JSconfig["X"],  $JSconfig["Y"], $w, $h);
        $tmp_name = ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . $this->filename.$this->imagesuffix;
        switch ($type) {
            case 1:
                $result = imagegif($dest,$tmp_name);
                break;

            case 2:
                $result = imagejpeg($dest,$tmp_name);
                break;

            case 3:
                $result = imagepng($dest,$tmp_name);
                break;

            default:
                return false;
                break;
        }
        return $result?$tmp_name:false;
    }

}
?>