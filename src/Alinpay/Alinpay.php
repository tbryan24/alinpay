<?php
/**
 * @Created by haihuike.
 * @User: wangchengfei
 * @Date: 2021/2/2
 * @Time: 13:29
 */
namespace tbryan24\Alinpay;
use app\core\payment\PaymentException;
use yii\base\Exception;

//header("Content-type:text/html;charset=utf-8");
class Alinpay
{
    private $appid = '';
    private $cusid = '';
    private $apiurl = "";//开发环境
    private $apiversion = '';
    private $private_key='';
    private $public_key='';
    private $appkey='';

    function setConfig($config){
        $this->appid=$config['appid'];
        $this->cusid=$config['cusid'];
        $this->apiurl=$config['apiurl'];
        $this->apiversion=$config['apiversion'];
        $this->private_key=$config['private_key'];
        $this->appkey=$config['appkey'];
        $this->public_key=$config['public_key'];
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
        $params["randomstr"] =$this->getRandomStr();
        $params["signtype"] =$order['signtype'];
        $params["front_url"] =$order['front_url'];
        $params["private_key"] =$this->private_key;
        $params["sign"] = AlinpayUtil::Sign($params);//签名
        $paramsStr = AlinpayUtil::ToUrlParams($params);
        $url = $this->apiurl . "/pay";
        $rsp = $this->request($url, $paramsStr);
        $rspArray = json_decode($rsp, true);
        return $rspArray;
        /*try{
            $rspArray['private_key']=$this->private_key;
            if($this->validSign($rspArray)){
                return $rspArray;
            }else{
                throw new \Exception('验签失败');
            }
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }*/


    }

    //当天交易用撤销
    function cancel($order){
        $params = array();
        $params["cusid"] = $this->cusid;
        $params["appid"] = $this->appid;
        $params["version"] = $this->apiversion;
        $params["trxamt"] = $order['trxamt'];
        $params["reqsn"] = $order['reqsn'];//商户退款交易单号,商户平台唯一
        $params["oldreqsn"] = $order['oldreqsn'];//原交易的商户交易单号
        $params["randomstr"] = $this->getRandomStr();//
        $params["signtype"] ='RSA';
        $params["sign"] = AlinpayUtil::Sign($params);//签名
        $paramsStr = AlinpayUtil::ToUrlParams($params);
        $url = $this->apiurl . "/cancel";
        $rsp = $this->request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if($this->validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

    //当天交易请用撤销,非当天交易才用此退货接口
    function refund($paymentRefund,$paymentOrderUnion){
        $params = array();
        $params["cusid"] = $this->cusid;
        $params["appid"] = $this->appid;
        $params["version"] = $this->apiversion;
        $params["trxamt"] = $paymentRefund->amount * 100;
        $params["reqsn"] = $paymentRefund->order_no;//商户退款交易单号,商户平台唯一
        $params["oldreqsn"] = $paymentRefund->out_trade_no;//原交易的商户交易单号
        $params["randomstr"] = $this->getRandomStr();//
        $params["signtype"] ='RSA';
        $params["private_key"] =$this->private_key;
        $params["sign"] = AlinpayUtil::Sign($params);//签名
        $paramsStr = AlinpayUtil::ToUrlParams($params);
        $url = $this->apiurl . "/refund";
        $rsp = $this->request($url, $paramsStr);
        $rspArray = json_decode($rsp, true);
        if ($rspArray['retcode']=='SUCCESS'&&$rspArray['trxstatus']=='0000'){
            return true;
        }else{
            $msg=isset($rspArray['retmsg'])?$rspArray['retmsg']:$rspArray['errmsg'];
            throw new PaymentException($msg);
        }
        /*$rspArray["private_key"] =$this->private_key;
        if($this->validSign($rspArray)){
            P('验签正确,进行业务处理');
            echo "验签正确,进行业务处理";
        }else{
            P('验签失败');
        }*/
    }


    //验签
    function validSign($array){
        if("SUCCESS"==$array["retcode"]){
            $signRsp = strtolower($array["sign"]);
            $array["sign"] = "";
            $sign =  strtolower(AlinpayUtil::sign($array));
            if($sign==$signRsp){
                return true;
            }else {
                throw new \Exception("验签失败:".$signRsp."--".$sign);
            }
        }else{
            throw new \Exception($array["retmsg"]);
        }
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

    public function getRandomStr($randLength = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
        $len = strlen($chars);
        $randStr = '';
        for ($i = 0; $i < $randLength; $i++) {
            $randStr .= $chars[mt_rand(0, $len - 1)];
        }
        return $randStr;
    }
}