<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends DashboardController {
	public function __construct(){
		parent::__construct();

		$this->load->library('table');
        $this->load->config('table');
		$this->load->model('dashboard_m');
		$this->load->module('Participant');
		$this->load->model('M_PTRound');

	}
	
	public function index()
	{	
		$data = [];

		$type = $this->session->userdata('type');
		$this->assets->addCss('css/main.css');
		$this->assets->addJs('js/main.js');

		// $view = "admin_dashboard";
		
		if($type == 'participant'){
			$this->db->where('status','active');
			$get = $this->db->get('pt_round_v')->row();

			if($get == null){
				$locking = 0;
			}else{
				$ongoing_check = $this->db->get_where('pt_round_v', ['type'=>'ongoing','status' => 'active'])->row();

				if($ongoing_check){
					$ongoing_pt = $ongoing_check->uuid;
				}else{
					$ongoing_pt = 0;
				}
		
				if($ongoing_pt){
					$checklocking = $this->M_PTRound->allowPTRound($ongoing_pt, $this->session->userdata('uuid'));

					if($checklocking == null){
						$locking = 0;
					}else{
						$locking = $checklocking->receipt;
					}
				}else{
					$locking = 0;
				}
			}
			

			$this->load->model('participant/M_Participant');
			$view = "dashboard_v";
			$data = [
				'receipt'   		=> $locking,
				'dashboard_data'	=>	$this->getParticipantDashboardData($this->session->userdata('uuid')),
				'participant'		=>	$this->M_Participant->findParticipantByIdentifier('uuid', $this->session->userdata('uuid'))
			];
		}elseif($type == "admin"){
			$view = "admin_dashboard";

			$round = $this->dashboard_m->getCurrentRound();

			// echo "<pre>";print_r($round);echo "</pre>";die();

			$stats = $this->getDashboardStats();
			$data = [
                'pending_participants'    =>  $this->dashboard_m->pendingParticipants(),
                'new_equipments'    =>  $this->dashboard_m->newEquipments(),
                'stats'			=>	$stats,
                'round_uuid' => $round->uuid
            ];
		}else if($type == "qareviewer"){
			$this->db->where('status','active');
			$this->db->where('type', 'ongoing');
			$round = $this->db->get('pt_round_v')->row();

			if($round){

			}
            $view = "qa_dashboard";
            $data = [
            	'pt_round'	=>	$round
            ];

            if($round){
            	$data['round'] = $round->id;
            }
        }else if($type == "program"){
        	redirect('Program/', 'refresh');
        }

        // echo "<pre>";print_r($data);echo "</pre>";die();
        $this->assets
				->addJs('dashboard/js/libs/moment.min.js')
				->addJs('dashboard/js/libs/fullcalendar.min.js')
				->addJs('dashboard/js/libs/gcal.js');

		$this->assets->setJavascript('PTRounds/calendar_js');
		$this->template->setPageTitle('EQA Dashboard')->setPartial($view,$data)->adminTemplate();
	}

	private function getDashboardStats(){
		$this->db->where('status', 'active');
		$this->db->where('type', 'ongoing');
		$pt_round = $this->db->get('pt_round_v')->row();

		$stats = new StdClass;
		$stats->pt_round = null;
		$stats->readiness_submissions = 0;
		$stats->received_panels = 0;
		$stats->not_received_panels = 0;
		$stats->pending_review = 0;
		$stats->no_response = 0;
		$stats->completed_and_revied = 0;
		$stats->not_completed = 0;

		if ($pt_round) {
			$stats->pt_round = $pt_round->pt_round_no;
			$dashboard_stats = $this->dashboard_m->getDashboardStats($pt_round->uuid);
			$stats->readiness_submissions = $dashboard_stats->readiness_submitted;
			$stats->received_panels = $dashboard_stats->panels_received;
			$stats->not_received_panels = $dashboard_stats->panels_not_received;
		}

		return $stats;
	}


	public function viewMessages(){
		$data = [];
        $title = "My Messages";
        // $equipment_count = $this->db->count_all('equipment');

        
        	$data = [
                'table_view'    =>  $this->createMessagesTable()
            ];
        

        $this->assets
                ->addCss("plugin/sweetalert/sweetalert.css");
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs("plugin/sweetalert/sweetalert.min.js");
        $this->assets->setJavascript('Dashboard/message_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Dashboard/messages_v', $data)
                ->adminTemplate();
	}


    function myMessage($message_uuid){
    	$this->db->where('uuid', $message_uuid);
        $message = $this->db->get('messages_v')->row();
        //echo '<pre>';print_r($message);echo '</pre>';die();

        if($message){
        	$this->db->set('status', 1);

            $this->db->where('uuid', $message_uuid);

            if($this->db->update('messages')){
                $this->session->set_flashdata('success', "Message marked as read");
            }
        }
        
        $data = [
            'from'          =>  $message->from,
            'email'        =>  $message->email,
            'subject'        =>  $message->subject,
            'message'              =>  $message->message,
            'date_sent'          =>  $message->date_sent
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");
        $this->template
                ->setPartial('Dashboard/my_message', $data)
                ->setPageTitle('My Message')
                ->adminTemplate();
    }

    function RemoveMessage($message_uuid){
    	// echo "<pre>";print_r($message_uuid);echo "</pre>";die();
    	$this->db->set('deleted', 1);

            $this->db->where('uuid', $message_uuid);

            if($this->db->update('messages')){
                $this->session->set_flashdata('success', "Successfully removed the message");
                echo "success";
            }else{
                $this->session->set_flashdata('error', "There was a problem removing the message. Please try again");
                echo "fail";
            }

            // redirect('Dashboard/viewMessages/');
    }


    private function getParticipantDashboardData($participant_uuid){
		$dashboard_data = new StdClass();
		$dashboard_data->current = "";
		$this->db->where('type', 'ongoing');
		$this->db->where('status', 'active');
		$query = $this->db->get('pt_round_v');

		$dashboard_data->calendar_legend = $this->createCalendarLegend();

		if($query->num_rows() == 1){
			$dashboard_data->rounds = $query->num_rows();
			$pt_round = $query->row();
			$readiness = $this->db->get_where('participant_readiness', ['participant_id'=>$participant_uuid, 'pt_round_no' => $pt_round->uuid])->row();
			$dashboard_data->pt_round = $pt_round;

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
				$dashboard_data->calendar_current = "No Current Item";
			}

			if($readiness){
				$this->db->where('readiness_uuid', $readiness->uuid);
				$this->db->where('pt_round_uuid', $pt_round->uuid);
				$participant_readiness = $this->db->get('pt_ready_participants')->row();
				$dashboard_data->readiness = $participant_readiness;

				// echo "<pre>";print_r($participant_readiness);echo"</pre>";die();

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
			$dashboard_data->rounds = $query->num_rows();
		}


		return $dashboard_data;
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

/* End of file Dashboard.php */
/* Location: ./application/modules/Home/controllers/Dashboard.php */
