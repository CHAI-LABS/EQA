<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Participant extends MY_Controller {
	public function __construct()
	{
		parent::__construct();

		$this->load->module('Participant');
		$this->load->model('M_Participant');
		$this->load->model('M_Readiness');
		$this->load->library('Mailer');

	}

	public function checkLogin($pt_uuid){
		// echo "<pre>";print_r($this->session->flashdata());echo "</pre>";
		if(empty($this->session->flashdata())){
			redirect('Participant/Participant/authenticate/'.$pt_uuid,'refresh');
		}
	}

	public function authenticate($pt_uuid)
	{	
		$data['pt_uuid']	=	$pt_uuid;
		$data['type']	=	"capa";

		$this->assets
			->addJs('dashboard/js/libs/jquery.validate.js')
            ->addJs("plugin/sweetalert/sweetalert.min.js");
        $this->assets->setJavascript('Participant/participant_login_js');
		$this->assets->addCss('css/signin.css');
		$this->template->setPageTitle('Form')->setPartial('login_v', $data)->authTemplate();
	}


	public function authentication(){
		$ptround = $this->input->post('ptround');

		$user = $this->M_Readiness->findParticipant($this->input->post('username'));
		// echo "<pre>";print_r($user);echo "</pre>";die();
		
		if ($user) {
			
			if($user->status == 1){
				if($user->approved == 1){
			$this->load->library('Hash');

					if (password_verify($this->input->post('password'), $user->password)) {
						
						$session_data = [
							'uuid'				=>	$user->uuid,
							'username'			=>	$user->username,
							'firstname'			=>	$user->firstname,
							'lastname'			=>	$user->lastname,
							'phone'				=>	$user->phone,
							'emailaddress'		=>	$user->email_address,
							'facilityid'		=>	$user->facility_id,
							'facilitycode'		=>	$user->facility_code,
							'facilityname'		=>	$user->facility_name,
							'facilityphone'		=>	$user->telephone,
							'facilityaltphone'	=>	$user->alt_telephone,
							'is_logged_in'		=>	true
						];



						$this->set_session($session_data);

						$this->CapaForm($ptround);
					}else{
						$this->session->set_flashdata('error', "Username or Password is incorrect. Please try again");
	        	redirect('Participant/Participant/authenticate/'.$ptround, 'refresh');
					}
				}else{
					$this->session->set_flashdata('error', "Your account is not approved. Please contact the administrator");
	        	redirect('Participant/Participant/authenticate/'.$ptround, 'refresh');
				}
			}else{
	            $this->session->set_flashdata('error', "Your account is not activated. Please your email account to activate");
	        	redirect('Participant/Participant/authenticate/'.$ptround, 'refresh');
			}
		}else{
			$this->session->set_flashdata('error', 'To get the capa form, click on the email address link for the PT Round');
			redirect('/', 'refresh');
		}	
	}

	private function set_session($session_data){
		foreach ($session_data as $key => $value) {
			$this->session->set_flashdata($key,$value); 
		}	    
    }




	public function CapaForm($pt_uuid){

		$sampledata = '';
		$this->checkLogin($pt_uuid);
		
		$user_details = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->flashdata('uuid'));
		$round = $this->db->get_where('pt_round_v', ['uuid' => $pt_uuid])->row();

		$participant_data = $this->db->get_where('pt_participant_review_v', ['participant_id' => $user_details->p_id,'round_id' => $round->id])->result();

		foreach ($participant_data as $key => $value) {
			// echo "<pre>";print_r($value);echo "</pre>";die();
			$sample_name = $this->db->get_where('pt_samples', ['id' => $value->sample_id])->row()->sample_name;
			$sampledata .= '<strong>Sample Name</strong> : '. $sample_name . '<br/> <strong>Value Entered</strong> : ' . $value->cd4_absolute . '<br/><br/>';
		}

        $data = [
        	'sampledata' => $sampledata,
        	'pt_uuid' => $pt_uuid
        ];

        $title = "CAPA Form";

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Participant/capa_form_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('capa_form_v', $data)
                ->readinessTemplate();
	}


	public function submitCAPA(){
		$response_array = [];
		if($this->input->post()){

			$round_uuid = $this->input->post('ptround');
			$participantuuid  =   $this->session->flashdata('uuid');
            $facilityid  =   $this->session->flashdata('facilityid');

            $occurrence = $this->input->post('occurrence');
            $cause = $this->input->post('cause');
            $correction = $this->input->post('correction');
            $effective = $this->input->post('effective');
            $prevention = $this->input->post('prevention');

            $tests = $this->input->post('tests');

            $attributes = $this->input->post('attribute');
            $other = $this->input->post('other');

			// echo "<pre>";print_r($tests);echo "</pre>";die();

            $insertcapadata = [
            	'round_uuid'		=>	$round_uuid,
            	'participant_uuid'			=>	$participantuuid,
            	'facility_id'				=>	$facilityid,
                'occurrence'    	  		=>  $occurrence,
                'cause'    			=>  $cause,
                'correction'  =>  $correction,
                'effective'    			=>  $effective,
                'prevention'  =>  $prevention,
                'approved'  =>  0,
                'status'  =>  0
            ];


            if($this->db->insert('capa_response', $insertcapadata)){
            	$capa_id = $this->db->insert_id();

            	foreach ($tests as $test) {
	            	$inserttestsedata = [
		            	'capa_test_id'	=>	$capa_id,
		            	'applied_test'		=>	$test
	            	];
	            	$this->db->insert('capa_tests', $inserttestsedata);
            	}

            	foreach ($attributes as $attribute) {
            		if($attribute == "Other"){
            			$insertattributesdata = [
			            	'capa_attribute_id'		=>	$capa_id,
			            	'attribute_factor'		=>	$attribute,
			            	'specific_other'	=>	$other
	            		];
            		}else{
            			$insertattributesdata = [
			            	'capa_attribute_id'		=>	$capa_id,
			            	'attribute_factor'		=>	$attribute,
			            	'specific_other'	=>	''
		            	];
            		}
	            	
	            	$this->db->insert('capa_attributes', $insertattributesdata);
            	}	
	            
            }


            redirect('/', 'refresh');
        }
	}


	function register(){
		if ($this->input->server('REQUEST_METHOD') == "POST") {
			$facility = $this->input->post('facility');
			$participant_id = $this->generateParticipantID($facility);

			//echo "<pre>";print_r($participant_id);echo "</pre>";die();

			$surname = $this->input->post('surname');
			$firstname = $this->input->post('firstname');
			$emailaddress = $this->input->post('email_address');
			$phonenumber = $this->input->post('phonenumber');

			$sex = $this->input->post('sex');
			$age = $this->input->post('age');
			$education = $this->input->post('education');
			$experience = $this->input->post('experience');
			
			$usertype = $this->input->post('usertype');
			$password = $this->input->post('password');

			$token =  $this->hash->hashPassword(bin2hex(openssl_random_pseudo_bytes(16)));
			$participant_insert = [
				'participant_id'			=>	$participant_id,
				'participant_lname'			=>	$surname,
				'participant_fname'			=>	$firstname,
				'participant_phonenumber'	=>	$phonenumber,
				'participant_email'			=>	$emailaddress,
				'participant_sex'			=>	$sex,
				'participant_age'			=>	$age,
				'participant_education'	    =>	$education,
				'participant_experience'	=>	$experience,
				'participant_password'		=>	$this->hash->hashPassword($password),
				'confirm_token'				=>	$token,
				'user_type'				    =>	$usertype,
				'participant_facility'		=>	$facility
			];

			$encoded_token = urlencode($token);
			$verification_url = $this->config->item('server_url') . 'Auth/verify/' . $emailaddress . '/' . $encoded_token;

			$this->db->insert('participants', $participant_insert);
			$id = $this->db->insert_id();

			$equipment = $this->input->post('equipment');
			$equipment_insert = [];
			foreach ($equipment as $equipment_id) {
				$equipment_insert[] = [
					'participant_id'	=>	$id,
					'equipment_id'		=>	$equipment_id
				];
			}

			$this->db->insert_batch('participant_equipment', $equipment_insert);

			$picgok = @$this->config->item('server_url') . 'assets/frontend/images/files/gok.png';
			$picministry = @$this->config->item('server_url') . 'assets/frontend/images/files/ministry.png';

			$data = [
				'participant_name'	=>	$surname . " " . $firstname,
				'url'				=>	$verification_url,
				'picgok' 			=> $picgok,
				'picministry' 		=> $picministry

			];

			$body = $this->load->view('Template/email/signup_v', $data, TRUE);
			$sent = $this->mailer->sendMail($emailaddress, "Registration Complete", $body);
			if ($sent == FALSE) {
				log_message('error', "The system could not send an email to {$emailaddress}. Participant Name: $surname $firstname at " . date('Y-m-d H:i:s'));
			}

			redirect('Auth/completeSignUp/' . $emailaddress);
		}else{
			redirect('Auth/signUp','refresh');
		}
	}

	private function generateParticipantID($facility_id){
		$prefix = $this->M_Participant->getFacilityCode($facility_id)->facility_code;

		$max_id = $this->M_Participant->getMaxParticipant()->highest;
		if (!$max_id) {
			$max_id = 0;
		}

		$next = $max_id + 1;
		//echo "<pre>";print_r($prefix);echo "</pre>";die();

		return $prefix."-".str_pad($next, 3, "0", STR_PAD_LEFT);
		
	}



}

/* End of file Participant.php */
/* Location: ./application/modules/Participant/controllers/Participant.php */