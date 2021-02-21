<?php
/**
 * @Created by haihuike.
 * @User: wangchengfei
 * @Date: 2021/2/2
 * @Time: 14:18
 */
namespace tbryan24\Alinpay;
//header("Content-Type:text/html; charset=gb2312");
//header("Content-type:text/html;charset=utf-8");
class AlinpayUtil{
    /**
     * 将参数数组签名
     */

    //RSA签名
    public static function Sign(array $array){
        $private_key=$array['private_key'];
        unset($array['private_key']);
        ksort($array);
        $bufSignSrc = AlinpayUtil::ToUrlParams($array);
        $private_key = chunk_split($private_key , 64, "\n");
        $key = "-----BEGIN RSA PRIVATE KEY-----\n".wordwrap($private_key)."-----END RSA PRIVATE KEY-----";
        //   echo $key;
        if(openssl_sign($bufSignSrc, $signature, $key )){
            //echo 'sign success';
        }else{
            echo 'sign fail';
        }
        $sign = urlencode(base64_encode($signature));//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        //echo $sign;
        // echo $signature,"\n";
        return $sign;

    }


    public static function ToUrlParams(array $array)
    {
        unset($array['private_key']);
        $buff = "";
        foreach ($array as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 校验签名
     * @param array 参数
     * @param unknown_type appkey
     */


    public static function ValidSign(array $array){
        $sign =$array['sign'];
        $public_key=$array['alinpay_public_key'];
        unset($array['alinpay_public_key']);
        unset($array['sign']);
        ksort($array);
        $bufSignSrc = AlinpayUtil::ToUrlParams($array);
        $public_key = chunk_split($public_key , 64, "\n");
        $key = "-----BEGIN PUBLIC KEY-----\n$public_key-----END PUBLIC KEY-----\n";
        $result= openssl_verify($bufSignSrc,base64_decode($sign), $key );
        return $result;
    }

}
?>