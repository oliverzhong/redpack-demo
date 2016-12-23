<?php 
namespace Controller;

use DOMDocument;

/**
 * 发红包测试
 * @author romic
 */
class RedpackController{
    
    private $api_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    const KEY = 'XXXXXX';
    
    public function indexAction() {
        header("Content-Type: text/xml; charset=utf-8");
        echo $this->send($this->api_url, $this->getXmlBody());
    }
    
    private function getXmlBody(){
        $dom = new DomDocument('1.0', 'UTF-8');
        $root = $dom->createElement("xml");

        $data = $this->getData();
        foreach ($data as $key=>$val){
            $root->appendChild($this->createElement($dom, $key, $val));
        }
        
        $sign = $this->MakeSign($data);
        $root->appendChild($this->createElement($dom, 'sign', $sign));
        
        $dom->appendChild($root);
        return $dom->saveXML();
    }
    
    private function getData(){
        $data = array();
        $data["nonce_str"] = "XXXXXX";
        $data["mch_billno"] = "XXXXXX";
        $data["mch_id"] = "XXXXXX";
        $data["wxappid"] = "XXXXXX";
        $data["send_name"] = "商户名称";
        $data["re_openid"] = "XXXXXX";
        $data["total_amount"] = 100;
        $data["total_num"] = 1;
        $data["wishing"] = "恭喜发财";
        $data["client_ip"] = "192.168.0.1";
        $data["act_name"] = "新年红包";
        $data["remark"] = "新年红包";
        return $data;
    }
    
    private function createElement($dom, $key, $val){
        $element = $dom->createElement($key);
        $section = $dom->createCDATASection($val);
        $element->appendChild($section);
        return $element;
    }
    
    /**
     * 发送模板消息
     * @param unknown $component_access_token
     */
    private function send($url, $xml){
        return $this->postXmlCurl($xml, $url, true);
    }
    
    
    /**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WxPayException
	 */
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30){
	    $CERT = 'apiclient_cert.pem';
	    $KET = 'apiclient_key.pem';
	    
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		
		//如果有配置代理这里就设置代理
// 		if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
// 			&& WxPayConfig::CURL_PROXY_PORT != 0){
// 			curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
// 			curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
// 		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $CERT);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $KET);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			return $error;
		}
	}
	
    /**
     * 格式化参数格式化成url参数
     */
    private function ToUrlParams($data){
        $buff = "";
        foreach ($data as $k => $v){
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function MakeSign($data){
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".self::KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
}
?>