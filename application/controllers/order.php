<?php
class Order extends Ysd
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('order_info_model');
		$this->load->model('cart_model');
		$this->load->model('users_model');
	}

	/**
	 * @todo  create an order & send payment info
	 * @param void
	 * @return void
	 */
	public function create()
	{
		if( $_POST ){
			$this->debug_dump($_POST, '$_POST');
//			$this->debug_dump($_COOKIE['cart'], '$_COOKIE');
			$goods_arr = unserialize($_COOKIE['cart']);
			$this->debug_dump($goods_arr, '$goods_arr');
			//取得收货的地址
			$add_id = $_POST['address_id'];
			$add_info = $this->cart_model->address_row($add_id);
			$this->debug_dump($add_info, '$add_info');

			//设置订单的相关变量
			/**
            Array ( [address_id] => 37 [address_name] => [user_id] => 1 [consignee] => 请问健康氨基酸打卡机 [email] => 23412@1231.com [country] => 1 [province] => 7 [city] => 98 [district] => 866 [address] => 气我诶哦亲io空间sadly [zipcode] => [tel] => 56465423546 [mobile] => [sign_building] => [best_time] => [default] => 1 [province_name] => 广西 [city_name] => 桂林 [district_name] => 叠彩区 ) 
            **/
			$data = array();
			//用户信息
			$user_id = $_SESSION['user_id'];
			$data['user_id'] = $user_id;
			$data['consignee'] = $add_info['consignee'];
			$data['country'] = $add_info['country'];
			$data['province'] = $add_info['province'];
			$data['city'] = $add_info['city'];
			$data['district'] = $add_info['district'];
			$data['address'] = $add_info['address'];
			$data['zipcode'] = $add_info['zipcode'];
			$data['tel'] = $add_info['tel'];
			$data['mobile'] = $add_info['mobile'];
			$data['email'] = $add_info['email'];
			
			//配送信息
			$data['shipping_id'] = $_POST['shipping_id'];
			$this->load->model('shipping_model');
			$data['shipping_name'] = $this->shipping_model->get_shipping_name($data['shipping_id']);
			
			//支付信息
			$data['pay_id'] = 1;
			$data['pay_name'] = $_POST['pay_type'];
			
			//商品信息
			$data['goods_amount'] = $_POST['goods_amount'];
			$data['shipping_fee'] = $_POST['shipping_fee'];
			//订单确认时间
			$data['confirm_time'] = time();
			
			$this->debug_dump($data, '$data');
			
			//添加订单信息
			//.....................
			$this->load->model('order_info_model');
			$order_uuid = $this->order_info_model->add($data);
			$this->debug_dump($order_uuid, '$order_uuid');


			//传递给支付接口的信息
			$order = array();
			$order['uuid'] = $order_uuid;
			$order['amount'] = $data['goods_amount'] + $data['shipping_fee'];
			$this->debug_dump($order, '$order');
			
			//根据支付方式选择处理方式
			if( $_POST['pay_type'] == 'alipay'){
				$this->ali_pay($order);
			} else if ( $_POST['pay_type'] =='yibao' ) {
				$this->yibao_pay($order);
			}

		}else{
			redirect();
		}
	}

	/**
	 * @todo 显示订单
	 */
	public function lists()
	{
		$this->order_info_model->info();
	}

	/**
	 * @todo del order
	 */
	public function del()
	{
		$order_id = $_POST['order_id'];
		if(isset($order_id)){
			$order_id = trim($order_id);
			$this->order_info_model->del($order_id);
			echo 'success';
		}
	}
	
	
	/**
    * @todo 用于支付宝支付
    * @param array $order
    * @return void
    **/
    public function ali_pay($order)
    {
        //获取订单数据示例
        // $order = $this->model->get($id);
//        $order = array();
//        $order['product']['name'] = 'test product';
//        $order['price'] = '1800';
        
        //加载支付宝配置
        $this->config->load('alipay', TRUE);
        //加载支付宝支付请求类库
        require_once( APPPATH."third_party/alipay/alipay_submit.class.php" );
        
        $submit = new AlipaySubmit( $this->config->item('alipay'));
        
        $body = $submit->buildRequestForm( array(
                'serivce' => 'create_direct_pay_by_user',
                'partner' => $this->config->item('partner', 'alipay'),
                'payment_type' => $this->config->item('payment_type', 'alipay'),
                'notify_url' => $this->config->item('notify_url', 'alipay'),
                'return_url' => $this->config->item('return_url', 'alipay'),
                'seller_email' => $this->config->item('seller_email', 'alipay'),
                'out_trade_no' => $order['uuid'], //订单唯一编号uuid
                'subject' => $order['uuid'], //这个订单名称， 这里用订单uuid，我们没名称
                'total_fee' => $order['amount'],
                //'body' => $order['description'],  //订单描述
                //订单详情的显示地址
                'show_url' => 'http://'.$_SERVER['HTTP_HOST'].'/order/list/'.$order['uuid'], 
                'anti_phishing_key' => '',
                'exter_invoke_ip' => '',
                '_input_charset' => $this->config->item('input_charset', 'alipay')
        ), 'post', '' );
        
        // echo $body;
        $data = array();
        $data['body'] = $body;
        
        $this->load->view('order/pay', $data);
    }
    
    
    /**
    * @todo 处理支付宝的callback逻辑
    * @param string $method
    * @return void
    **/
    public function ali_callback ( $method )
    {
        //加载支付宝配置
        $this->config->load('alipay', TRUE);
        //加载支付宝返回通知类
        require_once( APPPATH."third_party/alipay/alipay_notify.class.php");
        //初始化支付宝返回通知类
        $alipayNotify = new AlipayNotify( $this->config->item('alipay'));
        
        $input = array();
        $is_ajax = FALSE;
        $notify_status = 'success';
        
        //这里做同步还是异步的判断并获取返回数据验证请求
        switch ( $method ){
            case 'notify':
                $result = $alipayNotify->verifyNotify();
                $input = $this->input->post();
                $is_ajax = TRUE;
                break;
                
            case 'return':
                $result = $alipayNotify->verifyReturn();
                $input = $this->input->get();
                break;
            default:
                return $this->out_not_found();
                break;
        }
        
        //支付宝返回支付成功和交易结束标志
        if( $result && ($input['trade_status'] == 'TRADE_FINISHED' || $input['trade_status'] == 'TRADE_SUCCESS' ) ){
            $id = $input['out_trade_no'];
            
            //验证成功则更新订单信息
            // ..........
            
            
        }else{
            //否则置状态为失败
            $notify_status = 'fail';
        }
        
        
        if( $is_ajax ){
            //异步方式调用模板输出状态
            $this->view->load('alipay', array('status' => $notify_status));
        }else{
            //同步方式跳转到订单详情控制器，redirect方法要你自己写
            return $this->redirect("order/view/$id#status:$notify_status");
        }
    }
    
    
    
    /**
    * @todo 用于财付通支付
    * @param array $order
    * @return void
    **/
    public function tenpay()
    {
        //加载财付通配置
        $this->config->load('tenpay', TRUE);
        require_once (APPPATH."third_party/tenpay/classes/RequestHandler.class.php");

        /* 获取提交的订单号 */
        $out_trade_no = $_REQUEST["order_no"];
        /* 获取提交的商品名称 */
        $product_name = $_REQUEST["product_name"];
        /* 获取提交的商品价格 */
        $order_price = $_REQUEST["order_price"];
        /* 获取提交的备注信息 */
        $remarkexplain = $_REQUEST["remarkexplain"];
        /* 支付方式 */
        $trade_mode=$_REQUEST["trade_mode"];

        $strDate = date("Ymd");
        $strTime = date("His");

        /* 商品价格（包含运费），以分为单位 */
        $total_fee = $order_price*100;

        /* 商品名称 */
        $desc = "商品：".$product_name.",备注:".$remarkexplain;

        /* 创建支付请求对象 */
        $reqHandler = new RequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($this->config->item('key', 'tenpay'));
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

        //----------------------------------------
        //设置支付参数 
        //----------------------------------------
        $reqHandler->setParameter("partner", $this->config->item('partner', 'tenpay'));
        $reqHandler->setParameter("out_trade_no", $out_trade_no);
        $reqHandler->setParameter("total_fee", $total_fee);  //总金额
        $reqHandler->setParameter("return_url", $this->config->item('return_url', 'tenpay'));
        $reqHandler->setParameter("notify_url", $this->config->item('notify_url', 'tenpay'));
        $reqHandler->setParameter("body", $desc);
        $reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
        //用户ip
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
        $reqHandler->setParameter("fee_type", "1");               //币种
        $reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）

        //系统可选参数
        $reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
        $reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
        $reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

        //业务可选参数
        $reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", "");        	  //商品费用
        $reqHandler->setParameter("transport_fee", "0");      	  //物流费用
        $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
        $reqHandler->setParameter("time_expire", "");             //订单失效时间
        $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        $reqHandler->setParameter("goods_tag", "");               //商品标记
        $reqHandler->setParameter("trade_mode",$trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc","");              //物流说明
        $reqHandler->setParameter("trans_type","1");              //交易类型
        $reqHandler->setParameter("agentid","");                  //平台ID
        $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id","");                //卖家的商户号



        //请求的URL
        $reqUrl = $reqHandler->getRequestURL();

        //获取debug信息,建议把请求和debug信息写入日志，方便定位问题
        /**/
        $debugInfo = $reqHandler->getDebugInfo();
        echo "<br/>" . $reqUrl . "<br/>";
        echo "<br/>" . $debugInfo . "<br/>";
        
        // echo $body;
        $data = array();
        
        $action = $reqHandler->getGateUrl() ;
        $params = $reqHandler->getAllParameters();
        
        $data['action'] = $action;
        $data['params'] = $params;
        
        $this->load->view('order/tenpay', $data);
    }
    
    
    
    
    
    
    
}