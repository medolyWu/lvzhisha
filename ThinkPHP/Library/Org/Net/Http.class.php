<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Org\Net;
/**
 * Http 工具类
 * 提供一系列的Http方法
 * @author    liu21st <liu21st@gmail.com>
 */
class Http {

    /**
     * 采集远程文件
     * @access public
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     * @return mixed
     */
    static public function curlDownload($remote,$local) {
        $cp = curl_init($remote);
        $fp = fopen($local,"w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);
        curl_close($cp);
        fclose($fp);
    }

   /**
    * 使用 fsockopen 通过 HTTP 协议直接访问(采集)远程文件
    * 如果主机或服务器没有开启 CURL 扩展可考虑使用
    * fsockopen 比 CURL 稍慢,但性能稳定
    * @static
    * @access public
    * @param string $url 远程URL
    * @param array $conf 其他配置信息
    *        int   limit 分段读取字符个数
    *        string post  post的内容,字符串或数组,key=value&形式
    *        string cookie 携带cookie访问,该参数是cookie内容
    *        string ip    如果该参数传入,$url将不被使用,ip访问优先
    *        int    timeout 采集超时时间
    *        bool   block 是否阻塞访问,默认为true
    * @return mixed
    */
    static public function fsockopenDownload($url, $conf = array()) {
        $return = '';
        if(!is_array($conf)) return $return;

        $matches = parse_url($url);
        !isset($matches['host']) 	&& $matches['host'] 	= '';
        !isset($matches['path']) 	&& $matches['path'] 	= '';
        !isset($matches['query']) 	&& $matches['query'] 	= '';
        !isset($matches['port']) 	&& $matches['port'] 	= '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;

        $conf_arr = array(
            'limit'		=>	0,
            'post'		=>	'',
            'cookie'	=>	'',
            'ip'		=>	'',
            'timeout'	=>	15,
            'block'		=>	TRUE,
            );

        foreach (array_merge($conf_arr, $conf) as $k=>$v) ${$k} = $v;

        if($post) {
            if(is_array($post))
            {
                $post = http_build_query($post);
            }
            $out  = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: '.strlen($post)."\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out  = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if(!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if(!$status['timed_out']) {
                while (!feof($fp)) {
                    if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while(!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
    }

    /**
     * 下载文件
     * 可以指定下载显示的文件名，并自动发送相应的Header信息
     * 如果指定了content参数，则下载该参数的内容
     * @static
     * @access public
     * @param string $filename 下载文件名
     * @param string $showname 下载显示的文件名
     * @param string $content  下载的内容
     * @param integer $expire  下载内容浏览器缓存时间
     * @return void
     */
    static public function download ($filename, $showname='',$content='',$expire=180) {
        if(is_file($filename)) {
            $length = filesize($filename);
        }elseif(is_file(UPLOAD_PATH.$filename)) {
            $filename = UPLOAD_PATH.$filename;
            $length = filesize($filename);
        }elseif($content != '') {
            $length = strlen($content);
        }else {
            E($filename.L('下载文件不存在！'));
        }
        if(empty($showname)) {
            $showname = $filename;
        }
        $showname = basename($showname);
		if(!empty($filename)) {
			$finfo 	= 	new \finfo(FILEINFO_MIME);
			$type 	= 	$finfo->file($filename);			
		}else{
			$type	=	"application/octet-stream";
		}
        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=".$expire);
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=".$showname);
        header("Content-Length: ".$length);
        header("Content-type: ".$type);
        header('Content-Encoding: none');
        header("Content-Transfer-Encoding: binary" );
        if($content == '' ) {
            readfile($filename);
        }else {
        	echo($content);
        }
        exit();
    }

    /**
     * 显示HTTP Header 信息
     * @return string
     */
    static function getHeaderInfo($header='',$echo=true) {
        ob_start();
        $headers   	= getallheaders();
        if(!empty($header)) {
            $info 	= $headers[$header];
            echo($header.':'.$info."\n"); ;
        }else {
            foreach($headers as $key=>$val) {
                echo("$key:$val\n");
            }
        }
        $output 	= ob_get_clean();
        if ($echo) {
            echo (nl2br($output));
        }else {
            return $output;
        }

    }

    /**
     * HTTP Protocol defined status codes
     * @param int $num
     */
	static function sendHttpStatus($code) {
		static $_status = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',

			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',

			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
		}
	}

    /**
     * Post数据
     *
     * @param string $url
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    public static function Post($url, $data = array(), $cookie = array(), $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('POST', $host, $page, $port, $data, $cookie, $timeout);
    }

    /**
     * Get数据
     *
     * @param string $url
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function Get($url, $cookie, $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('GET', $host, $page, $port, null, $cookie, $timeout);
    }

    /**
     * 异步连接
     *
     * @param string $type
     * @param string $host
     * @param string $page
     * @param int $port
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    private static function async($type, $host, $page, $port = 80, $data = array(), $cookie = array(), $timeout = 3)
    {
        $type = $type == 'POST'?'POST':'GET';
        $errno = $errstr = null;
        $Content = array();
        if($type == 'POST' && $data && is_array($data)){
            foreach ($data as $k=>$v)
                $Content[] = $k . "=" . rawurlencode($v);
            $Content = implode("&", $Content);
        }
        //
        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if(!$fp)
            return array(false, '提示:无法连接!');
        $stream = "{$type} /{$page} HTTP/1.1\r\n";
        $stream .= "Host: {$host}\r\n";
        if($Content && $type == 'POST'){
            $stream .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $stream .= "Content-Length: " . strlen($Content) . "\r\n";
        }
        if($cookie && is_array($cookie)){
            $stream .= "Connection: Close\r\n";
            $stream .= 'Cookie:';
            $tmp = array();
            foreach ($cookie as $k=>$v)
                $tmp[] = "{$k}={$v}";
            $stream .= implode('; ', $tmp);
            $stream .= "\r\n\r\n";
        }else{
            $stream .= "Connection: Close\r\n\r\n";
        }
        fwrite($fp, $stream);
        if($Content){
            usleep(10);
            fwrite($fp, $Content);
        }
        stream_set_timeout($fp, $timeout);
        $res = stream_get_contents($fp);
        $info = stream_get_meta_data($fp);
        fclose($fp);
        if($info['timed_out']){
            return array(false, '提示:连接超时');
        }else{
            return array(true, substr(strstr($res, "\r\n\r\n"), 4));
        }
    }
    public static function CurlRequst($url, $params = array(), $method = 'GET', $ssl = false,$timeout = 30){
        $opts = array(
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );
        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url .'?'. http_build_query($params);
                break;
            case 'POST':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
        }
        if ($ssl) {
            $pemPath = dirname(__FILE__).'/Wechat/';
            $pemCret = $pemPath.'cert.pem';
            $pemKey  = $pemPath.'key.pem';
            if (!file_exists($pemCret)) {
                //$this->error = '证书不存在';
                //return false;
                return array(false, '提示:证书不存在');
            }
            if (!file_exists($pemKey)) {
                //$this->error = '密钥不存在';
                //return false;
                return array(false, '提示:密钥不存在');
            }
            $opts[CURLOPT_SSLCERTTYPE] = 'PEM';
            $opts[CURLOPT_SSLCERT]     = $pemCret;
            $opts[CURLOPT_SSLKEYTYPE]  = 'PEM';
            $opts[CURLOPT_SSLKEY]      = $pemKey;
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if ($err > 0) {
            //$this->error = $errmsg;
            //return false;
            return array(false, '提示:'.$errmsg);
        }else {
            return $data;
        }
    }

    public static function CurlPostFile($url,$filename,$path,$type){
        $data = array(
            'file'=>'@'.realpath($path).";type=".$type.";filename=".$filename
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}//类定义结束