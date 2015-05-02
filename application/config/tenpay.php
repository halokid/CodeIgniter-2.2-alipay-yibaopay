<?php

/**
$spname="财付通双接口测试";
$partner = "";                                  	//财付通商户号
$key = "";											//财付通密钥

$return_url = "http://xxx/payReturnUrl.php";			//显示支付结果页面
$notify_url = "http://xxx/payNotifyUrl.php";			//支付完成后的回调处理页面

**/

$config['partner']      = '财付通商户号';
$config['key']          = '财付通密钥';
$config['seller_email'] = '商户支付宝邮箱账号';
// $config['payment_type'] = 1;
// $config['transport'] = 'http';
// $config['input_charset'] = 'utf-8';
// $config['sign_type'] = 'MD5';
$config['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/order/tenpay_callback/notify';
$config['return_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/order/tenpay_callback/return';
// $config['cacert'] = APPPATH.'third_party/alipay/cacert.pem';
?>