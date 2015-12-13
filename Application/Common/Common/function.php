<?php
/**
 * 
 * 生成一段随机码
 * @param int $length 随机码长度
 */
function GeneralRandCode($length=6){
	$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ'; //字符池
 	for($i=0;$i<$length;$i++){
  		$key.=$pattern{mt_rand(0,35)};//生成php随机数
 	}
 	return $key;
}

/**
 * 
 * 上传图片
 * @param $subName
 * @param $rootPath
 * @param $savePath
 * @param $maxSize
 * @param $exts
 */
function UploadFile($subName,$rootPath="/Attachment/",$savePath="/face/",$files="",$maxSize=8048000,$exts=array("jpg","gif","png","jpeg")){
	if(substr($rootPath,0,1) != "/"){
		$rootPath = "/".$rootPath;
	}
	if(substr($rootPath,-1) != "/"){
		$rootPath .= "/";
	}
	if(substr($savePath,0,1) != "/"){
		$savePath = "/".$savePath;
	}
	if(substr($savePath,-1) != "/"){
		$savePath .= "/";
	}
	//先上传到又拍云，再上传到本地
	//又拍云上传代码
    //访问地址：http://shq-pic.b0.upaiyun.com/Attachment/face/2015/05/5569c5d5190d6.jpg
    $upload = new \Think\Upload(
    						array(),
    						FILE_UPLOAD_TYPE,
    						array(
    							'host'=>UPYUN_HOST,
    							'username'=>UPYUN_USERNAME,
    							'password'=>UPYUN_PASSWORD,
    							'bucket'=>UPYUN_BUCKET,
    							'timeout'=>UPYUN_TIMEOUT
    						)
					    );
	$upload->maxSize   	=  $maxSize ;// 设置附件上传大小    
	$upload->exts      	=  $exts;// 设置附件上传类型  
	$upload->rootPath 	= $rootPath;
	$upload->savePath 	= $savePath; // 设置附件上传目录
	$upload->subName 	= $subName;
	$result = $upload->upload($files);
    if (!empty($result)){
		$key = array_keys($result);//得到上传文件名称
		$filename = substr($result[$key[0]]['savename'], 0,strrpos($result[$key[0]]['savename'], "."));
		//上传到本地
		$upload = new \Think\Upload(array(),
            "LOCAL",
            null);
		$upload->maxSize   	=  $maxSize ;// 设置附件上传大小    
		$upload->exts      	=  $exts;// 设置附件上传类型  
		$upload->rootPath 	= ATTACHMENT_PATH;
		$upload->savePath 	= $savePath; // 设置附件上传目录
		$upload->subName 	= $subName;
		$upload->saveName	= $filename;//设置文件名称
		$upload->upload($files);
		$return['thumbpath'] = UPYUN_URL.$result[$key[0]]['savepath'].$result[$key[0]]['savename'];
        $return['url'] = UPYUN_URL.$result[$key[0]]['savepath'].$result[$key[0]]['savename'];
		$return['error'] = true;
		$return['picurl'] =$result[$key[0]]['savepath'].$result[$key[0]]['savename'];
	}else {
		$return['thumbpath'] = SITE_ATTACHMENT_URL."/face/default.jpg";
        $return['url'] = SITE_ATTACHMENT_URL."/face/default.jpg";
		$return['picurl'] = "/face/default.jpg";
		$return['error'] = 0;
	}
	header('Content-Type:application/json; charset=utf-8');
    return $return;
	//exit(json_encode($return));
}

/**
 * 
 * 写入文件
 * @param string $filename
 * @param string $data
 * @param string $method
 * @param int $iflock
 */
function write_file($filename,$data,$method="rb+",$iflock=1){
	@touch($filename);
	$handle=@fopen($filename,$method);
	if(!$handle){
		echo "此文件不可写:$filename";
	}
	if($iflock){
		@flock($handle,LOCK_EX);
	}
	@fputs($handle,$data);
	if($method=="rb+") @ftruncate($handle,strlen($data));
	@fclose($handle);
	@chmod($filename,0777);	
	if(!is_writable($filename) ){
		return false;
	}
	return true;
}

