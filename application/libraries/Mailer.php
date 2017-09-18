<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Pbc\Premailer;

class Mailer
{
	protected $ci;
	protected $transport;

	public function __construct()
	{
        $this->ci =& get_instance();
        $this->ci->load->config('mail');
        $this->transport = Swift_SmtpTransport::newInstance(
        	$this->ci->config->item('server'),
        	$this->ci->config->item('port'),
        	$this->ci->config->item('security'))
        	->setUsername($this->ci->config->item('from'))
        	->setPassword($this->ci->config->item('password'));
        
	}

	public function sendMail($to, $subject, $body, $cc=null, $bcc = null){
		$message = Swift_Message::newInstance();
		$body = Premailer::html($body);
		$message->setSubject($subject)
				->setFrom($this->ci->config->item('from'), $this->ci->config->item('fromName'))
				->setTo($to)
				->setBcc($bcc)
				->setCc($cc)
				->setBody($body['html'], 'text/html');
		$mailer = Swift_Mailer::newInstance($this->transport);

		$result = $mailer->send($message);

		if ($result) {
			return true;
		}else{
			return false;
		}
	}

	

}

/* End of file Mailer.php */
/* Location: ./application/libraries/Mailer.php */
