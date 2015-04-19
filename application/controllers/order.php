<?php
class Order extends CI_Controller
{
    /**
    * @todo 用于支付宝支付
    * @param array $order
    * @return void
    **/
    public function ali_pay()
    {
        //获取订单数据示例
        // $order = $this->model->get($id);
        $order = array();
        $order['product']['name'] = 'test product';
        $order['price'] = '1800';
        
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
                'total_fee' => $order['price'],
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
    * @todo 处理提交过来的订单信息的唯一入口
    * @param $_POST
    * @return void
    **/
    public function create()
    {
        //添加订单信息，状态设置为未支付
        $data['xxx'] = $_POST['xxx'];
        
        $this->load->model('order_model');
        //添加的动作返回订单的uuid
        $order['uuid'] = $this->order_model->add_order($data);
        
        // ........
        
        //根据支付方式选择处理方式
        if( $data['pay_type'] == 'alipay' ){
            $this->ali_pay($order);
        } else ($data['pay_type'] == 'yibao' ){
            $this->yibao_pay($order);
        }
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
}

