/**
 * 
 * 写入配置缓存文件
 * @param string $filename
 * @param array  $webdbs
 */
function write_config_cache($filename,$webdbs)
{
	$table = str_replace("@", "_", $filename);
	$arrModel = explode("@", $filename);
	foreach ($arrModel as $m){
		$Model .= ucfirst($m);
	}
	$Model = M($Model);
	if( is_array($webdbs) )
	{
		foreach($webdbs AS $key=>$value)
		{
			if(is_array($value))
			{
				$webdbs[$key]=$value=implode(",",$value);
			}
			$indata[] = $key;
			//$data[$key] = $value;
			$SQL.="('$key', '$value'),";
		}
		$SQL=$SQL.";";
		$SQL=str_Replace("'),;","')",$SQL);
		$Model->where(array("ckey"=>array('in',implode(",", $indata))))->delete();
		$Model->execute("INSERT INTO `".$Model->getTablePrefix().$table."` VALUES  $SQL ");
	}
	$Cache = \Think\Cache::getInstance();
	return $Cache->set($filename,$webdbs);
}

/**
 * 
 * 读取配置缓存文件
 * @param string $filename
 */
function get_config_cache($filename){
	$table = str_replace("@", "_", $filename);
	$arrModel = explode("@", $filename);
	foreach ($arrModel as $m){
		$Model .= ucfirst($m);
	}
	$Model = M($Model);
	$Cache = \Think\Cache::getInstance();
	if (!$Cache->exists($filename)){
		$result = $Model->select();
		foreach ($result as $key => $val){
			$rs[$val['ckey']] = $val['cvalue'];
		}
	}else {
		$rs = $Cache->get($filename);
	}
	return $rs;
}

/**
 *
 * 模板字符串截取
 *$str:要截取的字符串
 *$start=0：开始位置，默认从0开始
 *$length：截取长度
 *$charset=”utf-8″：字符编码，默认UTF－8
 *$suffix=true：是否在截取后的字符后面显示省略号，默认true显示，false为不显示
    */

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
        if ($suffix && strlen($str)>$length)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if ($suffix && strlen($str)>$length)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}


// 字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4; // 随机密钥长度 取值 0-32;
	                  // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	                  // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	                  // 当此值为 0 时，则不产生随机密钥
	
	$key = md5 ( $key ? $key : UC_KEY );
	$keya = md5 ( substr ( $key, 0, 16 ) );
	$keyb = md5 ( substr ( $key, 16, 16 ) );
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';
	
	$cryptkey = $keya . md5 ( $keya . $keyc );
	$key_length = strlen ( $cryptkey );
	
	$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
	$string_length = strlen ( $string );
	
	$result = '';
	$box = range ( 0, 255 );
	
	$rndkey = array ();
	for($i = 0; $i <= 255; $i ++) {
		$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
	}
	
	for($j = $i = 0; $i < 256; $i ++) {
		$j = ($j + $box [$i] + $rndkey [$i]) % 256;
		$tmp = $box [$i];
		$box [$i] = $box [$j];
		$box [$j] = $tmp;
	}
	
	for($a = $j = $i = 0; $i < $string_length; $i ++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box [$a]) % 256;
		$tmp = $box [$a];
		$box [$a] = $box [$j];
		$box [$j] = $tmp;
		$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
	}
	
	if ($operation == 'DECODE') {
		if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
			return substr ( $result, 26 );
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
	}
}

/**
 *计算某个经纬度的周围某段距离的正方形的四个点
 *
 *@param lng float 经度
 *@param lat float 纬度
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 *@return array 正方形的四个点的经纬度坐标
 */
function returnSquarePoint($lng, $lat,$distance = 0.5){
    define(EARTH_RADIUS, 6371);//地球半径，平均半径为6371km
    $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
    $dlat = $distance/EARTH_RADIUS;
    $dlat = rad2deg($dlat);
    return array(
        'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
        'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
        'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
        'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
    );
}
/**
 * @desc 根据两点间的经纬度计算距离
 * @param float $lat 纬度值
 * @param float $lng 经度值
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000; //approximate radius of earth in meters

    /*
    Convert these degrees to radians
    to work with the formula
    */

    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    /*
    Using the
    Haversine formula
    calculate the distance
    */
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;

    return round($calculatedDistance);
}

