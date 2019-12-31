<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{

    public function alipay()
    {
        $ali_geteway = 'https://openapi.alipaydev.com/gateway.do';


        //公公请求参数
        $appid = '2016101100657504';
        $method = 'alipay.trade.page.pay';
        $charset = 'utf-8';
        $signtype = 'RSA2';
        $sign = '';
        $timestamp = date('Y-m-d H:i:s');
        $version = '1.0';
//        $notify_url = 'http://zxl.xx20.top/alipay/notify';
        $return_url = 'http://1905api.comcto.com/test/alipay/return'; //支付宝同步通知
        $notify_url = 'http://1905api.comcto.com/test/alipay/notify'; //支付宝异步通知地
        $biz_content = '';

        //  请求参数
        $out_trade_no = time().rand(1111,9999);
        $product_code = 'FAST_INSTANT_TRADE_PAY';
        $total_amount = 66.66;
        $subject = '测试订单' .$out_trade_no;

        $request_param = [
            'out_trade_no'  => $out_trade_no,
            'product_code'  => $product_code,
            'total_amount'  => $total_amount,
            'subject'       => $subject
        ];

        $param = [
            'app_id'        => $appid,
            'method'        => $method,
            'charset'       => $charset,
            'sign_type'     => $signtype,
            'timestamp'     => $timestamp,
            'version'       => $version,
            'notify_url'    => $notify_url,
            'return_url'    => $return_url,
            'biz_content'   => json_encode($request_param)
        ];

        $url  = 'https://openapi.alipaydev.com/gateway.do?';
//        echo '<pre>';print_r($param);echo '</pre>';
                //字典排序
                ksort($param);
//        echo '<pre>';print_r($param);echo '</pre>';
        //2.拼接 key1=value1&key2=value2...

        $str ="";
        foreach ($param as $k=>$v)
        {
            $str .= $k . '=' . $v .'&';
        }
//        echo 'str: '.$str;die;
        $str = rtrim($str,'&');
        //3 计算签名
        $key = storage_path('keys/app_priv');
        $prikey = file_get_contents($key);
        $res = openssl_get_privatekey($prikey);
//        var_dump($res);echo '</br>';
        openssl_sign($str,$sign,$res,OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);
        $param['sign'] =$sign;
//        var_dump($param);die;

        //  第四部 urlencode
        $param_str = '?';
        foreach($param as $k => $v){
            $param_str .=$k. '='.urlencode($v) . '&';
        }
        $param_str = rtrim($param_str,'&');
        $url = $ali_geteway . $param_str;
        //发送GET请求
        //echo $url;die;
        header("Location:".$url);


    }
}