<?php
namespace Common;
abstract class Validation{

	//验证是否为非零的正整数
	public static function IsNum($str){return (preg_match("/^[1-9]*$/",$str))?true:false;}

	//验证字符串是否为指定长度的数字
	public static function IsNumLengthX2Y($str,$X,$Y){return (preg_match("/^[0-9]{".$X.",".$Y."}$/i",$str))?true:false;}

	//验证字符串是否为指定长度的字母
	public static function IsLetterX2Y($str,$X,$Y){return (preg_match("/^[a-zA-Z]{".$X.",".$Y."}$/i",$str)) ? true : false;}

	//验证是否为指定长度的字母/数字组合
	public static function IsNumAndLetterLengthX2Y($str,$X,$Y){$str = html_entity_decode($str);return (preg_match("/^[a-zA-Z0-9]{1}[a-zA-Z0-9_]{".$X.",".$Y."}$/",$str)) ? true:false;}

	//验证字符串是否为指定长度的字母和数字和下划线组合
	public static function IsNumAndLetterAndDownLineLengthX2Y($str,$X,$Y){$str = html_entity_decode($str);return (preg_match("/^[a-zA-Z0-9_]{".$X.",".$Y."}$/",$str)) ? true:false;}

	//验证字符串是否包含汉字  有中文返回FALSE
	public static function IsThereChinese($str){$str = html_entity_decode($str);return (preg_match("/[\x7f-\xff]/", $str)) ? false : true;}

	//计算字符串的长度，1个汉字长度为1
	public static function GetStringLength_Chinese1($str){$str = html_entity_decode($str);if(function_exists('mb_strlen')){return mb_strlen($str,'utf-8');}else {preg_match_all("/./u", $str, $ar);return count($ar[0]);}}

	//计算字符串的长度，1个汉字长度为2
	public static function GetStringLength_Chinese2($str){$str = html_entity_decode($str);$count =0;for($i=0;$i<strlen($str);){if(preg_match("/[\x7f-\xff]/", $str[$i]) === 1){$i = $i+3;$count += 2;}else{$i = $i+1;$count += 1;}}return $count;}

	//验证是否为合法的国际电话号码 010-12345678 或 0100-1234567
	public static function IsPhoneNumber($str){$str = html_entity_decode($str);return preg_match("/^(\d{3}-)(\d{8})$|^(\d{4}-)(\d{7})$|^(\d{4}-)(\d{8})$/", $str) ? true : false;}

	//验证是否为合法的国际手机号码
	public static function IsMobileNumber($str){$str = html_entity_decode($str);return preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{9}$|18[0-9]{9}$/",$str) ? true : false;}

	//验证是否为合法的日期 2000-02-05
	public static function IsDate($str){$str = html_entity_decode($str);if(preg_match("/^\d{4}-\d{2}-\d{2}$/",$str) == 1){$arr = explode('-',$str);if(checkdate($arr[1], $arr[2],$arr[0]) == true){return true;}else{return false;}}else{return false;}}

	//验证是否为合法的时间 12:15:20
	public static function IsTimeFormat($str){$str = html_entity_decode($str);if(preg_match("/^\d{2}:\d{2}:\d{2}$/",$str) == 1){$time = strtotime($str);if(date('H:i:s',$time) == $str){return true;}else{return false;}}else{return false;}}

	//验证是否为合法的日期时间 2000-02-05 12:15:20
	public static function IsDateTimeFormat($str){$str = html_entity_decode($str);$time = strtotime($str);if(date('Y-m-d H:i:s',$time) == $str){return true;}else{return false;}}

	//验证是否为合法的IP地址
	public static function IsIpAdress($str){$str = html_entity_decode($str);return preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',$str) ? true : false;}

	//验证是否为合法的邮件地址
	public static function IsEmailAdress($str){return (preg_match('/^\w+((-|\.)\w+)*@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]{2,3}$/',$str))?true:false; }

	//验证是否为合法的url地址
	public static function IsUrlAdress($str){$str = html_entity_decode($str);return (preg_match("/^http:\/\/[A-Za-z0-9-]+\.[A-Za-z0-9-]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/",$str))?true:false; }

	//验证是否为合法的中国身份证号码
	public static function IsIDCardNum($str){$str = trim($str);if(strlen($str) == 18){$idcard_base = substr($str,0,17);if (self::IDCardVerifyNumber($idcard_base) != strtoupper(substr($str, 17, 1))){return false;}else{return true;}}else{return false;}}

	// 计算身份证校验码，根据国家标准GB 11643-1999
	public static function IDCardVerifyNumber($idcard_base){if(strlen($idcard_base) != 17){return false;}$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');$checksum = 0;for ($i = 0; $i < strlen($idcard_base); $i++){$checksum += substr($idcard_base, $i, 1) * $factor[$i];}$mod = $checksum % 11;$verify_number = $verify_number_list[$mod];return $verify_number;}
	
	//判断是否是手机或座机号码
	public static function IsTel($value){$mobile = "/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/";$tel = "/^(\d{3,4}-?)?\d{7,9}$/g"; if(preg_match($mobile, $value) || preg_match($tel, $value)){return true;} return false;}

    //验证http:开头
    public static function isHttp($str){$str = html_entity_decode($str);return (preg_match("/^http:/",$str))?true:false; }

}
?>