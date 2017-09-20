<?php

class PTRounds extends DashboardController{
    protected $menu, $lab_id_prefix;
    function __construct(){
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('table');
        $this->load->config('table');
        
        $this->load->module('Participant');
        $this->load->model('M_Readiness');
        $this->load->model('M_PTRounds');
        $this->load->model('M_PTRound');
    
        // $this->load->model('Participant/M_PTRound');
        // $this->load->model('Participant/M_Readiness');

        $this->menu = [
            'information'   =>  [
                'icon'  =>  'fa fa-info-circle',
                'text'  =>  'PT Details'
            ],
            'facilities'      =>  [
                'icon'  =>  'fa fa-hospital-o',
                'text'  =>  'Facilities'
            ],
            'samples_labs'      => [
                'icon'  =>  'fa fa-flask',
                'text'  =>  'Samples & Testers'
            ],
            'variables'     =>  [
                'icon'  =>  'fa fa-table',
                'text'  =>  'Variables'
            ],
            'calendar'      =>  [
                'icon'  =>  'fa fa-calendar',
                'text'  =>  'Calendar'
            ],
            
        ];

        $this->lab_id_prefix = "CD4-PT-";
    }

    function index(){
        $data['pt_rounds'] = $this->createPTRoundTable();
        $this->template
                    ->setPageTitle('PT Rounds')
                    ->setPartial('PTRounds/list_v', $data)
                    ->adminTemplate();
    }

    function calendar($pt_round){
        $this->assets
                ->addJs('dashboard/js/libs/moment.min.js')
                ->addJs('dashboard/js/libs/fullcalendar.min.js')
                ->addJs('dashboard/js/libs/gcal.js');

        $data = [
            'pt_details'    =>  $this->db->get_where('pt_round', ['uuid' => $pt_round])->row(),
            'legend'        =>  $this->createCalendarColorLegend(),
            'pt_round'      =>  $pt_round
        ];
        $this->assets->setJavascript('PTRounds/calendar_js');
        $this->template
                ->setPartial('PTRounds/view_pt_calendar', $data)
                ->adminTemplate();
    }


    function create($step = NULL, $id = NULL){
        $data = $pagedata = [];
        $pt_details = new StdClass;
        if($step == NULL){
            $step = "information";
        }

        if($id != NULL){
            $pt_details = $this->db->get_where('pt_round', ['uuid'  => $id])->row();
            $pagedata['pt_details'] = $pt_details;
        }

        if(($step != NULL && $step != "information") && $id == NULL){
            $step = "information";
            $this->session->set_flashdata('error', 'Sorry. You need to create the PT Round first');
            redirect('PTRounds/create/' . $step);
        }

        $view = "";
        $js_data = [
            'step'  =>  $step
        ];

        switch ($step) {
            case 'information':
                $view = "pt_info_v";
                $pagedata['round_no'] = (!$id) ? $this->generateRoundNumber() : $pt_details->pt_round_no;
                $pagedata['lab_prefix'] = $this->lab_id_prefix;
                if($id){
                    $pagedata['lab_id'] = str_replace('-', '', substr($pt_details->blood_lab_unit_id, strpos($pt_details->blood_lab_unit_id, '-', strpos($pt_details->blood_lab_unit_id, '-')+1)));
                    $pagedata['round_duration'] = date('m/d/Y', strtotime($pt_details->from)) .' - ' . date('m/d/Y', strtotime($pt_details->to));
                }
                $this->assets
                        ->addJs('dashboard/js/libs/moment.min.js')
                        ->addJs('dashboard/js/libs/daterangepicker.js');
                break;
            case 'variables':
                $view = "pt_variables_v";
                $pagedata['accordion'] = $this->createVariablesAccordion($pt_details->id);
                break;
            case 'calendar':
                $view = "pt_calendar_v";
                $pagedata['calendar_form'] = $this->createCalendarForm($pt_details->id);
                $js_data['duration_from'] = $pt_details->from;
                $js_data['duration_to'] = $pt_details->to;

                $this->assets
                        ->addJs('dashboard/js/libs/moment.min.js')
                        ->addJs('dashboard/js/libs/daterangepicker.js');
                break;
            case 'samples_labs':
                $pagedata['no_labs'] = $this->db->get_where('pt_labs', ['pt_round_id' => $pt_details->id])->num_rows();
                $pagedata['no_testers'] = $this->db->get_where('pt_testers', ['pt_round_id' => $pt_details->id])->num_rows();
                $pagedata['samples_table'] = $this->createSamplesTable($pt_details->id);
                $view = "pt_samples_labs_v";
                break;
            case 'facilities':
                $view = "pt_facilities_v";
                $pagedata['statistics'] = $this->getFacilityStatistics($id);
                $js_data = [
                    'pt_details'    =>  $pt_details
                ];
                $this->assets
                            ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                            ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");
                break;
            default:
                break;
        }

        $data['step'] = $step;
        $data['uuid'] = $id;
        $data['page'] = $view;
        $data['pageData'] = $pagedata;
        $data['submenu'] = $this->createSubMenu($step, $id);

        $this->assets
                    ->addJs('dashboard/js/libs/jquery.validate.js')
                    ->setJavascript('PTRounds/pt_rounds_js', $js_data);
        $this->template
                    ->setPageTitle('PT Rounds')
                    ->setPartial('PTRounds/new_v', $data)
                    ->adminTemplate();
    }

