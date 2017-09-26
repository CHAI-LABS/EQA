<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Readiness extends MY_Controller {
	public function __construct(){
		parent::__construct();

		$this->load->module('Participant');
		$this->load->model('M_Readiness');

	}

	public function authenticate($pt_uuid)
	{	
		$data['pt_uuid']	=	$pt_uuid;
		$data['type']	=	"readiness";

		$this->assets
			->addJs('dashboard/js/libs/jquery.validate.js')
            ->addJs("plugin/sweetalert/sweetalert.min.js");
        $this->assets->setJavascript('Participant/participant_login_js');
		$this->assets->addCss('css/signin.css');
		$this->template->setPageTitle('Readiness Form')->setPartial('login_v', $data)->authTemplate();
	}

	
	
	private function set_session($session_data){
		foreach ($session_data as $key => $value) {
			$this->session->set_flashdata($key,$value); 
		}	    
    }

	public function logout()
    {
        $this->session->sess_destroy();
        redirect('Home', 'refresh');
    }

	public function checkLogin($pt_uuid){
		// echo "<pre>";print_r($this->session->flashdata());echo "</pre>";
		if(empty($this->session->flashdata())){
			redirect('Participant/Readiness/authenticate/'.$pt_uuid,'refresh');
		}
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

						$this->readinessChecklist($ptround);
					}else{
						$this->session->set_flashdata('error', "Username or Password is incorrect. Please try again");
	        	redirect('Participant/Readiness/authenticate/'.$ptround, 'refresh');
					}
				}else{
					$this->session->set_flashdata('error', "Your account is not approved. Please contact the administrator");
	        	redirect('Participant/Readiness/authenticate/'.$ptround, 'refresh');
				}
			}else{
	            $this->session->set_flashdata('error', "Your account is not activated. Please your email account to activate");
	        	redirect('Participant/Readiness/authenticate/'.$ptround, 'refresh');
			}
		}else{
			$this->session->set_flashdata('error', 'To get the readiness form, click on the email address link for the PT Round');
			redirect('/', 'refresh');
		}	
	}

	function getQuestions(){
		$results = $this->db->get('questionnairs')->result();

		$question_view = '';
		$counter = 0;

		foreach ($results as $key => $questions) {
			$counter ++;
			
		 	if($questions->question_no == '4'){
				$question_view .= 	'<div id="checkNoAnswers" class="form-group">
	                				<div class="form-group row">
		                			<div class="form-group col-md-12">
		                			<label>';
		        $question_view .= 	$counter . '. ' .$questions->question;
		        $question_view .=   '</label>
		                			</div>
									</div>';
				
			}else if($questions->question_no == '4.1'){
				$counter --;

				$question_view .= 	'<div class="form-group row">
					                <div class="form-group">
					                <label class="col-md-6 form-control-label" for="question_'.$questions->question_no.'">';
				    $question_view .= 	' &nbsp (a). ' .$questions->question;
				    $questions->question_no = str_replace('.','_',$questions->question_no);
				    $question_view .=   '</label>
				                		<div class="col-md-6">
				                    	<span class="help-block text-danger">Please enter a maximum of 500 words</span>
				                    	<textarea id="question_'.$questions->question_no.'" name="question_'.$questions->question_no.'" rows="8" maxlength="500" class="form-control" placeholder="Please provide reason for any No selection..."></textarea>
				                		</div>
				            			</div>
				        				</div>';

			}else if($questions->question_no == '4.2'){
				$counter --;

				$question_view .= 	'<div class="form-group row">
	                    			<label class="col-md-6 form-control-label">';
		        $question_view .= 	' &nbsp (b). ' .$questions->question;
		        $questions->question_no = str_replace('.','_',$questions->question_no);
		        $question_view .=   '</label>
	                    			<div class="col-md-6">
	                        		<label class="radio-inline" for="question_'.$questions->question_no.'">';
            	$question_view .= 	'<input type="radio" id="question_'.$questions->question_no.'_1" name="question_'.$questions->question_no.'" value="1">Yes';
				$question_view .= 	'</label>
	    							<label class="radio-inline" for="question_'.$questions->question_no.'">';
				$question_view .= 	'<input type="radio" id="question_'.$questions->question_no.'_0" name="question_'.$questions->question_no.'" value="0">No';
				$question_view .= 	'</label>
								    </div>
									</div>
									</div>';    				

		 	}else{
		 		$question_view .= 	'<div class="form-group row">
							 		<label class="col-md-6 form-control-label">';
		 		$question_view .= 	$counter . '. ' .$questions->question;
	 			$question_view .= 	'</label>
	    							<div class="col-md-6">
	        						<label class="radio-inline" for="question_'.$questions->question_no.'">';
				$question_view .= 	'<input required type="radio" id="question_'.$questions->question_no.'_1" name="question_'.$questions->question_no.'" value="1">Yes';
				$question_view .= 	'</label>
	    							<label class="radio-inline" for="question_'.$questions->question_no.'">';
				$question_view .= 	'<input type="radio" id="question_'.$questions->question_no.'_0" name="question_'.$questions->question_no.'" value="0">No';
				$question_view .= 	'</label>
								    </div>
									</div>';
		 	}

		 }

		return $question_view;
	}


	public function readinessChecklist($pt_uuid){
		$this->checkLogin($pt_uuid);
		// echo "<pre>";print_r("PT UUID ".$pt_uuid);echo "</pre>";die();

		$data = [];

		$user_details = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->flashdata('uuid'));
		$questions_data = $this->getQuestions();
		//echo "<pre>";print_r($data['pt_uuid']);echo "</pre>";die();
        $data = [
            'user'  =>  $user_details,
            'questionnair'  =>  $questions_data,
            'pt_uuid'  =>  $pt_uuid
        ];

        $title = "Readiness Form";

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Participant/readiness_form_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('readiness_form_v', $data)
                ->readinessTemplate();
	}

	public function submitReadiness(){
		$response_array = [];
		if($this->input->post()){

			$pt_round_no = $this->input->post('ptround');
		//echo "<pre>";print_r("PT UUID".$pt_round_no);echo "</pre>";die();
            $question1 = $this->input->post('question_1');
            $question2 = $this->input->post('question_2');
            $question3 = $this->input->post('question_3');
            $question4_1 = $this->input->post('question_4_1');
            $question4_2 = $this->input->post('question_4_2');
            $question5 = $this->input->post('question_5');
            $useruuid  =   $this->session->flashdata('uuid');
            $facilityid  =   $this->session->flashdata('facilityid');
            

            $response_array[] = [
            	'1'		=>	$question1,
            	'2'		=>	$question2,
            	'3'		=>	$question3,
                '5'     =>  $question4_1,
                '6'    	=>  $question4_2,
                '7'    	=>  $question5
            ];

            $insertrounddata = [
            	'participant_id'		=>	$useruuid,
            	'pt_round_no'			=>	$pt_round_no,
            	'status'				=>	0,
                'verdict'    	  		=>  0,
                'comment'    			=>  'No comment made',
                'participant_facility'  =>  $facilityid
            ];

            $this->db->insert('participant_readiness', $insertrounddata);

            $readiness_id = $this->db->insert_id();

            foreach ($response_array as $key => $values) {
            	foreach ($values as $question_id => $response) {
	            	//echo "<pre>";print_r($value);echo "</pre>";die();
	            	$comments = NULL;

	            	if($question_id == '5'){
	            		$comments = $question4_1;
	            	}

	            	$insertresponsedata = [
		            	'readiness_id'	=>	$readiness_id,
		            	'questionnaire_id'		=>	$question_id,
		            	'response'			=>	$response,
		            	'extra_comments' => $comments
	            	];

	            	$this->db->insert('participant_readiness_responses', $insertresponsedata);
            	}	
            }

            

            redirect('/', 'refresh');
        }
	}


	
}

/* End of file Home.php */
/* Location: ./application/modules/Home/controllers/Home.php */