<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Participant extends MY_Controller {
	public function __construct()
	{
		parent::__construct();

		$this->load->module('Participant');
		$this->load->model('M_Participant');
		$this->load->module('Auth');
		$this->load->model('M_Readiness');
		$this->load->library('Mailer');

	}

	// public function checkLogin($pt_uuid){
	// 	// echo "<pre>";print_r($this->session->flashdata());echo "</pre>";
	// 	if(empty($this->session->flashdata())){
	// 		redirect('Participant/Participant/authenticate/'.$pt_uuid,'refresh');
	// 	}
	// }

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
							'facilityphone'		=>	$user->facility_telephone,
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
		// $this->checkLogin($pt_uuid);
		
		$user_details = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
		$round = $this->db->get_where('pt_round_v', ['uuid' => $pt_uuid])->row();

		$participant_data = $this->db->get_where('pt_participant_review_v', 
			['participant_id' => $user_details->p_id,
			'round_id' => $round->id])->result();

		foreach ($participant_data as $key => $value) {
			
			$sample_name = $this->db->get_where('pt_samples', ['id' => $value->sample_id])->row()->sample_name;
			$sampledata .= '<strong>Sample Name</strong> : '. $sample_name . '<br/> <strong>Value Entered</strong> : ' . $value->cd4_absolute . '<br/><br/>';
		}

        $data = [
        	'sampledata' => $sampledata,
        	'pt_uuid' => $pt_uuid
        ];

		// echo "<pre>";print_r($capa_info);echo "</pre>";die();


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
			$participantuuid  =   $this->session->userdata('uuid');

			$participant = $this->db->get_where('participant_readiness_v', ['uuid' => $participantuuid])->row();

            $facilityid  =   $participant->facility_id;

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


            redirect('Dashboard/', 'refresh');
            
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


	public function Calendar(){
		$data = [];
		$counter = 0;

		$type = $this->session->userdata('type');

		$this->db->order_by('id', 'ASC');
        $rounds = $this->db->get('pt_round_v')->result();
        $round_list = '<select id="round-select" class="form-control select2-single">';
        foreach ($rounds as $round) {
            $counter++;
            if($counter == 1){
                $round_list .= '<option selected = "selected" value='.$round->uuid.'>'.$round->pt_round_no.'</option>';
                $round = $round->id;
            }else{
                $round_list .= '<option value='.$round->uuid.'>'.$round->pt_round_no.'</option>';
            }
        }
        $round_list .= '</select>';
		// echo "<pre>";print_r($this->getParticipantDashboardData($this->session->userdata('uuid')));echo "</pre>";die();

		$view = "calendar";
		$data = [
			'round_option' => $round_list,
			// 'dashboard_data'	=>	$this->getParticipantDashboardData($this->session->userdata('uuid')),
			'participant'		=>	$this->M_Participant->findParticipantByIdentifier('uuid', $this->session->userdata('uuid'))
		];
		
		$this->assets->addCss('css/main.css');
		$this->assets
				->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js')
				->addJs('dashboard/js/libs/moment.min.js')
				->addJs('dashboard/js/libs/fullcalendar.min.js')
				->addJs('dashboard/js/libs/gcal.js');
		$this->assets->setJavascript('Participant/calendar_js');
		$this->template->setPageTitle('EQA Dashboard')->setPartial($view,$data)->adminTemplate();

		$this->load->model('participant/M_Participant');
		
	}


	public function getParticipantDashboardData($participant_uuid,$round_uuid){
		$dash = $c_pt_round_no = $c_pt_type = $c_pt_from = $c_pt_to = $c_pt_uuid = '';
		$dashboard_data = new StdClass();
		$dashboard_data->current = "";
		// $this->db->where('type', 'ongoing');
		// $this->db->where('status', 'active');
		$this->db->where('uuid', $round_uuid);
		$query = $this->db->get('pt_round_v');

		$dashboard_data->calendar_legend = $this->createCalendarLegend();

		if($query->num_rows() == 1){
			$dashboard_data->rounds = $query->num_rows();
			$pt_round = $query->row();
			$readiness = $this->db->get_where('participant_readiness', ['participant_id'=>$participant_uuid, 'pt_round_no' => $pt_round->uuid])->row();
			$dashboard_data->pt_round = $pt_round;

			
			$c_pt_uuid = $dashboard_data->pt_round->uuid;
			$c_pt_round_no = $dashboard_data->pt_round->pt_round_no;
			$c_pt_type = $dashboard_data->pt_round->type;
			$c_pt_from = $dashboard_data->pt_round->to;
			$c_pt_to = $dashboard_data->pt_round->from;

			// echo "<pre>";print_r("reached 1");echo "</pre>";die();

			$today =  date('Y-m-d');

			$this->db->select('c.item_name, c.colors, ptc.date_from, ptc.date_to');
			$this->db->from('pt_calendar ptc');
			$this->db->join('calendar_items c', 'c.id = ptc.calendar_item_id');
			$this->db->join('pt_round pr', 'pr.id = ptc.pt_round_id');
			$this->db->where('pr.uuid', $pt_round->uuid);
			$this->db->where("ptc.date_from <=", $today);
			$this->db->where("ptc.date_to >=", $today);

			$calendar_query = $this->db->get();
			$dashboard_data->calendar_current = new StdClass();
			$dashboard_data->calendar_current->color = "";
			if($calendar_query->num_rows() > 1){
				$calendar_current_list = "<ul>";
				foreach ($calendar_query->result() as $value) {
					$days_left = $this->calculateDateDifference($value->date_to);

					$calendar_current_list .= "<li>$value->item_name: $days_left Left</li>";
				}
				$calendar_current_list .= "</ul>";
				$dashboard_data->calendar_current->name = $calendar_current_list;
			}elseif($calendar_query->num_rows() == 1){
				$item = $calendar_query->row();
				$days_left = $this->calculateDateDifference($item->date_to);
				$dashboard_data->calendar_current->name = $item->item_name . ": $days_left Left";
				$dashboard_data->calendar_current->color = $item->colors;

			}else{
				$dashboard_data->calendar_current->name = "No Current Item";
			}

			if($readiness){
				$this->db->where('readiness_uuid', $readiness->uuid);
				$this->db->where('pt_round_uuid', $pt_round->uuid);
				$participant_readiness = $this->db->get('pt_ready_participants')->row();
				$dashboard_data->readiness = $participant_readiness;


				if($participant_readiness){
					if($participant_readiness->status_code == 2){
						$dashboard_data->current = "enroute";
					}elseif($participant_readiness->status_code == 3){
						if($participant_readiness->receipt == 1){
							$dashboard_data->current = "pt_round_submission";
						}else{
							$dashboard_data->current = "bad_panel";
						}
					}
				}

				
			}else{
				$dashboard_data->current = "readiness";
			}
			
		}else{

			// echo "<pre>";print_r("reached 2");echo "</pre>";die();
			$dashboard_data->rounds = $query->num_rows();
		}


		$dash .= '<div class="row">
					<div class="col-md-8">
						<div class="card">
							<div class="card-header">
								<div class="col-sm-8">
						PT Round: (';

		

		// echo "<pre>";print_r($c_pt_round_no);echo "</pre>";die();
		$dash .= $c_pt_round_no;

		$dash .= ') Calendar
					</div>
					<div class="col-sm-4">';

		if($c_pt_type == "ongoing"){

		$dash .= '<a href = "'.base_url('Participant/PTRound/Round/' . $c_pt_uuid).'" class = "btn btn-primary pull-right">Open Round</a>';

		}

		$dash .= '</div>
				</div>
				<div class="card-block">
					<table class = "table table-bordered">
						<tr>
							<td>
								<p>Round Duration</p>
								<h6>
									From: ';

		$dash .= date('d/m/Y', strtotime($c_pt_from));

		$dash .= ' To ';

		$dash .= date('d/m/Y', strtotime($c_pt_to));

		$dash .= '</h6>
							</td>

							<td>
								<p>Total Days Left</p>
								<h6>';

		$date_time_to = date_create($c_pt_to);
		$data_time_now = date_create(date('Y-m-d'));
		$difference = date_diff($data_time_now, $date_time_to );

										// echo $difference->format('%a Days');

		$dash .= $difference->format('%a Days');

		$dash .= '</h6>
					</td>
					<td style="background-color: ';

		$dash .= $dashboard_data->calendar_current->color;

		$dash .= '"><p>Current Item By Calendar</p>
					<h6>';

		$dash .= $dashboard_data->calendar_current->name;

		$dash .= '</h6>
							</td>
						</tr>
					</table>

					<div id = "calendar"></div>
				</div>
			</div>
		</div>
		<div class="col-md-4" style="position:fixed;top: 20%;right: -5%;">
			<div class="card">
				<div class="card-block">
					<h5 class = "mb-1">Legend</h5>
					<hr>';

		$dash .= $dashboard_data->calendar_legend;

		$dash .= '</div>
							</div>
						</div>
					</div>';

		return $this->output->set_content_type('application/json')->set_output(json_encode($dash));
		// return $dashboard_data;
	}


	public function getCalendarData(){
        if ($this->input->is_ajax_request()) {
            $uuid = $this->input->post('round_id');
            $pt_round = $this->db->get_where('pt_round_v', ['uuid'   =>  $uuid])->row();

            $this->db->select('ci.item_name, ci.colors, pt.date_from, pt.date_to');
            $this->db->from('pt_calendar pt');
            $this->db->join('calendar_items ci', 'ci.id = pt.calendar_item_id');
            $result = $this->db->get()->result();
            $event_data = [];
            if($result){
                foreach ($result as $cal_data) {
                    $event_data[] = [
                        'title' =>  $cal_data->item_name,
                        'start' =>  $cal_data->date_from,
                        'end'   =>  date('Y-m-d', strtotime($cal_data->date_to. "+1 days")),
                        'backgroundColor' =>  $cal_data->colors,
                        'rendering' => 'background'

                    ];
                }
            }

            return $this->output->set_content_type('application/json')->set_output(json_encode($event_data));
        }
    }


	function calculateDateDifference($date1, $date2 = NULL, $format = NULL){
		$date_time_1 = date_create($date1);
		$date_time_2 = ($date2 == NULL) ? date_create(date('Y-m-d')) : date_create($date2);

		$interval = date_diff($date_time_1, $date_time_2);
		$format = ($format == NULL) ? '%a Days' : $format;
		$difference = $interval->format($format);

		return $difference;
	}


	private function createCalendarLegend(){
		$calendar_items = $this->db->get('calendar_items')->result();
		$calendar_legend = "";
		if ($calendar_items) {
			foreach ($calendar_items as $calendar_item) {
				$calendar_legend .= "<div class = 'm-1 clearfix'>
				<div class = 'pull-left' style = 'width: 30px;height:30px;background-color: {$calendar_item->colors};opacity: .3;'></div>
				<p class = 'pull-left ml-1'>{$calendar_item->item_name}</p>
				</div>";
			}
		}
		
		return $calendar_legend;
	}



}

/* End of file Participant.php */
/* Location: ./application/modules/Participant/controllers/Participant.php */