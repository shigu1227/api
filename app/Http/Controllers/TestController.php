<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function alipay()
    {
        $ali_gateway = 'https://openapi.alipaydev.com/gateway.do';//支付网关

        // 公共请求参数
        $appid = '2016101100657504';
        $method = 'alipay.trade.page.pay';
        $charset = 'utf-8';
        $signtype = 'RSA2';
        $sign = '';
        $timestamp = date('Y-m-d H:i:s');
        $version = '1.0';
        $return_url = 'http://1905api.comcto.com/test/alipay/return'; //支付宝同步通知
        $notify_url = 'http://1905api.comcto.com/test/alipay/notify'; //支付宝异步通知地址
        $biz_content = '';

        // 请求参数
        $out_trade_no = time().rand(1111,9999);   //商户订单号
        $product_code = 'FAST_INSTANT_TRADE_PAY';
        $total_amout = 66.66;
        $subject = '测试'.$out_trade_no;

        $request_param = [
            'out_trade_no'  =>$out_trade_no,
            'product_code'  =>$product_code,
            'total_amount'  =>$total_amout,
            'subject'       =>$subject
        ];

        $param = [
            'app_id'        =>$appid,
            'method'        =>$method,
            'charset'       =>$charset,
            'sign_type'     =>$signtype,
            'timestamp'     =>$timestamp,
            'version'       =>$version,
            'notify_url'    =>$notify_url,
            'return_url'    => $return_url,
            'biz_content'   =>json_encode($request_param)
        ];

        $url = 'https://openapi.alipaydev.com/gateway.do?';
        print_r($param);
        // 字典排序
        ksort($param);

        // 第二步  拼接key1=value1&key2=value2，，，
        $str = "";
        foreach($param as $k=>$v){
            $str .=$k . '=' .$v . '&';
        }
        // echo 'str:' . $str;die;
        $str = rtrim($str,'&');

        // 第三步，签名 https://docs.open.alipay.com/291/106118
        $key = storage_path('keys/app_priv');
        $priKey = file_get_contents($key);
        $res = openssl_get_privatekey($priKey);
        // var_dump($res);echo '</br>';
        openssl_sign($str,$sign,$res,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        $param['sign'] = $sign;

        //  第四部 urlencode
        $param_str = '?';
        foreach($param as $k => $v){
            $param_str .=$k. '='.urlencode($v) . '&';
        }
        $param_str = rtrim($param_str,'&');
        $url = $ali_gateway . $param_str;
        //发送GET请求
        //echo $url;die;
        header("Location:".$url);

    }
}