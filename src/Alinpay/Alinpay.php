<?php
/**
 * @Created by haihuike.
 * @User: wangchengfei
 * @Date: 2021/2/2
 * @Time: 13:29
 */
namespace tbryan24\Alinpay;
header("Content-type:text/html;charset=utf-8");
class Alinpay
{
    private $appid = '00000003';
    private $cusid = '990440148166000';
    private $apiurl = "https://vsp.allinpay.com/apiweb/unitorder";//生产环境
    private $apiversion = '11';

    function setConfig($config){
        $this->appid=$config['appid'];
        $this->cusid=$config['cusid'];
        $this->apiurl=$config['apiurl'];
        $this->apiversion=$config['apiversion'];
        return $this;
    }
    //统一支付接口
    function pay($order){
        $params = array();
        $params["cusid"] = $this->cusid;
        $params["appid"] = $this->appid;
        $params["version"] = $this->apiversion;
        $params["orgid"] = $order['orgid'];
        $params["trxamt"] = $order['trxamt'];
        $params["reqsn"] = $order['reqsn'];
        $params["paytype"] = $order['paytype'];
        $params["body"] = $order['body'];
        $params["remark"] = $order['remark'];
        $params["validtime"] = $order['validtime'];
        $params["acct"] = $order['acct'];
        $params["notify_url"] = $order['notify_url'];
        $params["limit_pay"] =$order['limit_pay'];
        $params["sub_appid"] = $order['sub_appid'];
        $params["subbranch"] = $order['subbranch'];
        $params["cusip"] = $order['cusip'];
        $params["idno"] = $order['idno'];
        $params["truename"] =$order['truename'];
        $params["fqnum"] =$order['fqnum'];
        $params["randomstr"] =$order['randomstr'];
        $params["signtype"] =$order['signtype'];
        $params["front_url"] =$order['front_url'];
        $params["sign"] = AlinpayUtil::Sign($params);//签名
        $paramsStr = AlinpayUtil::ToUrlParams($params);
        $url = $this->apiurl . "/pay";
        $rsp = $this->request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if($this->validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }

    }

    //验签
    function validSign($array){
        if("SUCCESS"==$array["retcode"]){
            $signRsp = strtolower($array["sign"]);
            $array["sign"] = "";
            $sign =  strtolower(AlinpayUtil::Sign($array));
            if($sign==$signRsp){
                return TRUE;
            }
            else {
                echo "验签失败:".$signRsp."--".$sign;
            }
        }else{
            echo $array["retmsg"];
        }

        return FALSE;
    }

    //发送请求操作仅供参考,不为最佳实践
    function request($url,$params){
        $ch = curl_init();
        $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        curl_close($ch);
        return  $output;
    }
}