<?php
$config['partner']      = '商户id';
$config['key']          = '商户API key';
$config['seller_email'] = '商户支付宝邮箱账号';
$config['payment_type'] = 1;
$config['transport'] = 'http';
$config['input_charset'] = 'utf-8';
$config['sign_type'] = 'MD5';
$config['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/order/ali_callback/notify';
$config['return_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/order/ali_callback/return';
$config['cacert'] = APPPATH.'third_party/alipay/cacert.pem';
?>