<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

	public function __construct(){
		parent::__construct();

		
		$this->load->model('auth_m');
	}

	
	
	public function signup()
	{
		$this->assets->addCss('css/signup.css');
		$this->assets->addJs('plugin/select2/js/select2.full.min.js')
						->addJs('dashboard/js/libs/jquery.validate.js');;
		$this->assets->setJavascript('Auth/authjs');
		$this->template->setPageTitle('External Quality Assurance Programme')->setPartial('signup')->authTemplate();
	}

	public function signin()
	{
		$this->assets->addCss('css/signin.css');
		$this->template->setPageTitle('External Quality Assurance Programme')->setPartial('signin')->authTemplate();
	}

	public function verify($email, $token){
		$this->load->module('Participant');
		$token = urldecode($token);
		$user = $this->M_Participant->findParticipantByIdentifier('participant_email', $email);
		if($user){
			if ($token == $user->confirm_token) {
				$update_data = [
					'confirm_token'	=>	NULL,
					'status'		=>	1
				];

				$this->db->update('participants', $update_data);
				redirect('Auth/signin','refresh');
			}else{
				echo "Invalid Token";
			}
		}else{
			echo "Email not registered";
		}
	}

	public function firstTime($uuid){
		$user = $this->auth_m->findUserByIdentifier('uuid', $uuid);
		if($user){
			if($user->password == "" && $user->status == 0){
				$data['uuid'] = $uuid;
				$this->assets->addCss('css/signup.css');
				$this->assets->addJs('dashboard/js/libs/jquery.validate.js');
				$this->assets->setJavascript('Auth/authjs');
				return $this->template->setPageTitle('External Quality Assurance Programme')->setPartial('firsttime_v', $data)->authTemplate();
			}elseif($user->password != "" && $user->status == 1){
				redirect("Auth/signin");
			}else{
				echo "There was an error";
			}
		}{
			echo "No user found!";die;
		}
	}

	function userComplete($uuid){
		$user = $this->auth_m->findUserByIdentifier('uuid', $uuid);
		if($user){
			if($this->input->post('password') == $this->input->post('confirm_password')){
				$hashed_password = $this->hash->hashPassword($this->input->post('password'));
				$username = $this->input->post('username');

				$this->load->module('API/Users');
				$continue = $this->users->checkExist($username);

				if($continue == true){
					$insert_data = [
						'password'	=>	$hashed_password,
						'username'	=>	$username,
						'status'	=>	1
					];

					$this->db->where('uuid', $uuid);
					$this->db->update('user', $insert_data);
				}else{
					$this->session->set_flashdata('error', "The username already exists");
					redirect('Auth/firstTime/' . $uuid);
				}
				redirect('Auth/signin', 'refresh');
			}else{
				$this->session->set_flashdata('error', "The passwords you entered do not match");
				redirect('Auth/firstTime/' . $uuid);
			}
		}else{
			show_404();
		}
	}

	public function participantLogin(){
		$participant = $this->auth_m->check_participant_exist();
		$this->load->library('Hash');
		if($participant){
			if (password_verify($this->input->post('password'), $participant->participant_password)) {
				$session_data = array(
		        	'id'	=>	$participant->uuid
		        );

		        $this->set_session($session_data);
				redirect('Dashboard','refresh');
			}	
		}else{
			$this->session->set_flashdata('error', 'Username or Password is incorrect. Please try again');
			redirect('Auth/signin');
		}
	}

	public function authenticate(){
		$user = $this->auth_m->findUser($this->input->post('username'));
		if ($user) {
			$this->load->library('Hash');
			if (password_verify($this->input->post('password'), $user->password) && $user->approved == 1 && $user->status == 1) {
				$session_data = [
					'uuid'			=>	$user->uuid,
					'type'			=>	$user->user_type,
					'is_logged_in'	=>	true
				];

				$this->set_session($session_data);
				redirect('Dashboard', 'refresh');
			}
		}
		$this->session->set_flashdata('error', 'Username or Password is incorrect. Please try again');
		redirect('Auth/signin', 'refresh');
	}

	private function set_session($session_data){
		$this->session->set_userdata($session_data);        
    }

    public function completeSignUp($email){
    	$data['email'] = $email;
    	$this->template->setPageTitle('Complete Registration')
    					->setPartial('Auth/complete_sign_up_v', $data)
    					->frontEndTemplate();
    }



	public function logout()
    {
        $this->session->sess_destroy();
        redirect('Auth/signin');
    }

    public function checkLogin(){
		if($this->session->userdata('is_logged_in') != true){
			redirect('Auth/signin/','refresh');
		}
	}

	
}

/* End of file Home.php */
/* Location: ./application/modules/Home/controllers/Home.php */