    function createSubMenu($active = NULL, $id = NULL){
        $menu = $this->menu;
        if($active == NULL){
            $active = key($menu);
        }
        
        $menu_list = "";

        if($menu){
            foreach($menu as $item  =>   $details){
                $active_link = "";
                if($active == $item){
                    $active_link = "active";
                }

                $menu_list .= " <li class='nav-item {$active_link}'>
                <a class='nav-link' href=".base_url('PTRounds/create/' . $item . '/' . $id)."><i class='{$details['icon']}'></i> {$details['text']}</a>
            </li>";
            }
        }

        return $menu_list;
    }

    function add($step, $id = NULL){
        $nextpage = $this->nextpage($step);
        $round_id = ($id != NULL) ? $this->db->get_where('pt_round', ['uuid' => $id])->row()->id : 0;
        if($step != NULL){
            if(($id != NULL && $step == "information") || ($step != "information" && $id != NULL) || ($step == "information" && $id == NULL)){
                switch ($step) {
                    case 'information':
                        $round_duration_frags = explode('-', preg_replace('/\s+/', '', $this->input->post('round_duration')));
                        $from_date = date('Y-m-d', strtotime($round_duration_frags[0]));
                        $to_date = date('Y-m-d', strtotime($round_duration_frags[1]));
                        if(!$id){
                            $pt_round_no = $this->generateRoundNumber();
                            $pt_data = [
                                'pt_round_no'       =>  $pt_round_no,
                                'tag'               =>  substr($pt_round_no, strpos($pt_round_no, "-") + 1),
                                'blood_lab_unit_id' =>  $this->lab_id_prefix . $this->input->post('blood_unit_lab_id'),
                                'from'              =>  $from_date,
                                'to'                =>  $to_date
                            ];

                            $this->db->insert('pt_round', $pt_data);
                            $round_id = $this->db->insert_id();

                            $this->db->select('uuid');
                            $this->db->where('id', $round_id);
                            $id = $this->db->get('pt_round')->row()->uuid;
                        }else{
                            $update_data = [
                                'blood_lab_unit_id' =>  $this->lab_id_prefix . $this->input->post('blood_unit_lab_id'),
                                'from'  =>  $from_date,
                                'to'    =>  $to_date
                            ];

                            $this->db->where('id', $round_id);
                            $this->db->update('pt_round', $update_data);
                        }
                        break;
                    case 'variables':
                        $filtered_input = array_filter($this->input->post());
                        $sorted_input = [];
                        foreach ($filtered_input as $key => $value) {
                            $key_frags = explode('_', $key);
                            if (is_array($key_frags) && count($key_frags) == 2) {
                                $section = $key_frags[0];
                                $ids_frag = explode('//', $key_frags[1]);
                                $sorted_input[$section][] = [
                                    'pt_round_uuid'     =>  $id,
                                    'equipment_uuid'    =>  $ids_frag[0],
                                    'sample_uuid'       =>  $ids_frag[1],
                                    $section . '_uuid'  =>  $ids_frag[2],
                                    'result'            =>  $value
                                ];
                            }
                        }
                        if ($sorted_input['tester']) {
                            foreach ($sorted_input['tester'] as $data_array) {
                                $equipment_id = $this->db->get_where('equipment', ['uuid' => $data_array['equipment_uuid']])->row()->id;
                                $sample_id = $this->db->get_where('pt_samples', ['uuid' =>  $data_array['sample_uuid']])->row()->id;
                                $tester_id = $this->db->get_where('pt_testers', ['uuid' =>  $data_array['tester_uuid']])->row()->id;
                                $result = $data_array['result'];

                                $sql = "CALL proc_pt_testers_result($equipment_id, $sample_id, $tester_id, $round_id, $result)";
                                $this->db->query($sql);
                            }
                        }
                        if ($sorted_input['lab']) {
                            foreach ($sorted_input['lab'] as $data_array) {
                                $equipment_id = $this->db->get_where('equipment', ['uuid' => $data_array['equipment_uuid']])->row()->id;
                                $sample_id = $this->db->get_where('pt_samples', ['uuid' =>  $data_array['sample_uuid']])->row()->id;
                                $lab_id = $this->db->get_where('pt_labs', ['uuid' =>  $data_array['lab_uuid']])->row()->id;
                                $result = $data_array['result'];

                                $sql = "CALL proc_pt_labs_results($equipment_id, $sample_id, $lab_id, $round_id, $result)";
                                $this->db->query($sql);
                            }
                        }
                        break;
                    case 'samples_labs':
                        $no_testers = $this->input->post('no_testers');
                        $no_labs = $this->input->post('no_labs');
                        if($no_testers > 0 && $no_labs > 0){
                            $testers_data = [];
                            $labs_data = [];
                            for ($i=0; $i < $no_testers; $i++) { 
                                $number = $i + 1;
                                $tester_name = 'Tester ' . $number;
                                $testers_data[] = [
                                    'tester_name'   =>  $tester_name,
                                    'pt_round_id'   =>  $round_id
                                ];
                            }

                            for ($i=0; $i < $no_labs; $i++) { 
                                $number = $i + 1;
                                $lab_name = 'Lab' . $number;
                                $labs_data[] = [
                                    'lab_name'      =>  $lab_name,
                                    'pt_round_id'   =>  $round_id
                                ];
                            }

                            $this->db->insert_batch('pt_testers', $testers_data);
                            $this->db->insert_batch('pt_labs', $labs_data);
                        }

                        $samples = $this->input->post('samples');
                        $sample_data = [];
                        foreach ($samples as $sample) {
                            $sample_data[] = [
                                'sample_name'   =>  $sample,
                                'pt_round_id'   =>  $round_id
                            ];
                        }

                        $this->db->insert_batch('pt_samples', $sample_data);
                        break;
                    case 'calendar':
                        foreach ($this->input->post() as $calendar_item_uuid => $dates) {
                           $item_id = $this->db->get_where('calendar_items', ['uuid'    =>  $calendar_item_uuid])->row()->id;
                           $dates_frags = explode('-', preg_replace('/\s+/', '', $dates));
                           $date_from = date('Y-m-d', strtotime($dates_frags[0]));
                           $date_to = date('Y-m-d', strtotime($dates_frags[1]));

                           $sql = "CALL proc_pt_calendar($item_id, $round_id, '$date_from', '$date_to')";
                           $this->db->query($sql);
                        }
                        break;
                    default:
                        $this->session->set_flashdata('error', 'Sorry. An error was encountered while proessing your request. Please try again');
                        redirect('PTRounds/create/' . $step);
                        break;
                }              
                redirect('PTRounds/create/' . $nextpage . '/' . $id,'refresh');               
            }else{
                echo $step;die;
                $step = "information";
                $this->session->set_flashdata('error', 'Sorry. An error was encountered while proessing your request. Please try again');
                redirect('PTRounds/create/' . $step);
            }
        }
    }

