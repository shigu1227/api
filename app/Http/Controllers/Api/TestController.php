<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use App\Model\UserModel;

class TestController extends Controller
{
    public function test()
    {
        echo '<pre>';print_r($_SERVER);echo '</pre>';
    }
    /**
     * 用户注册
     */
    public function reg0(Request $request)
    {
        echo '<pre>';print_r($request->input());echo '</pre>';
        //验证用户名 验证email 验证手机号
        $pass1 = $request->input('pass1');
        $pass2 = $request->input('pass2');
        if($pass1 != $pass2){
            die("两次输入的密码不一致");
        }
        $password = password_hash($pass1,PASSWORD_BCRYPT);
        $data = [
            'email'         => $request->input('email'),
            'name'          => $request->input('name'),
            'password'      => $password,
            'mobile'        => $request->input('mobile'),
            'last_login'    => time(),
            'last_ip'       => $_SERVER['REMOTE_ADDR'],     //获取远程IP
        ];
        $uid = UserModel::insertGetId($data);
        var_dump($uid);
    }

    /**
     * 用户登录接口
     * @param Request $request
     * @return array
     */
    public function login0(Request $request)
    {
        $name = $request->input('name');
        $pass = $request->input('pass');
        $u = UserModel::where(['name'=>$name])->first();
        if($u){
            //验证密码
            if( password_verify($pass,$u->password) ){
                // 登录成功
                //echo '登录成功';
                //生成token
                $token = Str::random(32);
                $response = [
                    'errno' => 0,
                    'msg'   => 'ok',
                    'data'  => [
                        'token' => $token
                    ]
                ];
            }else{
                $response = [
                    'errno' => 400003,
                    'msg'   => '密码不正确'
                ];
            }
        }else{
            $response = [
                'errno' => 400004,
                'msg'   => '用户不存在'
            ];
        }
        return $response;
    }
    /**
     * 获取用户列表
     *
     */
    public function userList0()
    {
        $user_token = $_SERVER['HTTP_TOKEN'];
        echo 'user_token: '.$user_token;echo '</br>';
        $current_url = $_SERVER['REQUEST_URI'];
        echo "当前URL: ".$current_url;echo '<hr>';
        //echo '<pre>';print_r($_SERVER);echo '</pre>';
        //$url = $_SERVER[''] . $_SERVER[''];
        $redis_key = 'str:count:u:'.$user_token.':url:'.md5($current_url);
        echo 'redis key: '.$redis_key;echo '</br>';
        $count = Redis::get($redis_key);        //获取接口的访问次数
        echo "接口的访问次数： ".$count;echo '</br>';
        if($count >= 5){
            echo "请不要频繁访问此接口，访问次数已到上限，请稍后再试";
            Redis::expire($redis_key,3600);
            die;
        }
        $count = Redis::incr($redis_key);
        echo 'count: '.$count;
    }

    public function decrypt0()
    {
        $data = base64_encode($_GET['data ']);
        $method = "AES-256-CBC";
        $key = '1905api';
        $iv = 'SHIGUSHISU011227';

        $dec_data = openssl_decrypt($data,$method,$key,OPENSSL_RAW_DATA,$iv);
        echo "解密:".$dec_data;echo '</br>';

    }

    /**
     * APP注册
     * @return bool|string
     */
    public function reg()
    {
        //请求passport
        $url = 'http://api.1905pass.com/user/reg';
        $response = UserModel::curlPost($url,$_POST);
        return $response;
    }
    /**
     * APP 登录
     */
    public function login()
    {
        //请求passport
        $url = 'http://api.1905pass.com/user/login';
        $response = UserModel::curlPost($url,$_POST);
        return $response;
    }
    public function showData()
    {
        // 收到 token
        $uid = $_SERVER['HTTP_UID'];
        $token = $_SERVER['HTTP_TOKEN'];
        // 请求passport鉴权
        $url = 'http://api.1905pass.com/user/token';         //鉴权接口
        $response = UserModel::curlPost($url,['uid'=>$uid,'token'=>$token]);
        echo $response;die;
        $status = json_decode($response,true);
        //处理鉴权结果
        if($status['errno']==0)     //鉴权通过
        {
            $data = "sdlfkjsldfkjsdlf";
            $response = [
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => $data
            ];
        }else{          //鉴权失败
            $response = [
                'errno' => 40003,
                'msg'   => '授权失败'
            ];
        }
        return $response;
    }


