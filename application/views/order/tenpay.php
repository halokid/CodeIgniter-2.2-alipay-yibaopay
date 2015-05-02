<?php
//echo $body;
?>

<!-- 
<form id='alipaysubmit' name='alipaysubmit' action='https://mapi.alipay.com/gateway.do?_input_charset=utf-8' method='post'><input type='hidden' name='_input_charset' value='utf-8'/><input type='hidden' name='body' value='test product'/><input type='hidden' name='notify_url' value='http://localhost/order/callback/notify'/><input type='hidden' name='out_trade_no' value='yasoooxxxxx817262'/><input type='hidden' name='partner' value='商户id'/><input type='hidden' name='payment_type' value='1'/><input type='hidden' name='return_url' value='http://localhost/order/callback/return'/><input type='hidden' name='seller_email' value='商户支付宝邮箱账号'/><input type='hidden' name='serivce' value='create_direct_pay_by_user'/><input type='hidden' name='show_url' value='http://localhost/product/view/Array'/><input type='hidden' name='subject' value='test product'/><input type='hidden' name='total_fee' value='1800'/><input type='hidden' name='sign' value='e89f06542c2283870f28d28e61962948'/><input type='hidden' name='sign_type' value='MD5'/><input type='submit' value='' style="display:none"></form>

-->


<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>财付通即时到帐程序演示</title>
</head>
<body>
<br/>


<form action="<?php echo $action; ?>" method="post" target="_blank">
<?php
// $params = $reqHandler->getAllParameters();
foreach($params as $k => $v) {
	echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
}
?>
<input type="submit" value="财付通支付">
</form>
</body>
</html>