    function createVariablesAccordion($round_id){
        $accordion = "";
        $template = $this->config->item('default');

        $where = ['pt_round_id' =>  $round_id];
        $samples = $this->db->get_where('pt_samples', $where)->result();
        $testers = $this->db->get_where('pt_testers', $where)->result();
        $labs = $this->db->get_where('pt_labs', $where)->result();
        $equipments = $this->db->get_where('equipment', ['equipment_status'=>1])->result();

        $table_headers = $testers_arr = $labs_arr = [];

        $table_headers[] = "Sample ID";
        foreach($testers as $tester){
            $testers_arr[] = $tester->tester_name;
        }
        $testers_arr[] = "Mean";
        $testers_arr[] = "SD";
        $testers_arr[] = "2SD";
        $testers_arr[] = "Upper Limit";
        $testers_arr[] = "Lower Limit";
        $testers_arr[] = "CV";
        $testers_arr[] = "Outcome";
        foreach($labs as $lab){
            $testers_arr[] = $lab->lab_name;
            $testers_arr[] = "Field Stability";
        }
        $testers_arr[] = "Outcome";
        $table_headers = array_merge($table_headers, $testers_arr);

        foreach($equipments as $equipment){
            $table_body = [];
            $accordion .= "<div class = 'card'>";
            $accordion .= "<div class = 'card-header' role='tab' id = 'heading-{$equipment->id}'>
                <h5 class = 'mb-0'>
                    <a data-toggle = 'collapse' data-parent = '#accordion' href = '#collapse{$equipment->id}' aria-expanded = 'true' aria-controls = 'collapse{$equipment->id}'>
                        {$equipment->equipment_name}
                    </a>
                </h5>
            </div>
            <div id = 'collapse{$equipment->id}' class = 'collapse' role = 'tabpanel' aria-labelledby= 'heading-{$equipment->id}'>
                <div class = 'card-block'>";
                $table_data = [];
                foreach($samples as $sample){
                    $table_body = [];
                    $table_body[] = $sample->sample_name;
                    foreach($testers as $tester){
                        $where_array = [
                            'pt_round_id'   => $round_id,
                            'equipment_id'  =>  $equipment->id,
                            'pt_tester_id'  =>  $tester->id,
                            'pt_sample_id'  =>  $sample->id
                        ];
                        $tester_val = $this->db->get_where('pt_testers_result', $where_array)->row();
                        $testers_value = ($tester_val) ? $tester_val->result : "";
                        $table_body[] = "<input type = 'number' name = 'tester_{$equipment->uuid}//{$sample->uuid}//{$tester->uuid}' value = '{$testers_value}'/>";
                    }

                    $calculated_values = $this->db->get_where('pt_testers_calculated_v', ['pt_round_id' =>  $round_id, 'equipment_id'   =>  $equipment->id, 'pt_sample_id'  =>  $sample->id])->row(); 

                    $table_body[] = ($calculated_values) ? $calculated_values->mean : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->sd : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->doublesd : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->upper_limit : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->lower_limit : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->cv : 0;
                    $table_body[] = ($calculated_values) ? $calculated_values->outcome : 0;
                    foreach($labs as $lab){
                        $where_array = [
                            'pt_round_id'   => $round_id,
                            'equipment_id'  =>  $equipment->id,
                            'pt_lab_id'     =>  $lab->id,
                            'pt_sample_id'  =>  $sample->id
                        ];
                        $lab_val = $this->db->get_where('pt_labs_results', $where_array)->row();
                        $lab_value = ($lab_val) ? $lab_val->result : "";
                        $table_body[] = "<input type = 'number' name = 'lab_{$equipment->uuid}//{$sample->uuid}//{$lab->uuid}' value = '{$lab_value}'/>";
                        $stability_val = $this->db->get_where('pt_labs_calculated_v', $where_array)->row();
                        $stability_value = ($stability_val) ? $stability_val->stability : "N/A";
                        $table_body[] = $stability_value;
                    }
                    $table_body[] = "";

                    array_push($table_data, $table_body);
                }

                $this->table->set_template($template);
                $this->table->set_heading($table_headers);
            $accordion .= "<div class = 'table-responsive'>" . $this->table->generate($table_data) . "</div>";
            $accordion .= "</div>
            </div>";
            $accordion .= "</div>";
        }
        return $accordion;
    }

