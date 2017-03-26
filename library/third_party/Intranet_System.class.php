<?php
/** 
*关于日常系统服务API
*例如邮件服务、短信接口服务
*
* @author lin.qianhui<linqh@wondershare.cn>
* @version 1.0  create_time:2013-11-19 20:32:22 
* @copyright  2013 @Wondershare.com
*/ 


class Intranet_System extends Intranet_Base{
    
    
    /**
    *发送系统邮件接口
    *当指定超时时间时，可能抛出异常，用于模拟多线程发送
    *
    *@param string $subject 邮件主题
    *@param string $body 邮件内容
    *@param string $sender 发送者名称
    *@param array $to_addresses 发送地址，结构为 array(array('email' => 'linqh@w.com', 'name' => 'lin'))
    *@param boolean $is_html 是否html内容， 默认false
    *@param array $reply 回复邮箱和名称信息，结构如array('email'=>'linqh@ws.com', 'name' => 'lin')
    *@param int $timeout 超时时间，默认0 
    *@param string $ip 用户IP 传入的话发送邮件接口会对同IP用户做调用频率控制
    *@return boolean 发送成功返回true，失败返回false
    */
    public function send_email($subject, $body, $sender, $to_addresses, $is_html=false, $reply=array(), $timeout=0, $ip=null){
        $data = array('subject' => $subject, 'body' => $body, 'sender' => $sender, 'addresses' => $to_addresses,
                        'is_html' => $is_html);
        if(!empty($reply)){
            $data['reply'] = $reply;
        }
        if(!empty($timeout)){
            $this->set_timeout($timeout);
        }
        if(!empty($ip)){
            $data['ip'] = $ip;
        }
        $result = $this->send('/intranet/send_email/', $data);
        return $result['status'] == Protocols::SUCCESS ? true : false;
    }
    
    
    /**
    *发送短信接口
    *
    *@param string $phone 手机号码
    *@param string $message 短信内容
    *@param string $ip 用户IP，传入的话发送短信接口会对同IP用户做调用频率控制
    *@return boolean 成功返回true，失败返回false
    */
    public function send_sms($phone, $message, $ip){
        $data = array('phone' => $phone, 'message' => $message);
        if(!empty($ip)){
            $data['ip'] = $ip;
        }
        $result = $this->send('/intranet/send_sms/', $data);
        return $result['status'] == Protocols::SUCCESS ? true : false;
    }
    
}


?>