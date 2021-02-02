<?php
/**
 * @Created by haihuike.
 * @User: wangchengfei
 * @Date: 2021/2/2
 * @Time: 14:18
 */
namespace tbryan24\Alinpay;
header("Content-Type:text/html; charset=gb2312");
class AlinpayUtil{
	/**
	 * 将参数数组签名
	 */

	 //RSA签名
		public static function Sign(array $array){
				ksort($array);
		$bufSignSrc = AlinpayUtil::ToUrlParams($array);
		$private_key='MIICXAIBAAKBgQDtB6T1D/nh5P/wWumTnj76GVQ6TgkbbfeN6HlXwk5ZcnuN26o2tY1SGnm19DIF/sj2J1iECDifbm0h9EeIsmPctfx0dNKpFKX1t/9LrAT7fap+69MbRR3q4VQneHU9qym9jnOSISjoNOXMPwZjBaITJBbKrdI5a1FyPMyTpSyHMQIDAQABAoGAPZbf6QGGt4iubEDjMoVK7eeI+EFwolz3lzsR1JjbjOhvbFPoraCNIQlaGMpj+STUCQn+OQh91gd2ef0kXUOlKKNXI2qOnAqITy8TEnyYJbGQh3JDx+d6NuUShM2uJ4yICgvsjexwIWpyspocP8mEOotGl6t/MxSJPHYzLRO0esUCQQD+o7zcu1ITyjua8RhSkfjL72zt6p2B+sdI2QhmNxZcbhn22r7njohbTHiuuxbg7+vN3XFCovjOPMkeJB+QnyLrAkEA7kvSheppZt5BwImVoFYRPFVTWBqUSwkQ4ACpuCJxpr5OZlL/jtcgLQBVQBB1iIA+GsAuxKn9WBa0hfyLfR1fUwJBALF4lOyScYXxcNFwLy99JRWdbSH0Xop0qegPu1biFeedpOLzWhIwuMBI7+N36V4kWQhFyeZTh2zV2KX1LzqwbrkCQDqaMPK3/CXNINRtwXtFz0VMIov3NWLinuDHqPVcmyCLipJFdQ22v/XxMAXqRk1EZIGFo7q/p0sjgk+1FMS3FXsCQDy4oPHVXyqbIjcGLr3hGDHBWjucaEboge7/gjJr8abrivq6xUvne0XZsA25wYg1eCZE4BTm1IhrwezLUvixn4c=';
    $private_key = chunk_split($private_key , 64, "\n");
    $key = "-----BEGIN RSA PRIVATE KEY-----\n".wordwrap($private_key)."-----END RSA PRIVATE KEY-----";
  //   echo $key;
	 if(openssl_sign($bufSignSrc, $signature, $key )){
	//		echo 'sign success';
		}else{
			echo 'sign fail';
		} 
$sign = base64_encode($signature);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
//echo $sign;
// echo $signature,"\n";
		return $sign;

	}
	
	
	public static function ToUrlParams(array $array)
	{
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
		unset($array['sign']);
		ksort($array);
		$bufSignSrc = AlinpayUtil::ToUrlParams($array);
		$public_key='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCm9OV6zH5DYH/ZnAVYHscEELdCNfNTHGuBv1nYYEY9FrOzE0/4kLl9f7Y9dkWHlc2ocDwbrFSm0Vqz0q2rJPxXUYBCQl5yW3jzuKSXif7q1yOwkFVtJXvuhf5WRy+1X5FOFoMvS7538No0RpnLzmNi3ktmiqmhpcY/1pmt20FHQQIDAQAB';	
 	  $public_key = chunk_split($public_key , 64, "\n");
	  $key = "-----BEGIN PUBLIC KEY-----\n$public_key-----END PUBLIC KEY-----\n";
	  $result= openssl_verify($bufSignSrc,base64_decode($sign), $key );
    return $result;  
	}
	
	
}
?>