    function createCalendarForm($round_id){
        $calendar_form = "";

        $form_data = $this->M_PTRounds->findCalendarDetailsByRound($round_id);

        foreach($form_data as $calendar_data){
            $dates = "";
            if($calendar_data->date_from != "" && $calendar_data->date_to != ""){
                $dates = "{$calendar_data->date_from} - {$calendar_data->date_to}";
            }
            $calendar_form .= "<div class = 'form-group'>
                <label class = 'control-label'>{$calendar_data->calendar_item}</label>
                <input class = 'form-control daterange' type = 'text' name = '{$calendar_data->calendar_item_id}' value = '$dates'/>
            </div>";
        }

        return $calendar_form;
    }

    function createSamplesTable($round_id){
        $this->db->where('pt_round_id', $round_id);
        $query = $this->db->get('pt_samples');
        $samples = $query->result();

        $samples_table = "";
        if($samples){
            $counter = 1;
            foreach ($samples as $sample) {
               $samples_table .= "<tr>";
               $samples_table .= "<td>{$counter}</td>";
               $samples_table .= "<td>Sample <span class = 'sample-no'>{$counter}</span></td>";
               $samples_table .= "<td><input type = 'text' class = 'form-control' value = '{$sample->sample_name}' disabled/></td>";
               $samples_table .= "<td><center>N/A</center></td>";
               $samples_table .= "</tr>";

               $counter++;
            }
        }

        return $samples_table;
    }

