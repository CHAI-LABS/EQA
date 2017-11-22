<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PTRound extends MY_Controller {
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_PPTRound');
        $this->load->module('Participant');
        $this->load->model('M_Readiness');
        $this->load->model('M_PTRound');
        $this->load->library('Mailer');
        $this->load->library('table');
        $this->load->config('table');
    }

    public function index(){
        
            $data = [
                'pt_rounds'    =>  $this->createPTRoundTable()
            ];

            $this->template->setPageTitle('PT Rounds')->setPartial("pt_view",$data)->adminTemplate();
    }

    public function userlist(){
        $data = [];
        $title = "Facility Participants";

        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
        // echo '<pre>';print_r($user);echo "</pre>";die();

        $data = [
            'table_view'    =>  $this->createFacilityParticipantsTableView($user->facility_id)
        ];

       
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");
        // $this->assets->setJavascript('QAReviewer/participants_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('QAReviewer/facility_participants_v', $data)
                ->adminTemplate();
    }


    function createFacilityParticipantsTableView($facility_code){

        $template = $this->config->item('default');

        $change_state = '';

        $facility_participants = $this->M_PPTRound->getFacilityParticipantsView($facility_code);
        

        $heading = [
            "No.",
            "Participant ID",
            "Participant",
            "Phone Number",
            "Actions"
        ];
        $tabledata = [];


        if($facility_participants){
            $counter = 0;
            foreach($facility_participants as $participant){
                $counter ++;
// echo '<pre>';print_r($participant->approved);echo "</pre>";die();
                

                if($participant->approved == 0){
                    $change_state = ' <a href = ' . base_url("QAReviewer/PTRound/ChangeStatus/activate/$participant->username/$participant->uuid") . ' class = "btn btn-primary btn-sm"><i class = "icon-note"></i>&nbsp;Activate</a> ';
                }else{
                    $change_state = ' <a href = ' . base_url("QAReviewer/PTRound/ChangeStatus/deactivate/$participant->username/$participant->uuid") . ' class = "btn btn-danger btn-sm"><i class = "icon-note"></i>&nbsp;Deactivate</a> ';
                }

                

                $tabledata[] = [
                    $counter,
                    $participant->username,
                    $participant->lastname.' '.$participant->firstname,
                    $participant->phone,
                    $change_state
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }

    function ChangeStatus($type,$username,$participant_uuid){
        switch ($type) {
            case 'activate':
               $this->db->set('approved', 1);
                break;
            
            case 'deactivate':
                $this->db->set('approved', 0);
                break;
        }

        
        $this->db->where('uuid', $participant_uuid);

        if($this->db->update('participants')){
            $this->session->set_flashdata('success', "Successfully updated Participant ID: ".$username);
        }else{
            $this->session->set_flashdata('error', "There was a problem updating the Participant ID: ".$username);
        }

        redirect('QAReviewer/PTRound/userlist/', 'refresh');

    }

    

    function createPTRoundTable(){
        $rounds = $this->db->get('pt_round_v')->result();
        $ongoing = $prevfut = '';
        $round_array = [];
        if ($rounds) {
            foreach ($rounds as $round) {
                $created = date('dS F, Y', strtotime($round->date_of_entry));
                // $view = "<a class = 'btn btn-success btn-sm' href = '".base_url('QAReviewer/PTRound/Round/' . $round->uuid)."'><i class = 'fa fa-eye'></i>&nbsp;View</a>";
                
                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('QAReviewer/PTRound/Round/' . $round->uuid)."'><i class = 'icon-eye'></i>&nbsp;View</a>";

                $capa = "<a class = 'btn btn-primary btn-sm dropdown-item' href = '".base_url('QAReviewer/PTRound/Capa/' . $round->uuid)."'><i class = 'icon-eye'></i>&nbsp;CAPA</a>";

                $dropdown = "<div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                                $capa
                            </div>
                        </div>";

                $status = ($round->status == "active") ? '<span class = "tag tag-success">Active</span>' : '<span class = "tag tag-danger">Inactive</span>';
                if ($round->type == "ongoing") {
                    $ongoing .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>{$dropdown}</td>
                    </tr>";
                }else{
                    $prevfut .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>{$dropdown}</td>
                    </tr>";
                }
            }
        }

        $round_array = [
            'ongoing'   =>  $ongoing,
            'prevfut'   =>  $prevfut
        ];

        return $round_array;
    }


    public function Capa()
    {   
        $data = [];
        $title = "Capa Analysis";

        $data = [
            'page_title'    => 'Capa List',
            'back_text'     => 'Back to Dashboard',
            'back_link'     => base_url('QAReviewer/PTRound/'),
            'table_view'    =>  $this->createCapaTable()
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('QAReviewer/qareviewer_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('QAReviewer/capa_view', $data)
                ->adminTemplate();
    }


    public function createCapaTable(){
        $template = $this->config->item('default');

        $heading = [
            "No.",
            "Participant",
            "Date Submitted",
            "Reviewed",
            "Actions"
        ];
        $tabledata = [];

        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
        // echo "<pre>";print_r($user);echo "</pre>";die();
        $facility_id = $user->facility_id;

        $this->db->where('facility_id', $facility_id);
        $capas = $this->db->get('capa_review')->result();
        
        if($capas){
            $counter = 0;
            foreach($capas as $capa){
                
                $counter ++;

                $participant_id = $this->db->get_where('participant_readiness_v', ['uuid' => $capa->participant_uuid])->row()->username;
                
                if($capa->approved == 0){
                    $status = "<label class = 'tag tag-danger tag-sm'>Not Approved</label>"; 
                }else{
                    $status = "<label class = 'tag tag-success tag-sm'>Approved</label>";
                }

                $date = date('dS F, Y', strtotime($capa->date_of_submission));

                $view = "<a class = 'btn btn-info btn-sm dropdown-item' href = '".base_url('QAReviewer/PTRound/CapaView/' . $capa->participant_uuid .'/'. $capa->round_uuid)."'><i class = 'icon-eye'></i>&nbsp;View</a>";

                if($capa->status == 0){
                    $review = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('QAReviewer/PTRound/MarkReview/1/' . $capa->id)."'><i class = 'icon-eye'></i>&nbsp;Mark as Approved</a>";
                }else{
                    $review = "<a class = 'btn btn-danger btn-sm dropdown-item' href = '".base_url('QAReviewer/PTRound/MarkReview/0/' . $capa->id)."'><i class = 'icon-eye'></i>&nbsp;Mark as Disapproved</a>";
                }

                $dropdown = "<div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                                $review
                            </div>
                        </div>";
                
                $tabledata[] = [
                    $counter,
                    $participant_id,
                    $date,
                    $status,
                    $dropdown
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }


    function MarkReview($type, $capa_id){
        $response = [];

            $update_data = [];

            if($type == 1){
                $update_data = ['approved'  =>  1];
            }else{
                $update_data = ['approved'  =>  0];
            }

            $this->db->where('id', $capa_id);
            if($this->db->update('capa_response', $update_data)){
                $response = [
                    'status'    =>  TRUE,
                    'message'   =>  "Successfully Marked CAPA as Reviewed"
                ];
            }else{
                $response = [
                    'status'    =>  FALSE,
                    'message'   =>  "There was a problem marking the CAPA"
                ];
            }
        $this->Capa();
    }


    public function CapaView($participant_uuid, $round_uuid){
        $data = [];
        $title = "Capa View";

        // echo "<pre>";print_r($rounds);echo "</pre>";die();

        $data = [
            'page_title'    => 'Capa View',
            'back_text'     => 'Back to CAPA Participants',
            'back_link'     => base_url('PTRound/CAPA/'.$round_uuid),
            'capa_view'    =>  $this->createCapaView($participant_uuid, $round_uuid)
        ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/capa_view', $data)
                ->adminTemplate();
    }


    function createCapaView($participant_uuid, $round_uuid){
        $capa_view = '';

        $this->db->where('round_uuid',$round_uuid);
        $this->db->where('participant_uuid',$participant_uuid);

        $capa = $this->db->get('capa_review')->row();

        $date = date('dS F, Y', strtotime($capa->date_of_submission));

        $participant_id = $this->db->get_where('participant_readiness_v', ['uuid' => $participant_uuid])->row()->username;

        if($capa->effective){
            $effect = "Yes";
        }else{
            $effect = "No";
        }

        // echo "<pre>";print_r($capa);echo "</pre>";die();

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Occurrence Details
                            </div>

                            <div class = "card-block">
                                <div class="col-sm-4"><strong>Participant ID</strong></div>
                                <div class="col-sm-8">' . $participant_id . '</div>
                                <br/>&nbsp;<br/>
                                <div class="col-sm-4"><strong>Description of the occurrence</strong></div>
                                <div class="col-sm-8">' . $capa->occurrence . '</div>
                            </div>
                        </div>';

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Root Cause
                            </div>
                        <div class = "card-block">
                            <div class="col-sm-4"><strong>Selected applicable testing phase(s)</strong></div>
                            <div class="col-sm-8">';

        $tests = $this->db->get_where('capa_tests', ['capa_test_id' => $capa->id])->result();

        foreach ($tests as $test) {
            $capa_view .= $test->applied_test . '<br/>';
        }

        $capa_view .= '</div>
                            <br/>&nbsp;<br/>
                            <div class="col-sm-4"><strong>Description of root cause</strong></div>
                            <div class="col-sm-8">' . $capa->cause . '</div>
                            <br/>&nbsp;<br/>
                            <div class="col-sm-4"><strong>Attributing factor(s)</strong></div>
                            <div class="col-sm-4">';

        $attributes = $this->db->get_where('capa_attributes', ['capa_attribute_id' => $capa->id])->result();

        foreach ($attributes as $attribute) {
            if($attribute->attribute_factor == "Other"){
                $capa_view .= $attribute->attribute_factor . ' - ';
                $capa_view .= $attribute->specific_other . '<br/>';
            }else{
                $capa_view .= $attribute->attribute_factor . '<br/>';
            }
            
        }

        $capa_view .= '</div>
                        </div>
                        </div>

                        <div class = "card">
                            <div class="card-header">
                                Corrective Action
                            </div>

                            <div class = "card-block">

                                <div class="col-sm-4"><strong>Describe corrective measures taken</strong></div>
                                <div class="col-sm-8">' . $capa->correction . '</div>
                                <br/>&nbsp;<br/>
                                <div class="col-sm-4"><strong>Was the corrective action effective ?</strong></div>
                                <div class="col-sm-8">' . $effect . '</div>
                            </div>
                        </div>';

        $capa_view .= '<div class = "card">
                            <div class="card-header">
                                Preventive Action
                            </div>

                            <div class = "card-block">

                                <div class="col-sm-4"><strong>Describe action(s) taken to prevent recurrence</strong></div>
                                <div class="col-sm-8">' . $capa->prevention . '</div>
                            </div>
                        </div>

                        <a href="'. base_url('QAReviewer/PTRound/MarkReview/1/' . $capa->id) .'"><button id="submit-capa" type="submit" class="btn btn-block btn-primary">Mark as Approved</button></a>';

        return $capa_view;
    }


    function Round($round_uuid){
        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));
        // echo '<pre>';print_r($user->facility_id);echo "</pre>";die();
        $facility_id = $user->facility_id;

        $data = [];
        $title = "Ready Participants";

        $data = [
            'table_view'    =>  $this->createFacilityParticipantsTable($round_uuid,$facility_id)
        ];

       
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs("dashboard/js/libs/toastr.min.js");
        $this->assets->setJavascript('QAReviewer/notifications_js');
        $this->template
                ->setModal("QAReviewer/message_v", "Send Message")
                ->setPageTitle($title)
                ->setPartial('QAReviewer/facility_participants_v', $data)
                ->adminTemplate();

    }


    function createFacilityParticipantsTable($round_uuid,$facility_id){

        $template = $this->config->item('default');

        $change_state = '';

        $facility_participants = $this->M_PPTRound->getFacilityParticipants($round_uuid,$facility_id);
        // echo '<pre>';print_r($facility_id);echo "</pre>";die();
        $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;

        $heading = [
            "No.",
            "Participant ID",
            "Participant",
            "Phone Number",
            "Verdict",
            "Actions"
        ];
        $tabledata = [];

        // $this->db->where('verdict', 0);
        

        if($facility_participants){
            $counter = 0;
            foreach($facility_participants as $participant){
                $counter ++;
                $participantid = $participant->participant_id;
                 

                $pid = $participant->p_id;
                
                $verdict = $this->M_PPTRound->getVerdictCheck($round_id,$pid);
                // echo "<pre>";print_r($facility_participants);echo "</pre>";die();

                
                $change_state = '<div class = "dropdown">
                            <button class = "btn btn-secondary dropdown-toggle" type = "button" id = "dropdownMenuButton1" data-toggle = "dropdown" aria-haspopup="true" aria-expanded = "true">
                                Quick Actions
                            </button>
                            <div class = "dropdown-menu" aria-labelledby= = "dropdownMenuButton">

                             <a href = ' . base_url("QAReviewer/PTRound/ParticipantDetails/$round_uuid/$participantid") . ' class = "btn btn-info btn-sm dropdown-item"><i class = "icon-note"></i>&nbsp;View Submissions</a> ';
                

                $Check = $this->M_PPTRound->getDataSubmission($round_id,$pid);

                if($Check){
                    $getCheck = $Check->status;
                }else{
                    $getCheck = 2;
                }

                if($verdict == 1){
                    $smart_status = "<label class = 'tag tag-success tag-sm'>Accepted</label>";
                }else if($verdict == 0){
                    $smart_status = "<label class = 'tag tag-danger tag-sm'>Rejected</label>";
                }else{
                    $smart_status = "<label class = 'tag tag-warning tag-sm'>Awaiting Verdict</label>";
                }

                //echo "<pre>";print_r($getCheck);echo "</pre>";die();
                

                if($participant->lab_result){
                    $change_state .= '<a data-type="lab" href = ' . base_url("QAReviewer/PTRound/Round/$round_uuid#") . ' class = "btn btn-success btn-sm showtoast dropdown-item"><i class = "icon-note"></i>  &nbsp;Lab Results</a>';

                    $smart_status .= "&nbsp;<label class = 'tag tag-info tag-sm'>Lab Result</label>";
                }else{
                    $change_state .= '<a href = ' . base_url("QAReviewer/PTRound/MarkLabResult/$round_uuid/$round_id/$participant->participant_uuid") . ' class = "btn btn-danger btn-sm dropdown-item"><i class = "icon-note"></i>&nbsp;&nbsp;Mark as Lab Result</a> 
                    ';
                }

                if($getCheck == 1){
                    $change_state .= '<a data-type="send" href = ' . base_url("QAReviewer/PTRound/Round/$round_uuid#") . ' class = "btn btn-primary btn-sm showtoast dropdown-item" ><i class = "icon-note"></i>&nbsp;Send to NHRL</a>';

                    $smart_status .= "&nbsp;<label class = 'tag tag-danger tag-sm'>Sent to NHRL</label>";

                }else if($getCheck == 2){
                    $change_state = '';
                    
                }else{
                    $change_state .= '<a href = ' . base_url("QAReviewer/PTRound/MarkSubmissions/$round_uuid/$round_id/$pid") . ' class = "btn btn-success btn-sm dropdown-item"><i class = "icon-note"></i>&nbsp;Send to NHRL</a>   
                    ';
                }

                $change_state .= '</div>
                        </div> 
                    ';

    
                $tabledata[] = [
                    $counter,
                    $participantid,
                    $participant->participant_lname.' '.$participant->participant_fname,
                    $participant->participant_phonenumber,
                    $smart_status,
                    $change_state
                ];
            }
        }else{
            $heading = [
                        "Participant Data"
                    ];
            $tabledata[] = [
                    "No data submitted for this round"
                ];
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }

    function Message($round_uuid,$participant_uuid){
        $data = [];
        $title = "Message";

        $data = [
               'round_uuid' => $round_uuid,
               'participant_uuid' => $participant_uuid
            ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('QAReviewer/notifications_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('QAReviewer/message_v', $data)
                ->adminTemplate();
    }



    function MarkSubmissions($round_uuid,$round_id, $pid){

        $denySubmission = 0;

        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));

        $participants = $this->M_PPTRound->getFacilityParticipantsView($user->facility_id);

        foreach ($participants as $key => $participant) {
           
            $statusCheck = $this->M_PPTRound->getStatusCheck($round_id, $participant->p_id);
            // echo "<pre>";print_r($user);echo "</pre>";die();
            if($statusCheck->status){
                $denySubmission = 1;
            }
        }

            

        if($denySubmission){
            $this->session->set_flashdata('error', "A participant's data for this PT Round has already sent to NHRL");
        }else{
            $verdictCheck = $this->M_PPTRound->getFacilityParticipant($round_uuid, $user->facility_id);

            // echo "<pre>";print_r($verdictCheck->lab_result);echo "</pre>";die();

            if($verdictCheck->lab_result){
                $this->db->set('status', 1);
        
                $this->db->where('round_id', $round_id);
                $this->db->where('participant_id', $pid);
                //$this->db->update('equipment');

                if($this->db->update('pt_data_submission')){
                    $this->session->set_flashdata('success', "Successfully sent PT submission to the NHRL");
                }else{
                    $this->session->set_flashdata('error', "There was a problem sending the details. Please try again");
                }
            }else{
                $this->session->set_flashdata('error', "Participant must be set as the Lab Result First");
            }

            
        }


        redirect('QAReviewer/PTRound/Round/'.$round_uuid, 'refresh');

    }

    function MarkLabResult($round_uuid,$round_id, $participant_uuid){

        $user = $this->M_Readiness->findUserByIdentifier('uuid', $this->session->userdata('uuid'));

        //Removes any participant who was marked as the lab result

        $this->db->set('lab_result', 0);
        $this->db->where('pt_round_no', $round_uuid);
        $this->db->where('participant_facility', $user->facility_id);
        $this->db->update('participant_readiness');


        //Marks this participant as the lab result

        $this->db->set('lab_result', 1);
        $this->db->where('pt_round_no', $round_uuid);
        $this->db->where('participant_id', $participant_uuid);
        $this->db->where('participant_facility', $user->facility_id);
        $update = $this->db->update('participant_readiness');
        //$this->db->update('equipment');

        if($update){
            $this->session->set_flashdata('success', "Successfully made Participant as the Lab Result");
        }else{
            $this->session->set_flashdata('error', "There was a problem making the participant as Lab Result. Please try again");
        }

        redirect('QAReviewer/PTRound/Round/'.$round_uuid, 'refresh');
    }

    function ParticipantDetails($round_uuid,$participant_id){
        $data = [];
        $title = "Ready Participants";

        
        $pt_round_to = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->to;
        $user = $this->M_Readiness->findUserByIdentifier('username', $participant_id);
        $participant_uuid = $user->uuid;

        $equipment_tabs = $this->createTabs($round_uuid,$participant_uuid);

        $data = [
                'pt_round_to' => $pt_round_to,
                'pt_uuid'    =>  $round_uuid,
                'participant'    =>  $participant_id,
                'equipment_tabs'    =>  $equipment_tabs,

            ];
        $this->assets
                ->addCss("plugin/bootstrap-3.3.7/css/bootstrap.min.css");
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");
        $this->assets->setJavascript('QAReviewer/data_submission_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('QAReviewer/participant_submissions_v', $data)
                ->adminTemplate();
    }


    public function createTabs($round_uuid, $participant_uuid){

        $equipments = $this->M_PTRound->Equipments($participant_uuid);
        
        $datas=[];
        $tab = 0;
        $zero = '0';
        
        $samples = $this->M_PTRound->getSamples($round_uuid,$participant_uuid);
        $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;
        $user = $this->M_Readiness->findUserByIdentifier('uuid', $participant_uuid);
        $participant_id = $user->p_id;

        

        // echo "<pre>";print_r($equipments);echo "</pre>";die();
        
        $equipment_tabs = '';

        $equipment_tabs .= "<ul class='nav nav-tabs' role='tablist'>";

        foreach ($equipments as $key => $equipment) {
            $tab++;
            $equipment_tabs .= "";

            $equipment_tabs .= "<li class='nav-item'>";
            if($tab == 1){
                $equipment_tabs .= "<a class='nav-link active' data-toggle='tab'";
            }else{
                $equipment_tabs .= "<a class='nav-link' data-toggle='tab'";
            }

            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);
            
            $equipment_tabs .= " href='#".$equipmentname."' role='tab' aria-controls='home'><i class='icon-calculator'></i>&nbsp;";
            $equipment_tabs .= $equipment->equipment_name;
            $equipment_tabs .= "&nbsp;";
            // $equipment_tabs .= "<span class='tag tag-success'>Complete</span>";
            $equipment_tabs .= "</a>
                                </li>";
        }

        $equipment_tabs .= "</ul>
                            <div class='tab-content'>";

        $counter = 0;
        $counter3 = 0;

        foreach ($equipments as $key => $equipment) {
            $counter++;
            

            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);

            if($counter == 1){
                $equipment_tabs .= "<div class='tab-pane active' id='". $equipmentname ."' role='tabpanel'>";
            }else{
                $equipment_tabs .= "<div class='tab-pane' id='". $equipmentname ."' role='tabpanel'>";
            }
    // echo "<pre>";print_r($participant_id);echo "</pre>";die();        
            $this->db->where('round_uuid',$round_uuid);
            $this->db->where('participant_id',$participant_id);
            $this->db->where('equipment_id',$equipment->id);

            $datas = $this->db->get('data_entry_v')->result();

            $equipment_tabs .= "<div class='row'>
        <div class='col-sm-12'>
        <div class='card'>
            <div class='card-header'>

            <div class='form-group row'>
                <div class='col-md-6'>

                <label class='checkbox-inline'>
                <strong>RESULTS FOR ". $equipment->equipment_name ."</strong>
                </label>

                </div>
                <div class='col-md-6'>
                    
            <label class='checkbox-inline' for='check-complete'>";

            if($datas){
                $getCheck = $this->M_PTRound->getDataSubmission($round_id,$participant_id,$equipment->id)->status;
            }else{
                $getCheck = 0; 
            }
            

            $equipment_tabs .= "</label>
                    </div>
                </div>


            </div>
            <div class='card-block'>
            
                <div class='row'>
                    <table  style='text-align: center;' class='table table-bordered'>";

            $reagents = $this->M_PTRound->getReagents($datas[0]->equip_result_id,$equipment->id);
            // echo "<pre>";print_r($reagents);echo "</pre>";die();

            foreach ($reagents as $regkey => $reagent) {
                

                $equipment_tabs .= "<tr>

                    <td style='style='text-align: center;' colspan='2'>

                        <label style='text-align: center;' for='reagent_name'>Reagent Name: </label>";

                if($datas){
                    if($reagent->reagent_name){
                        $reagent_name = "<div>".$reagent->reagent_name." </div>" ;
                    }else{
                        $reagent_name = "<div>No Reagent</div>";
                    }
                }else{
                    $reagent_name = "<div>No Reagent</div>";
                }

                // echo "<pre>";print_r($reagent);echo "</pre>";die();

                $equipment_tabs .= $reagent_name;

                            
                $equipment_tabs .= " </td>

                      <td style='style='text-align: center;' colspan='3'>

                        <label style='text-align: center;' for='lot_number'>Lot Number: </label>";

                if($datas){
                    
                    if($reagent->lot_number){
                        $lot = "<div>".$reagent->lot_number." </div>" ;
                    }else{
                        $lot = "<div>0</div>";
                    }
                }else{
                    $lot = "<div>0</div>";
                }
                $equipment_tabs .= $lot;

                            
                      $equipment_tabs .= " </td>


                      <td style='style='text-align: center;' colspan='3'>

                        <label style='text-align: center;' for='expiry_date'>Expiry Date: </label>";

                if($datas){
                    if($reagent->expiry_date != ''){
                        $expiry_date = "<div>".$reagent->expiry_date." </div>" ;
                    }else{
                        $expiry_date = "<div>No Expiry Date</div>";
                    }
                }else{
                    $expiry_date = "<div>No Expiry Date</div>";
                }

                $equipment_tabs .= $expiry_date;

                            
                $equipment_tabs .= " </td>
                                    </tr>";


            }

                $equipment_tabs .= " <tr>
                            <th style='text-align: center; width:20%;' rowspan='3'>
                                PANEL
                            </th>
                            <th style='text-align: center;' colspan='7'>
                                RESULT
                            </th>
                        </tr>
                        <tr>
                            <th style='text-align: center;' colspan='2'>
                                CD3
                            </th>
                            <th style='text-align: center;' colspan='2'>
                                CD4
                            </th>
                            <th style='text-align: center;' colspan='2'>
                                Other (Specify)
                            </th>
                        </tr>
                        <tr>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                            <th style='text-align: center;'>
                                Absolute
                            </th>
                            <th style='text-align: center;'>
                                Percent
                            </th>
                        </tr>";

                    $counter2 = 0;
                    foreach ($samples as $key => $sample) {
                        
                    // echo "<pre>";print_r($datas);echo "</pre>";die();

                        $value = 0;
                        $equipment_tabs .= "<tr> <th style='text-align: center;'>";
                        $equipment_tabs .= $sample->sample_name;

                        $equipment_tabs .= "</th> <td>";
                                
                        //echo "<pre>";print_r($datas[$counter2]->equipment_id);echo "</pre>";die();
                            if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd3_absolute;
                                }else{
                                    $value = 0;
                                }
                            }else{
                                $value = 0;
                            }

                        $equipment_tabs .= $value ." </td> <td>";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd3_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value." </td> <td>";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd4_absolute;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."</td> <td>";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->cd4_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."</td> <td>";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->other_absolute;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."</td> <td>";

                        if($datas){
                                if($equipment->id == $datas[$counter2]->equipment_id){
                                    $value = $datas[$counter2]->other_percent;
                                }else{
                                    $value = 0;
                                }
                        }else{
                            $value = 0;
                        }

                        $equipment_tabs .= $value."</td> </tr> ";
                        $counter2++;
                    }

                    $equipment_tabs .= "<tr><td colspan='8'>
                            <form action='".base_url("QAReviewer/PTRound/sendVerdict/$round_id/$participant_id/$equipment->id")."' method='POST'>

                              <div style='text-align: center; width:40%;' class='form-group'> <label>Verdict</label>
                                  <select name='verdict' class='form-control' required>
                                    <option selected='selected' value=''>Select your verdict</option>
                                    <option value='Accepted'>Accepted</option>
                                    <option value='Rejected'>Rejected</option>
                                </select>
                              </div>


                              <div style='text-align: center; width:80%;' class='form-group'>
                                <label>Comments</label>
                                <textarea maxlength='500' name='comments' class='form-control' rows='3'></textarea>
                              </div>

                              <div>
                              <button type='submit' class='btn btn-primary'>Submit</button>
                              </div>
                            </form>
                    </td></tr>
                                        </table>
                                        </div>

                                        </div>   
                                        </div>
                                        </div>
                                        </div>

                                        </div>";

                    
        }

        $equipment_tabs .= "</div>";



        return $equipment_tabs;

    }


    public function sendVerdict($pt_id,$part_id,$equip_id){
        // echo "<pre>";print_r($equip_id);echo "</pre>";die();

        $verdict = $this->input->post('verdict');
        $comments = $this->input->post('comments');

            $insertdata = [
                'round_id'          =>  $pt_id,
                'participant_id'    =>  $part_id,
                'equipment_id'      =>  $equip_id,
                'verdict'           =>  $verdict,
                'comments'          =>  $comments
            ];


            if($this->db->insert('pt_data_log', $insertdata)){
                $this->session->set_flashdata('success', "Successfully sent the message");

                if($verdict == 'Accepted'){
                    $verd = 1;
                }else if($verdict == 'Rejected'){
                    $verd = 0;
                }else{
                    $verd = 2;
                }


                $this->db->set('verdict', $verd);
                $this->db->where('round_id', $pt_id);
                $this->db->where('participant_id', $part_id);
                $this->db->where('equipment_id', $equip_id);
                    $this->db->update('pt_data_submission');


                $user = $this->M_Readiness->findUserByIdentifier('p_id', $part_id);
                $pt_uuid = $this->M_Readiness->findRoundByIdentifier('id', $pt_id)->uuid;

                if($verdict == 'Rejected'){

                    if($user){
                        $data = [];

                        $body = $this->load->view('Template/email/qa_results_review', $data, TRUE);
                        $this->load->library('Mailer');
                        $sent = $this->mailer->sendMail($user->email_address, "QA / Supervisor Results Review", $body);
                        if ($sent == FALSE) {
                            log_message('error', "The system could not send an email to {$user->participant_email}. Names: $user->participant_lname $user->participant_fname at " . date('Y-m-d H:i:s'));
                        }
                    }
                }
                

            }else{
                $this->session->set_flashdata('error', "There was a problem sending the message. Please try again");
            }

            // $user_id = $this->db->insert_id();

            
            redirect('QAReviewer/PTRound/ParticipantDetails/'.$pt_uuid.'/'.$user->username, 'refresh');
    }

}

/* End of file PTRound.php */
/* Location: ./application/modules/QAReviewer/controllers/PTRound.php */