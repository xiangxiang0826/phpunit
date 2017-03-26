<?php

/**
 * 邮件发送
 * 
 * @author 刘通
 */
defined('CMS_LIB_ROOT') OR define('CMS_LIB_ROOT', dirname(__FILE__) . '/Libs');

class Cms_Mail
{
	private static $_instance = null;
	private $_phpmailer;
	
	private function __construct()
	{
		include CMS_LIB_ROOT . '/phpmailer/class.phpmailer.php';
		$this->_phpmailer = new PHPMailer();
		
		$this->_phpmailer->IsSMTP();
		$this->_phpmailer->IsHTML(true);
		$this->_phpmailer->SMTPAuth = true;
		$this->_phpmailer->CharSet	='UTF-8';
		$this->_phpmailer->Timeout	= 60;
	}
	
	static function getInstance()
	{
		if(null === self::$_instance)
		{
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * 群发邮件
	 * 
	 * @param array $options
	 * @param array $mails			邮件列表
	 */
	function send($options, $mails)
	{
		$this->_phpmailer->Host		= $options['host'];
		$this->_phpmailer->From		= $options['from'];
		$this->_phpmailer->Username = $options['username'];
		$this->_phpmailer->Password = $options['password'];
		$this->_phpmailer->FromName = $options['from'];
		$this->_phpmailer->Subject	= $options['subject'];
		$this->_phpmailer->Body		= $options['body'];
		$this->_phpmailer->ClearAddresses();

		foreach($mails as $mail)
		{
			$this->_phpmailer->AddAddress($mail, $mail);
		}
		
		return $this->_phpmailer->Send();
	}
}