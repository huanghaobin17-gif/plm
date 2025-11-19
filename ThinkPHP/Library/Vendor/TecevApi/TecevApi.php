<?php
/*
* $Author ：邓锦龙
*
* 版权所有 广州职帅人力资源服务有限公司
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

/**
 * 如果您的 PHP 没有安装 cURL 扩展，请先安装
 */
if (!function_exists('curl_init'))
{
	throw new Exception('OpenAPI needs the cURL PHP extension.');
}

/**
 * 如果您的 PHP 不支持JSON，请升级到 PHP 5.2.x 以上版本
 */
if (!function_exists('json_decode'))
{
	throw new Exception('OpenAPI needs the JSON PHP extension.');
}

/**
 * 错误码定义
 */
define('OPENAPI_ERROR_REQUIRED_PARAMETER_EMPTY', 2001); // 参数为空
define('OPENAPI_ERROR_REQUIRED_PARAMETER_INVALID', 2002); // 参数格式错误
define('OPENAPI_ERROR_RESPONSE_DATA_INVALID', 2003); // 返回包格式错误
define('OPENAPI_ERROR_RESPONSE_SIGNED_INVALID', 2004); // 无效的签名
define('OPENAPI_ERROR_RESPONSE_SYNCFAIL', 2005); // 同步数据失败
define('OPENAPI_ERROR_RESPONSE_FOLLOWFAIL', 2006); // 跟进数据失败
define('OPENAPI_ERROR_RESPONSE_EDITFAIL', 2007); // 跟进数据失败
define('OPENAPI_ERROR_RESPONSE_BUYFAIL', 2008); // 跟进数据失败
define('OPENAPI_ERROR_CURL', 3000); // 网络错误, 偏移量3000, 详见 http://curl.haxx.se/libcurl/c/libcurl-errors.html

/**
 * 提供访问平台api的接口
 */
class TecevApi
{
	public $appKey  = 'tecev-hsyy';//总部分配给项目组的appKey，必须具有唯一性
    public $signKey = 'xIZ1cxi8';//总部分配给项目组的密钥
    public $url     = 'http://www.tecev-n.com/index.php/Admin/NotCheckLogin/ChangeData/index.html';//总部数据同步调用地址
	private $debug   = false;
	/**
	 * 构造函数
	 *
	 */
	function __construct()
	{

	}
	/**
     * 获取数据签名，MD5(signKey+enterCode+data+requestTime),
	 * @param string $enterCode 总部分配给医院的唯一识别码
	 * @param string $data BASE64加密字符串
	 * @return string
	 */
	public function getSign($method,$requestTime,$enterCode,$data='')
	{
		$signKey = $this->signKey;
		return MD5($method.$signKey.$enterCode.$data.$requestTime);
	}

	//获取当前时间戳
	public function getRequestTime()
	{
		return sprintf("%.3f", microtime(true))*1000;

	}

	//心跳检测
	public function testAlive()
	{
		$url = $this->url;
		$method = 'testAlive';
		$appKey = $this->appKey;
		$requestTime = $this->getRequestTime();
		$data = array('requestParam'=>'hello Are you alive?');
		$data = $this->formatData($data);
		$signed = $this->getSign($method,$requestTime,$enterCode='livetest',$data);
		$url = $url.'?method='.$method."&appKey=".$appKey."&requestTime=".$requestTime."&signed=".$signed.'&enterCode='.$enterCode.'&data='.$data;
		return $this->curl($url);
	}

	/**
	 * 调用同步数据接口
	 * @param $method string 方法名
     * @param $enterCode string 医院的唯一识别码
	 * @param array $data 请求的参数
	 * @return bool|mixed
	 */
	public function syncBusinessData($method,$enterCode,$data=array())
	{
		$url = $this->url;
		$appKey = $this->appKey;
		$requestTime = $this->getRequestTime();
		$data = $this->formatData($data);
		$signed = $this->getSign($method,$requestTime,$enterCode,$data);
		$url = $url.'?method='.$method."&appKey=".$appKey."&requestTime=".$requestTime."&signed=".$signed.'&enterCode='.$enterCode.'&data='.$data;
		$res = $this->curl($url);
		$res = $this->formatRes($res);
		return $res;
	}

    /**
     * 调用采购对接同步信息到项目组
     * @param $url string 项目组访问地址
     * @param $method string 方法名
     * @param $enterCode string 医院的唯一识别码
     * @param array $data 请求的参数
     * @return bool|mixed
     */
	public function syncBuyMaterielData($url,$method,$enterCode,$appKey,$data=array())
    {
        $requestTime = $this->getRequestTime();
        $data = $this->formatData($data);
        $signed = $this->getSign($method,$requestTime,$enterCode,$data);
        $url = $url.'?method='.$method."&appKey=".$appKey."&requestTime=".$requestTime."&signed=".$signed.'&enterCode='.$enterCode.'&data='.$data;
        $res = $this->curl($url);
        $res = $this->formatRes($res);
        return $res;
    }

	function curl($url) {
		$cur = curl_init($url);
		curl_setopt($cur, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($cur, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($cur, CURLOPT_POST, 1);
		curl_setopt($cur, CURLOPT_HEADER, 0);
		curl_setopt($cur, CURLOPT_TIMEOUT, 30);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
		$rec = curl_exec($cur);
		curl_close($cur);
		return $rec;
	}

	//格式化参数
	public function formatData($data = array())
	{
		if(!$data){
			return '';
		}
		$data = (object)$data;
		$data = json_encode($data);
		return base64_encode($data);
	}

	//格式化数据
	public function formatRes($res)
	{
		$res = json_decode($res,true);
		if($res['resultCode'] != 200){
			return $res;
		}
		if($res['data']) {
			$data = base64_decode($res['data']);
			$data = json_decode($data, true);
			$res['data'] = $data;
		}else{
			$res['data'] = '';
		}
		return $res;
	}
}