    public function postman()
    {
        echo __METHOD__;
    }

    public function postman1()
    {
        //获取用户标识
        $token = $_SERVER['HTTP_TOKEN'];
        // 当前url
        $request_uri = $_SERVER['REQUEST_URI'];

        $url_hash = md5($token . $request_uri);


//        echo 'url_hash: ' .  $url_hash;echo '</br>';
        $key = 'count:url:'.$url_hash;
//        echo 'Key: '.$key;echo '</br>';

        //检查 次数是否已经超过限制
        $count = Redis::get($key);
        echo "当前接口访问次数为：". $count;echo '</br>';

        if($count >= 5){
            $time = 10;     // 时间秒
            echo "请勿频繁请求接口, $time 秒后重试";
            Redis::expire($key,$time);
            die;
        }

        // 访问数 +1
        $count = Redis::incr($key);
        echo 'count: '.$count;

    }

    public function md5test()
    {
        $data = "Hello world";      //要发送的数据
        //计算签名的key 和接收保持一致
        $key = "1905";

        //计算签名  MD5($data . $key)
        $signature = md5($data . $key);
//        $signature = 'sdlfkjsldfkjsfd';

        echo "待发送的数据：". $data;echo '</br>';
        echo "签名：". $signature;echo '</br>';

        //发送数据
        $url = "http://api.1905pass.com/test/check?data=".$data . '&signature='.$signature;
        echo $url;echo '<hr>';

        $response = file_get_contents($url);
        echo $response;
    }

    public function sign2()
    {
        //计算签名的key 和接收保持一致  使用同一个key 计算签名
        $key = "1905";

        //待签名的数据
        $order_info = [
            "order_id"          => 'LN_' . mt_rand(111111,999999),
            "order_amount"      => mt_rand(111,999),
            "uid"               => 12345,
            "add_time"          => time(),
        ];

        $data_json = json_encode($order_info);

        //计算签名
        $sign = md5($data_json.$key);

        // post 表单（form-data）发送数据
        $client = new Client();
        $url = 'http://api.1905pass.com/test/check2';
        $response = $client->request("POST",$url,[
            "form_params"   => [
                "data"  => $data_json,
                "sign"  => $sign
            ]
        ]);

        //接收服务器端响应的数据
        $response_data = $response->getBody();
        echo $response_data;

    }

    //非对称加密
    public function sign3(){
        //代签名的数据
        $data='世故1229';

        //密钥路劲
        $path=storage_path('keys/priv_key');

        //获取公钥
        $pkeyid=openssl_pkey_get_private("file://".$path);
        // dump($pkeyid);die;
        //非对称加密算法
        openssl_sign($data,$signature,$pkeyid);

        // 释放密钥资源
        openssl_free_key($pkeyid);

        //base64 编码 方便传输
        $sign_str=base64_encode($signature);
        $sign_url=urlencode($sign_str);
        $url='http://api.1905pass.com/test/check3?data='.$data.'&sign='.$sign_url;
//        echo $url;die;
        $get=file_get_contents($url);
        echo $get;

    }

    //对称加密
    public function aes(){
        //要加密的数据
        $str='世俗1229';
        $method='AES-256-CBC';  //加密方式
        $key='zhangxiaoleshiguxyx';    //加密的密钥
        $iv='zhangxiaolexyxaa';       //必须为16位

        //加密函数进行加密
        $enc=openssl_encrypt($str,$method,$key,OPENSSL_RAW_DATA,$iv);
        echo $enc;
        //不可读模式转换成可读模式
        $str_base=base64_encode($enc);
        //转换为可传输数据类型
        $str_url=urlencode($str_base);
        $url='http://api.1905pass.com/test/aes?str='.$str_url;
        $g=file_get_contents($url);
        echo $g;
    }


}