    function ReadyParticipants($round_uuid){
        $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;

        $data = [];
        $title = "Ready Participants";

        $data = [
            'table_view'    =>  $this->createFacilityParticipantsTable($round_uuid),
            'back_link'     =>  base_url("PTRounds/"),
            'back_name'     =>  "Back to Rounds",
            'qa_unresponsive'     =>  base_url("PTRounds/QAUnresponsive/$round_uuid"),
            'qa_unresponsive_count' => $this->M_PTRounds->getQAUnresponsiveCount($round_uuid)->qa_count
        ];

        // echo '<pre>';print_r($this->M_PTRounds->getQAUnresponsiveCount($round_uuid)->qa_count);echo "</pre>";die();

       
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");

        $this->assets->setJavascript('PTRounds/pt_participants_submissions_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('PTRounds/pt_participants_submissions_v', $data)
                ->adminTemplate();
    }

    function FacilityParticipants($round_uuid,$facility_id){

        $data = [];
        $title = "Ready Participants";

        $data = [
            'table_view'    =>  $this->createFacilityParticipantsResults($round_uuid,$facility_id),
            'back_link'     =>  base_url("PTRounds/PTRounds/ReadyParticipants/$round_uuid"),
            'back_name'     =>  "Back to Ready Participants",
            'qa_unresponsive'     =>  base_url("PTRounds/QAUnresponsive/$round_uuid"),
            'qa_unresponsive_count' => $this->M_PTRounds->getQAUnresponsiveCount($round_uuid)->qa_count
        ];

       
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");

        $this->assets->setJavascript('PTRounds/pt_participants_submissions_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('PTRounds/pt_participants_submissions_v', $data)
                ->adminTemplate();
    }


    function QAUnresponsive($round_uuid){

        $data = [];
        $title = "Ready Participants";

        $data = [
            'table_view'    =>  $this->createQAUnresponsiveTable($round_uuid),
            'back_link'     =>  base_url("PTRounds/ReadyParticipants/$round_uuid")
        ];

       
        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");

        $this->assets->setJavascript('PTRounds/pt_participants_submissions_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('PTRounds/unresponsive_qa_v', $data)
                ->adminTemplate();
    }


    function createQAUnresponsiveTable($round_uuid){

        $template = $this->config->item('default');

        $change_state = '';

        $facilities = $this->M_PTRounds->getQAUnresponsive($round_uuid);
        // echo '<pre>';print_r($facilities);echo "</pre>";die();

        
        $tabledata = [];


        
        if($facilities){
            $counter = 0;
            $heading = [
                        "No.",
                        "QA / Supervisor ID",
                        "Name",
                        "Phone Number",
                        "Email"
                    ];
                    
            foreach($facilities as $facility){
                $counter ++;
                
                $qa_unresponsive = $this->db->get_where('participant_readiness_v',['facility_id'=> $facility->facility_id, 'user_type' => 'qareviewer', 'status' => 1, 'approved' => 1])->row();

                    

                    $tabledata[] = [
                        $counter,
                        $qa_unresponsive->username,
                        $qa_unresponsive->lastname.' '.$qa_unresponsive->firstname,
                        $qa_unresponsive->phone,
                        $qa_unresponsive->email_address
                    ];    
            }
        }else{
            $heading = [
                        "QA Table Empty"
                    ];
            $tabledata[] = [
                    "No data of any QA / Supervisors that have not approved"
                ];
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }


    function createFacilityParticipantsResults($round_uuid,$facility_id){

        $template = $this->config->item('default');

        $change_state = '';

        $facility_participants = $this->M_PTRounds->getFacilityParticipants($round_uuid,$facility_id);
        // echo '<pre>';print_r($facility_participants);echo "</pre>";die();

        $heading = [
            "No.",
            "Participant ID",
            "Participant",
            "Phone Number",
            "Actions"
        ];
        $tabledata = [];


        // echo '<pre>';print_r($facility_participants);echo "</pre>";die();

        if($facility_participants){
            $counter = 0;
            foreach($facility_participants as $participant){
                $counter ++;
                $participantid = $participant->participant_id;
                $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;
                

                $change_state = ' <a href = ' . base_url("PTRounds/PTRounds/ParticipantDetails/$round_uuid/$participantid") . ' class = "btn btn-primary btn-sm"><i class = "icon-note"></i>&nbsp;View Submissions</a>';

                
                $tabledata[] = [
                    $counter,
                    $participant->participant_id,
                    $participant->participant_lname.' '.$participant->participant_fname,
                    $participant->participant_phonenumber,
                    $change_state
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
    }


    function createFacilityParticipantsTable($round_uuid){

        $template = $this->config->item('default');

        $change_state = '';

        $facility_participants = $this->M_PTRounds->getFacilityParticipants($round_uuid);
        // echo '<pre>';print_r($facility_participants);echo "</pre>";die();

        $heading = [
            "No.",
            "Facility Name",
            "Participant ID",
            "Participant",
            "Phone Number",
            "Actions"
        ];
        $tabledata = [];


        // echo '<pre>';print_r($facility_participants);echo "</pre>";die();

        if($facility_participants){
            $counter = 0;
            foreach($facility_participants as $participant){
                $counter ++;
                $participantid = $participant->participant_id;
                $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round_uuid)->id;
                

                $change_state = ' <a href = ' . base_url("PTRounds/PTRounds/ParticipantDetails/$round_uuid/$participantid") . ' class = "btn btn-primary btn-sm"><i class = "icon-note"></i>&nbsp;View Submissions</a>';

                
                $tabledata[] = [
                    $counter,
                    '<a class="data-toggle="tooltip" data-placement="top" title="Facilities with this equipment"" href = ' . base_url("PTRounds/FacilityParticipants/$round_uuid/$participant->facility_id") . ' >'. $participant->facility_name .'</a>',
                    $participant->participant_id,
                    $participant->participant_lname.' '.$participant->participant_fname,
                    $participant->participant_phonenumber,
                    $change_state
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
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

        

        // echo "<pre>";print_r($datas[0]->cd3_absolute);echo "</pre>";die();
        
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

            // if($datas){
            //     $getCheck = $this->M_PTRound->getDataSubmission($round_id,$participant_id,$equipment->id)->status;
            // }else{
            //     $getCheck = 0; 
            // }
            //echo "<pre>";print_r($getCheck);echo "</pre>";die();

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
                        
                    //echo "<pre>";print_r($datas);echo "</pre>";die();

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

                        $equipment_tabs .= $value."</td> </tr>";
                        $counter2++;
                    }

                    $equipment_tabs .= "</table>
                                        </div>

                                        </div>   
                                        </div>
                                        </div>
                                        </div>
                                        </div>";

                    $equipment_tabs .= "";
        }

        $equipment_tabs .= "</div>";

        return $equipment_tabs;

    }



    function ParticipantDetails($round_uuid,$participant_id){
        $data = [];
        $title = "Ready Participants";


        $user = $this->M_Readiness->findUserByIdentifier('p_id', $participant_id);
        // echo "<pre>";print_r($user);echo "</pre>";die();

        $participant_uuid = $user->uuid;

        // $equipment_tabs = $this->load->module('QAReviewer/PTRound')->createTabs($round_uuid,$participant_uuid);
        $equipment_tabs = $this->createTabs($round_uuid,$participant_uuid);
        $data = [
                'pt_uuid'    =>  $round_uuid,
                'participant'    =>  $participant_id,
                'equipment_tabs'    =>  $equipment_tabs,

            ];

         $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js");
        // $this->assets->setJavascript('QAReviewer/participants_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('PTRounds/participant_submission_v', $data)
                ->adminTemplate();
    }




    function createPTRoundTable(){
        $rounds = $this->db->get('pt_round_v')->result();
        $ongoing = $prevfut = '';
        $round_array = [];
        if ($rounds) {
            foreach ($rounds as $round) {

                $round_id = $this->M_Readiness->findRoundByIdentifier('uuid', $round->uuid)->id;
                $getRound = $this->M_PTRounds->getDataSubmission($round_id);

                if($getRound){
                    $submissions = "<a class = 'btn btn-info btn-sm dropdown-item' href = '".base_url('PTRounds/PTRounds/ReadyParticipants/' . $round->uuid)."'><i class = 'fa fa-file-text-o'></i>&nbsp;Submissions</a>";
                }else{
                    $submissions = '';
                }

                $created = date('dS F, Y', strtotime($round->date_of_entry));
                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('PTRounds/create/information/' . $round->uuid)."'><i class = 'fa fa-eye'></i>&nbsp;View</a>";
                $panel_tracking = "<a class = 'btn btn-danger btn-sm dropdown-item' href = '".base_url('PTRounds/PanelTracking/details/' . $round->uuid)."'><i class = 'fa fa-truck'></i>&nbsp;Panel Tracking</a>";
                $status = ($round->status == "active") ? '<span class = "tag tag-success">Active</span>' : '<span class = "tag tag-danger">Inactive</span>';
                if ($round->type == "ongoing") {
                    $ongoing .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>
                    <td>
                        <div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                                $panel_tracking
                                $submissions
                            </div>
                        </div>
                    </td>

                    </tr>";
                }else{
                    $prevfut .= "<tr>
                    <td>{$round->pt_round_no}</td>
                    <td>{$created}</td>
                    <td>{$status}</td>

                    <td>
                        <div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton2' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Quick Actions
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                                $panel_tracking
                                $submissions
                            </div>
                        </div>
                    </td>

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

    function nextpage($current){
        reset($this->menu);
        while(key($this->menu) !== $current){
            next($this->menu);
        }

        $next_array = next($this->menu);
        $next_key = (key($this->menu)) ? key($this->menu) : "information";
        
        return $next_key;
    }

    function generateRoundNumber(){
        $prefix = "NHRL/CD4/";
        $year = date("Y");
        $this->db->select_max("tag");
        $query = $this->db->get('pt_round');
        
        $data = $query->row();
        $number = 17;
        if($data){
            if($data->tag != ""){
                $number = $data->tag + 1;
            }
        }

        $round_number = $prefix . $year . '-' . $number;
        
        return $round_number;
    } 

    function createDateRangeArray($start, $end){
        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($start,5,2), substr($start,8,2),substr($start,0,4));
        $iDateTo=mktime(1,0,0,substr($end,5,2), substr($end,8,2),substr($end,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m{d}',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m{d}',$iDateFrom));
            }
        }
        return $aryRange;
    } 

    function getCalendarData(){
        if ($this->input->is_ajax_request()) {
            $uuid = $this->input->post('round_uuid');
            $pt_round = $this->db->get_where('pt_round', ['uuid'   =>  $uuid])->row();

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

    function createCalendarColorLegend(){
        $calendar_items_span = "&nbsp;";
        $calendaritems = $this->db->get('calendar_items')->result();
        if ($calendaritems) {
            foreach ($calendaritems as $item) {
                $calendar_items_span .= "
                    <a class='dropdown-item'><span class = 'circle' style = 'color: {$item->colors}'></span>&nbsp;{$item->item_name}</a>
                ";
            }
        }

        return $calendar_items_span;
    }

    function getFacilityStatistics($round_uuid){
        $query = $this->db->query("CALL get_pt_facility_statistics('$round_uuid');");
        $result = $query->row();
        $query->next_result();
        $query->free_result();
        $statistics_array = [];
        $stats_section = "";
        if($result){
            $statistics_array[] = [
                                'text'      =>  'Facilities with participants',
                                'no'        =>  $result->with_participants,
                                'percentage'=>  round($result->with_participants / $result->total_sites * 100, 3)
                            ];
            $statistics_array[] = [
                                'text'      =>  'Participants Have Responded',
                                'no'        =>  $result->responded,
                                'percentage'=>  round($result->responded / $result->with_participants * 100, 3)
                            ];
            if($result->responded == 0){
                $statistics_array[] = [
                                'text'      =>  'Ready for this round',
                                'no'        =>  0,
                                'percentage'=>  round($result->responded / $result->total_sites * 100, 3)
                            ];
                $statistics_array[] = [
                                'text'      =>  'Not Ready for this round',
                                'no'        =>  0,
                                'percentage'=>  round($result->responded / $result->total_sites * 100, 3)
                            ];
            }else{
                $statistics_array[] = [
                                'text'      =>  'Ready for this round',
                                'no'        =>  $result->ready,
                                'percentage'=>  round($result->ready / $result->responded * 100, 3)
                            ];
                $statistics_array[] = [
                                'text'      =>  'Not Ready for this round',
                                'no'        =>  $result->not_ready,
                                'percentage'=>  round($result->not_ready / $result->responded * 100, 3)
                            ];
            }
            
            
            foreach ($statistics_array as $key => $value) {
                $percentage_color = "info";
                $percentage = $value['percentage'];
                
                if ($percentage >= 0 && $percentage < 25) {
                    $percentage_color = "danger";
                }elseif ($percentage >= 25 && $percentage < 50) {
                    $percentage_color = "warning";
                }elseif ($percentage >= 50 && $percentage < 75) {
                    $percentage_color = "info";
                }elseif ($percentage >= 75 && $percentage <= 100) {
                    $percentage_color = "success";
                }
                else {
                    # code...
                }
                
                $stats_section .= "<div class = 'card'>";
                $stats_section .= "<div class = 'card-block'>";
                $stats_section .= "<div class='h4 mb-0'>{$value['no']}</div>";
                $stats_section .= "<small class='text-muted text-uppercase font-weight-bold'>{$value['text']}</small>";
                $stats_section .= "<progress class='progress progress-xs progress-{$percentage_color} mt-1 mb-0' value='{$percentage}' max='100'>{$percentage}%</progress>";
                $stats_section .= "</div>";
                $stats_section .= "</div>";
            }
        }
        return $stats_section;
    }

    function getFacilitiesTable($pt_uuid, $type=NULL){
        if($this->input->is_ajax_request()){
            $columns = [];
            $limit = $offset = $search_value = NULL;
            $columns = [
                0 => "facility_code",
                1 => "facility_name",
                2 => "status"
            ];

            $limit = $_REQUEST['length'];
            $offset = $_REQUEST['start'];
            $search_value = $_REQUEST['search']['value'];

            $facilities = $this->M_PTRounds->searchFacilityReadiness($pt_uuid, $search_value, $limit, $offset);
            $data = [];
            if ($facilities) {
                foreach ($facilities as $facility) {
                    $status_label = $smart_status_label = $complete_mark = $resend_link = "";
                    $complete_mark = "<a target = '_blank' class='dropdown-item' href = '".base_url('PTRounds/facilityreadiness/'. $pt_uuid . '/' . $facility->facility_code)."'>Mark as Complete</a>";
                    if($facility->status == "No Response"){
                        $status_label = "<span class = 'tag tag-danger'>{$facility->status}</span>";
                    }elseif ($facility->status == "In Review") {
                        $status_label = "<span class = 'tag tag-warning'>{$facility->status}</span>";
                    }elseif ($facility->status == "Complete") {
                       $status_label = "<span class = 'tag tag-success'>{$facility->status}</span>";
                    }
                    if($facility->smart_status != NULL && $facility->smart_status != "No Response"){
                        if ($facility->smart_status === "1" || $facility->smart_status === "0") {
                            $smart_status_label = ($facility->smart_status == 1) ? "<span class = 'text-success'><i class = 'fa fa-circle'></i>&nbsp;Verdict: Accepted</span>" : "<span class = 'text-danger'><i class = 'fa fa-circle'></i>&nbsp;Verdict: Rejected</span>";
                            $complete_mark = "";
                        }else{

                            if($facility->smart_status == "Okay"){
                                $smart_status_label = "<a class = 'btn btn-success btn-sm' href = '#'><i class = 'fa fa-check'></i> Okay</a>";
                            }elseif($facility->smart_status == "Not Okay"){
                                $smart_status_label = "<a class = 'btn btn-danger btn-sm' href = '#'><i class = 'fa fa-times'></i> Not Okay</a>";
                            }else{
                                $smart_status_label = $facility->smart_status;
                            }
                        }
                    }else{
                        $smart_status_label = "<span class = 'tag tag-danger'>No Response</span>";
                        $resend_link = "<a class='dropdown-item' href = '".base_url('PTRounds/sendemails/'. $pt_uuid . '/' . $facility->facility_code)."'>Resend Link to Email</a>";
                    }
                    $data[] = [
                        $facility->facility_code,
                        $facility->facility_name,
                        $status_label,
                        "<center>" . $smart_status_label . "</center>",
                        "<div class = 'dropdown'>
                        <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'false'>
                            Quick Actions
                        </button>
                        <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                            <a target = '_blank' class='dropdown-item' href = '".base_url('PTRounds/facilityreadiness/'. $pt_uuid . '/' . $facility->facility_code)."'>View Response</a>
                            {$complete_mark}
                            {$resend_link}
                        </div>
                        </div>"
                    ];
                }
            }

            $all_facilities = $this->M_PTRounds->searchFacilityReadiness($pt_uuid, NULL, NULL, NULL);
            $total_data = count($all_facilities);
            $data_total= count($data);
            $json_data = [
                 "draw"             =>  intval( $_REQUEST['draw']),
                "recordsTotal"      =>  intval($total_data),
                "recordsFiltered"   =>  intval(count($this->M_PTRounds->searchFacilityReadiness($pt_uuid, $search_value, NULL, NULL))),
                'data'              =>  $data
             ];
            return $this->output->set_content_type('application/json')->set_output(json_encode($json_data));
        }
    }

    function facilityreadiness($pt_round_uuid, $facility_code){
        $result = $this->M_PTRounds->getParticipantRoundReadiness($facility_code, $pt_round_uuid);
        if($result){
            $data['pt_round'] = $pt_round_uuid;
            $data['result'] = $result;
            $data['response_table'] = $this->generateResponseQuestionnaire($result->readiness_id);
            $this->assets
                        ->addCss('dashboard/js/libs/icheck/skins/flat/blue.css')
                        ->addCss('dashboard/js/libs/icheck/skins/flat/green.css')
                        ->addCss('dashboard/js/libs/icheck/skins/flat/red.css')
                        ->addJs('dashboard/js/libs/icheck/icheck.min.js')
                        ->setJavascript('PTRounds/readiness_js');
            $this->template
                    ->setPartial('PTRounds/pt_facility_readiness_v', $data)
                    ->setPageTitle('Facility Readiness')
                    ->readinessTemplate();
        }else{
            show_404();
        }
    }

    function generateResponseQuestionnaire($readiness_id){
        $responses = $this->M_PTRounds->getReadinessResponses($readiness_id);
        $responses_table = "";
        if($responses){
            foreach ($responses as $response) {
                $responses_table .= "<tr>";
                $responses_table .= "<td>{$response->question_no}</td>";
                $responses_table .= "<td>{$response->question}</td>";
                $response_status = "";
                if($response->response != NULL && $response->extra_comments == NULL){
                    if($response->response == 0) {
                        $response_status = "No";
                    }
                    elseif($response->response == 1){
                        $response_status = "Yes";
                    }
                }elseif($response->extra_comments != NULL){
                    $response_status = $response->extra_comments;
                }
                $responses_table .= "<td>{$response_status}</td>";
                $responses_table .= "</tr>";
            }
        }

        return $responses_table;
    }

    function addReadinessAssessmentOutcome($readiness_id){
        $readiness = $this->db->get_where('participant_readiness', ['readiness_id' => $readiness_id])->row();
        if ($readiness) {
            $participant = $this->db->get_where('participants', ['uuid' => $readiness->participant_id])->row();
            $facility = $this->db->get_where('facility', ['id'  =>  $participant->participant_facility])->row();
            $verdict = $this->input->post('verdict');
            $status = ($this->input->post('status') == 'on') ? 1 : 0;
            $comment = $this->input->post('readiness_comment');

            $update_data = [
                'verdict'   =>  $verdict,
                'status'    =>  $status,
                'comment'   =>  $comment
            ];

            $this->db->where('readiness_id', $readiness->readiness_id);
            $result = $this->db->update('participant_readiness', $update_data);
            if ($result) {
                $this->session->set_flashdata('success', 'Successfully updated assessment outcome');
            }else{
                $this->session->set_flashdata('error', 'There was an issue updating your assessment outcome');
            }
            redirect('PTRounds/facilityreadiness/' . $readiness->pt_round_no . '/' . $facility->facility_code);
        }else{
            $this->session->set_flashdata('error', 'There was an issue updating your assessment outcome');
            redirect('Dashboard','refresh');
        }
    }

    function sendemails($pt_round_uuid, $facility_code = NULL){
        $pt_round = $this->db->get_where('pt_round', ['uuid'   =>  $pt_round_uuid])->row();
        $this->load->library('Mailer');
        if ($pt_round) {
            $recepients = [];
            if($facility_code == NULL){
                $facilities = $this->M_PTRounds->searchFacilityReadiness($pt_round_uuid);
                $recepients_array = [];
                foreach ($facilities as $facility) {
                    if($facility->smart_status == NULL || $facility->smart_status == "No Response"){
                        $participants = $this->db->get_where('participants', ['participant_facility' =>  $facility->facility_id])->result();
                        if($participants){
                            foreach ($participants as $participant) {
                               $recepients_array[$participant->participant_email]  =  $participant->participant_fname . ' ' . $participant->participant_lname;
                            }
                        }elseif ($facility->email != "NULL" && $facility->email != "(NULL)" && $facility->email != "") {
                            // $recepients_array[$facility->email]  =  $facility->facility_name;
                        }
                    }
                }

                if ($recepients_array) {
                    $recepients = $recepients_array;
                }
            }else{
                $facility = $this->db->get_where('facility', ['facility_code'   =>  $facility_code])->row();
                $participants = $this->db->get_where('participants', ['participant_facility' =>  $facility->id])->result();
                if($participants){
                    foreach ($participants as $participant) {
                        $recepients[$participant->participant_email]  =  $participant->participant_fname . ' ' . $participant->participant_lname;
                    }
                }elseif ($facility->email != "NULL" && $facility->email != "(NULL)" && $facility->email != "") {
                    // $recepients[$facility->email]  =  $facility->facility_name;
                }
            }

            $data = [
                'pt_round_no'   =>  $pt_round->pt_round_no,
                'round_uuid'    =>  $pt_round->uuid,
                'due_date'  => $pt_round->to
            ];
            $body = $this->load->view('Template/email/assessment_link_v', $data, TRUE);
            $result = $this->mailer->sendMail('john.otaalo@strathmore.edu', 'PT Round Evaluation Link', $body, $recepients);
            if($result == true){
                $this->session->set_flashdata('success', 'Successfully sent the evaluation link(s)');
            }else{
                $this->session->set_flashdata('error', 'There was an error sending the evaluation link(s). Please contact the system administrator for further guidance');
            }

            redirect('PTRounds/create/facilities/' . $pt_round_uuid);
        }else{
            show_404();
        }
    }
}