/**
 * 根据传入的时间戳，获取历史时间
 * 这个函数的另外一种用法，直接在模板{{时间戳|time}}
 * @access    传入的时间戳
 */
function beforeTime($time)
{
    $limit = time() - $time;
    if($limit<60){

        $time=$_SESSION['LANG']=='en' ? $limit.' seconds ago':$limit.' 秒前';
    }
    if($limit>=60 && $limit<3600){
        $i = floor($limit/60);
        $_i = $limit%60;
        $s = $_i;
        $time=$_SESSION['LANG']=='en' ?  $i.' minutes ago' : $i.' 分钟前 ';
    }

    if($limit>=3600 && $limit<(3600*24)){
        $h = floor($limit/3600);
        $_h = $limit%3600;
        $i = ceil($_h/60);
        $time=$_SESSION['LANG']=='en' ? $h.' hours ago' : $h.' 小时前';
    }

//    if($limit>=(3600*24) && $limit<(3600*24*30)){
//        $d = floor($limit/(3600*24));
//        $time= $_SESSION['LANG']=='en' ? $d.' days ago' : $d.' 天前';
//    }
//
//    if($limit>=(3600*24*30)){
//        $m = floor($limit/(3600*24*30));
//        if($m>=12){
//            $y=floor($m/12);
//            $time=$_SESSION['LANG']=='en' ? $y.' years ago' : $y.' 年前';
//        }else{
//            $time=$_SESSION['LANG']=='en' ? $m.' months ago' : $m.' 个月前';
//        }
//    }
    if($limit>=(3600*24)){
        $time = date('Y-m-d H:i:s',$time);
    }
    return $time;
}

function base_encode($str) {
    $src  = array("/","+","=");
    $dist = array("_a","_b","_c");
    $old  = base64_encode($str);
    $new  = str_replace($src,$dist,$old);
    return $new;
}

function base_decode($str) {
    $src = array("_a","_b","_c");
    $dist  = array("/","+","=");
    $old  = str_replace($src,$dist,$str);
    $new = base64_decode($old);
    return $new;
}
function checkPicpath($path){
    if($path){
        if(\Common\Validation::isHttp($path)) {
            $newPath = $path;
        }else{
            $newPath = UPYUN_URL.$path;
        }
    }else{
        $newPath = "";
    }
    return $newPath;
}

/**
 * 判断是否是移动设备
 */
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array (
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
/**
 * 判断是否是微信浏览器
 */
function isWeiXinBrowser(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}
// 连接mongodb
function conn($dbhost, $dbname, $dbuser, $dbpasswd){
    $server = 'mongodb://'.$dbuser.':'.$dbpasswd.'@'.$dbhost.'/'.$dbname;
    try{
        $conn = new MongoClient($server);
        $db = $conn->selectDB($dbname);
    } catch (MongoException $e){
        throw new ErrorException('Unable to connect to db server. Error:' . $e->getMessage(), 31);
    }
    return $db;
}

// 插入坐标到mongodb
function add($dbconn, $tablename, $longitude, $latitude, $name){
    $index = array('loc'=>'2dsphere');
    $data = array(
        'loc' => array(
            'type' => 'Point',
            'coordinates' => array(doubleval($longitude), doubleval($latitude))
        ),
        'name' => $name
    );
    $coll = $dbconn->selectCollection($tablename);
    $coll->ensureIndex($index);
    $result = $coll->insert($data, array('w' => true));
    return (isset($result['ok']) && !empty($result['ok'])) ? true : false;
}

// 搜寻附近的坐标
function query($dbconn, $tablename, $longitude, $latitude, $maxdistance, $limit=20){
    $param = array(
        'loc' => array(
            '$nearSphere' => array(
                '$geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(doubleval($longitude), doubleval($latitude)),
                ),
                '$maxDistance' => $maxdistance*1000
            )
        )
    );

    $coll = $dbconn->selectCollection($tablename);
    $cursor = $coll->find($param);
    $cursor = $cursor->limit($limit);

    $result = array();
    foreach($cursor as $v){
        $result[] = $v;
    }

    return $result;
}
