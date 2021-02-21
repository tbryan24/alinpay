<?php
/**
 * @Created by haihuike.
 * @User: wangchengfei
 * @Date: 2021/2/6
 * @Time: 13:31
 */

namespace tbryan24\Alinpay;


class Customs
{
    private $md5_key='';
    private $cusid='';
    private $customsCode='';
    private $channel='';
    private $currency;
    private $ent_name;
    private $ent_code;
    private $version;
    private $visitor_id;
    private $mchId;
    private $signType;
    private $charset;
    private $customs_url;
    public function setConfig($config){
        $this->md5_key=$config['md5_key'];
        $this->cusid=$config['cusid'];
        $this->customsCode=$config['customsCode'];
        $this->channel=$config['channel'];
        $this->currency=$config['currency'];
        $this->ent_name=$config['ent_name'];
        $this->ent_code=$config['ent_code'];
        $this->version=$config['version'];
        $this->visitor_id=$config['visitor_id'];
        $this->mchId=$config['mchId'];
        $this->signType=$config['signType'];
        $this->charset=$config['charset'];
        $this->customs_url=$config['customs_url'];
        return $this;
    }
    public function send($order)
    {
        $time = date('YmdHis');
        //先组装body
        $body = "<BODY>";
        $body .= "<CUSTOMS_CODE>" . $this->customsCode . "</CUSTOMS_CODE>";
        $body .= "<PAYMENT_CHANNEL>" . $this->channel . "</PAYMENT_CHANNEL>";
        $body .= "<CUS_ID>" . $this->cusid . "</CUS_ID>";
        $body .= "<PAYMENT_DATETIME>" . $order['paytime'] . "</PAYMENT_DATETIME>";
        $body .= "<MCHT_ORDER_NO>" . $order['union_order_no'] . "</MCHT_ORDER_NO>";
        $body .= "<PAYMENT_ORDER_NO>" . $order['payment_order_no'] . "</PAYMENT_ORDER_NO>";
        $body .= "<PAYMENT_AMOUNT>" . $order['order_amount'] . "</PAYMENT_AMOUNT>";
        $body .= "<CURRENCY>" . $this->currency . "</CURRENCY>";
        $body .= "<ESHOP_ENT_CODE>" . $this->ent_code . "</ESHOP_ENT_CODE>";
        $body .= "<ESHOP_ENT_NAME>" . $this->ent_name . "</ESHOP_ENT_NAME>";
        $body .= "<PAYER_NAME>" . $order['truename'] . "</PAYER_NAME>";
        $body .= "<PAPER_TYPE>01</PAPER_TYPE>";
        $body .= "<PAPER_NUMBER>" . $order['idnum'] . "</PAPER_NUMBER>";
        $body .= "<PAPER_PHONE>" . $order['mobile'] . "</PAPER_PHONE>";
        $body .= "<MEMO></MEMO>";
        $body .= "</BODY>";
        $sign = $this->sign($body);
        //先组装header

        $header = "<HEAD>";
        $header .= "<VERSION>" . $this->version . "</VERSION>";
        $header .= "<VISITOR_ID>" . $this->visitor_id . "</VISITOR_ID>";
        $header .= "<MCHT_ID>" . $this->mchId . "</MCHT_ID>";
        $header .= "<ORDER_NO>" . $order['payment_order_no'] . "</ORDER_NO>";
        $header .= "<TRANS_DATETIME>" . $time . "</TRANS_DATETIME>";
        $header .= "<CHARSET>" . $this->charset . "</CHARSET>";
        $header .= "<SIGN_TYPE>" . $this->signType . "</SIGN_TYPE>";
        $header .= "<SIGN_MSG>" . $sign . "</SIGN_MSG>";
        $header .= "</HEAD>";
        $xml = $header . $body;
        $sendXml = "<PAYMENT_INFO>" . $xml . "</PAYMENT_INFO>";
        $result = $this->curlSend($this->customs_url, ['data' => base64_encode($sendXml)], 1, 1);
        try{
            $xml = simplexml_load_string(base64_decode($result), 'SimpleXMLElement', LIBXML_NOCDATA);
            $xmlData = json_decode(json_encode($xml), TRUE);
            $returnData['code']=0;
            $returnData['request_xml']=$sendXml;
            $returnData['response_xml']=base64_decode($result);
            $returnData['data']=$xmlData;
            return $returnData;
        }catch (\Exception $e){
            $returnData['code']=1;
            $returnData['request_xml']=$sendXml;
            $returnData['response_xml']='';
            $returnData['data']=base64_decode($result);
            return $returnData;
        }
    }

    public function sign($singXml)
    {
        $key = "<key>{$this->md5_key}</key>";
        return strtoupper(md5($singXml . $key));
    }

    private function charaCet($data, $targetCharset = 'UTF-8')
    {
        if (!empty($data)) {
            $fileType = $fileType = mb_detect_encoding($data, array('UTF-8', 'GBK', 'LATIN1', 'BIG5'));
            if ($fileType != $targetCharset) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

    public function curlSend($url, $params, $isPost = 1, $https = 0)
    {
        $header = [
            "content-type: application/x-www-form-urlencoded;charset=UTF-8",
            'cache-control: no-cache',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
}