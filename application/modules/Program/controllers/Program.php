<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Program extends MY_Controller {
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Program_m');
        $this->load->module('Analysis');
        $this->load->model('Analysis_m');
    }

    public function index(){
        $title = "Program";
        $counter = 0;
        
        $this->db->where('type', 'previous');
        $this->db->order_by('id', 'ASC');
        $rounds = $this->db->get('pt_round_v')->result();
        $round_list = '<select id="round-select" class="form-control select2-single">';
        foreach ($rounds as $round) {
            $counter++;
            if($counter == 1){
                $round_list .= '<option selected = "selected" value='.$round->id.'>'.$round->pt_round_no.'</option>';
                $firstround = $round->id;
            }else{
                $round_list .= '<option value='.$round->id.'>'.$round->pt_round_no.'</option>';
            }
        }
        $round_list .= '</select>';

        $this->db->join("pt_participant_review_v prv", "cv.id = prv.county_id");
        $this->db->group_by("cv.id");
        $this->db->order_by("cv.county_name");
        $counties = $this->db->get('county_v cv')->result();
        $county_list = '<select id="county-select" class="form-control select2-single">
                        <option selected = "selected" value="0">All Counties</option>';
        foreach ($counties as $county) {
            $county_list .= '<option value='.$county->id.'>'.$county->county_name.'</option>';
        }
        $county_list .= '</select>';

        // $facilities = $this->db->get('facility_v')->result();
        $facility_list = '<option selected = "selected" value="0">All Facilities</option>';


        $data = [
            'back_link' => '',
            'page_title' => "Program Graphs",
            'round_option' => $round_list,
            'county_option' => $county_list,
            'facility_option' => $facility_list,
            'round' => $firstround
        ];

        $this->assets
                ->addCss('sweetalert2/dist/sweetalert2.min.css');
        $this->assets
                ->addJs('dashboard/js/libs/jquery.dataTables.min.js')
                ->addJs('dashboard/js/libs/dataTables.bootstrap4.min.js')
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js')
                ->addJs('js/Chart.min.js')
                ->addJs('js/chartsjs-plugin-data-labels.js')
                ->addJs('js/Chart.PieceLabel.js')
                ->addJs('sweetalert2/dist/sweetalert2.min.js');
        $this->assets->setJavascript('Program/program_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Program/program_view', $data)
                ->adminTemplate();
    }


    public function getFacilities($county_id = null){
        if($county_id == 0){
            $this->db->join("pt_participant_review_v prv", "prv.facility_id = fv.facility_id");
            $this->db->group_by("fv.facility_id");
            // $facilities = $this->db->get('facility_v fv')->result();
        }else{
            $this->db->join("pt_participant_review_v prv", "prv.facility_id = fv.facility_id");
            $this->db->where('prv.county_id', $county_id);
            $this->db->group_by("fv.facility_id");
            
            // $facilities = $this->db->get_where('facility_v', ['county_id' => $county_id])->result();
        }
        $facilities = $this->db->get('facility_v fv')->result();
        
        return $this->output->set_content_type('application/json')->set_output(json_encode($facilities));
    }


    public function ParticipantPass($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        
        $submissions = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
    
        foreach ($submissions as $submission) {
            $partcount++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            if($facilityid){
                $facil_id = $facilityid->facility_id;

                $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facil_id])->row();

                if($faci_name){
                    $facility_name = $faci_name->facility_name;
                    $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                    if($county){
                        $county_name = $county->county_name;
                    }else{
                        $county_name = "No County";
                    }
                }else{
                    $facility_name = "No Facility";
                    $county_name = "No County";
                }
            }else{
                $facility_name = "No Facility";
                $county_name = "No County";
            }

            foreach ($samples as $sample) {
                $samp_counter++;
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 
                
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,$submission->participant_id);
               
                if($part_cd4){

                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                        $acceptable++;

                    }    
                }      
            }

            $grade = (($acceptable / $samp_counter) * 100);

            if($grade == 100){
                $passed++;
            }else{
                $failed++;
            }
        }
        
        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid, $county_id, $facility_id)->participants;

        $datasets = [
            'label'         =>  ['Passed','Failed'],
            'backgroundColor' => ['rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
            'data' => [$passed, $failed]
        ];
        $labels = ['Passed','Failed'];

        $graph_data['labels'] = $labels;
        $graph_data['no_participants'] = $no_of_participants;
        $graph_data['datasets'] = [$datasets];

        // echo "<pre>";print_r("reached");echo "</pre>";die();

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function DisqualifiedParticipants($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $equipment_breakdown = $reagent_stock_out = $analyst_unavailable = $pending_capa = 0;


        // echo "<pre>";print_r("reached");echo "</pre>";die();

        // $round = $this->db->get_where('pt_round_v', ['id' => $round_id])->row();

        $rounds = $this->Program_m->getLatestRounds();

        $equipment = [
            'label'         =>  'Equipment Breakdown',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)'
        ];

        $reagent = [
            'label'         =>  'Reagent Stock-Out',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)'
        ];

        $analysts = [
            'label'         =>  'Unavailable Analysts',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)'
        ];

        $capa = [
            'label'         =>  'Pending CAPA',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)'
        ];

        if($rounds){
            foreach ($rounds as $round) {
                $round_uuid = $round->uuid;

                $labels[] = $round->pt_round_no;

                $equipment_breakdown = $this->Program_m->getEquipmentBreakdown($round_uuid, $county_id, $facility_id);

                if($equipment_breakdown){
                    $equipment['data'][] = $equipment_breakdown->equipments;
                }else{
                    $equipment['data'][] = 0;
                }

                $reagent_stock_out = $this->Program_m->getReagentStock($round_uuid, $county_id, $facility_id);

                if($reagent_stock_out){
                    $reagent['data'][] = $reagent_stock_out->reagents;
                }else{
                    $reagent['data'][] = 0;
                }
                
                $analyst_unavailable = $this->Program_m->getUnavailableAnalyst($round_uuid, $county_id, $facility_id);

                if($analyst_unavailable){
                    $analysts['data'][] = $analyst_unavailable->analysts;
                }else{
                    $analysts['data'][] = 0;
                }

                $pending_capa = $this->Program_m->getPendingCapa($round_uuid, $county_id, $facility_id);

                if($pending_capa){
                    $capa['data'][] = $pending_capa->capas;
                }else{
                    $capa['data'][] = 0;
                }

            }
        }else{
            $equipment['data'][] = 0;
            $analysts['data'][] = 0;
            $reagent['data'][] = 0;
            $capa['data'][] = 0;
        }

        // $datasets = [
        //     'label'         =>  ['Equipment Breakdown','Reagent Stock-Out','Analyst Unavailable','Pending CAPA'],
        //     'backgroundColor' => ['rgba(211,84,0,0.5)','rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
        //     'data' => [(int)$equipment_breakdown, (int)$reagent_stock_out, (int)$analyst_unavailable, (int)$pending_capa]
        // ];

        // $labels = ['Equipment Breakdown','Reagent Stock-Out','Analyst Unavailable','Pending CAPA'];

        
        $graph_data['round'] = $round->pt_round_no;
        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$equipment,$reagent,$analysts,$capa];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function OverallOutcomeGraph($round_id,$county_id,$facility_id){
        $facility_part = $labels = $graph_data = $datasets = $data = $pass = $fail = array();
        $counter = $pass_rate = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];


        $no_participants = [
            'label'         =>  'Score (%)',
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter],
            'yAxisID' => 'y-axis-2',
            'type' => 'line'
        ];

        $counter++;

        $pass = [
            'label'         =>  'Pass',
            'backgroundColor' => $backgroundColor[$counter],
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter]
        ];

        $counter++;

        $fail = [
            'label'         =>  'Fail',
            'backgroundColor' => $backgroundColor[$counter],
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter]
        ];

        $round = $this->db->get_where('pt_round_v', ['id' =>  $round_id])->row();
        $round_uuid = $round->uuid;
        $round_name = $round->pt_round_no;

        if($county_id == 0 && $facility_id == 0){
            $counties = $this->Program_m->getCounties();

            foreach ($counties as $county) {
                $no_of_participants = $passed = $failed = 0;

                $labels[] = $county->county_name;

                $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$county->county_id)->participants;

                if($no_of_participants == 0){
                    $failed = $passed = 0;
                    $pass_rate = 0;

                }else{
                    $equipments = $this->Program_m->Equipments();

                    foreach ($equipments as $key => $equipment) {
                        $partcount = 0;

                        $equipment_id = $equipment->id;

                        $participants = $this->Program_m->getReadyParticipants($round_id, $county->county_id, $county->facility_id);

     
                        foreach ($participants as $participant) {
                            $partcount ++;
                            $novalue = $sampcount = $acceptable = $unacceptable = 0;

                            $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                            foreach ($samples as $sample) {
                                $sampcount++;
                                $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                                if($cd4_values){

                                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                                }else{
                                    $upper_limit = 0;
                                    $lower_limit = 0;
                                } 

                                $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                                if($part_cd4){
                                    
                                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                        $acceptable++;    
                                    } else{
                                        $unacceptable++;    
                                    } 
                                } 
                            } 

                            if($acceptable == $sampcount) {
                                $passed++;
                            }
                        } 
                    } 

                    $failed = $no_of_participants - $passed;
                    $pass_rate = (($passed / $no_of_participants) * 100);

                } 

                $no_participants['data'][] = round($pass_rate, 2);
                $pass['data'][] = $passed;
                $fail['data'][] = $failed;
            }
            $graph_data['y_axis_left_name'] = "Health Facilities";
            $graph_data['x_axis_name'] = "Counties";
        }else{

            if($facility_id == 0){
                $graph_data['y_axis_left_name'] = "Participants";
                $facilities = $this->Program_m->getFacilities($county_id);

                foreach ($facilities as $facility) {
                    $no_of_participants = $passed = $failed = 0;

                    $labels[] = $facility->facility_name;

                    $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$facility->county_id,$facility->facility_id)->participants;

                    if($no_of_participants == 0){
                        $failed = $passed = 0;
                        $pass_rate = 0;

                    }else{
                        $equipments = $this->Program_m->Equipments();

                        foreach ($equipments as $key => $equipment) {
                            $partcount = 0;

                            $equipment_id = $equipment->id;

                            $participants = $this->Program_m->getReadyParticipants($round_id, $facility->county_id, $facility->facility_id);

                            foreach ($participants as $participant) {
                                $partcount ++;
                                $novalue = $sampcount = $acceptable = $unacceptable = 0;

                                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                                foreach ($samples as $sample) {
                                    $sampcount++;
                                    $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                                    if($cd4_values){

                                        $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                        $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                                    }else{
                                        $upper_limit = 0;
                                        $lower_limit = 0;
                                    } 

                                    $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                                    if($part_cd4){
                                        
                                        if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                            $acceptable++;    
                                        } else{
                                            $unacceptable++;    
                                        } 
                                    } 
                                } 

                                if($acceptable == $sampcount) {
                                    $passed++;
                                }
                            } 
                        } 

                        $failed = $no_of_participants - $passed;
                        $pass_rate = (($passed / $no_of_participants) * 100);

                    } 

                    $no_participants['data'][] = round($pass_rate, 2);
                    $pass['data'][] = $passed;
                    $fail['data'][] = $failed;
                }

                $graph_data['x_axis_name'] = "Participants";
            }else{
                //Facility Data
                $graph_data['y_axis_left_name'] = "Participant";
                $facility_participants = $participating = $data = array();

                $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
                ];

                $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
                ];

                $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
                ];

                $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
                ];

                $no_of_participants = $passed = $failed = 0;

                if($county_id == 0){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }

                $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$county_id,$facility_id)->participants;

                if($no_of_participants == 0){

                    $pass_rate = 0;
                    // array_push($facility_part, $pass_rate);

                }else{
                    
                    $partcount = 0;
                    $rounds = $this->Program_m->getLatestRounds();

                    if($rounds){
                        foreach ($rounds as $round) {
                            $color = $counter = 0;
                            $labels[] = $round->pt_round_no; 
                            
                            $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);
                            
                            if($submissions){

                                foreach ($submissions as $participant) {
                                    $partcount ++;
                                    $novalue = $sampcount = $acceptable = $unacceptable = 0;

                                    $participant_no = $this->db->get_where('participant_readiness_v', ['p_id' =>  $participant->participant_id])->row()->username;

                                    $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();

                                    foreach ($samples as $sample) {
                                        $sampcount++;
                                        $cd4_values = $this->Program_m->getRoundResults($round->id, $participant->equipment_id, $sample->id);

                                        if($cd4_values){
                                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                                        }else{
                                            $upper_limit = 0;
                                            $lower_limit = 0;
                                        } 

                                        $part_cd4 = $this->Program_m->absoluteValue($round->id,$participant->equipment_id,$sample->id,$participant->participant_id);

                                        if($part_cd4){
                                            if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                                $acceptable++;    
                                            } else{
                                                $unacceptable++;    
                                            } 
                                        }
                                    }

                                    if($acceptable == $sampcount) {
                                        $passed++;
                                    }
                                    
                                    $pass_rate = (($passed / $partcount) * 100);

                                    if($color == 7){
                                        $color = 0;
                                    }

                                    if (!(array_key_exists($participant_no, $participating))) {

                                        $facility_participants[$counter] = [
                                            'label'         =>  $participant_no,
                                            // 'backgroundColor' => $backgroundColor[$color],
                                            'borderColor' => $borderColor[$color],
                                            'highlightFill' => $highlightFill[$color],
                                            'highlightStroke' => $highlightStroke[$color],
                                            'data' => []
                                        ];

                                        $participating[$participant_no] = $counter;
                                        
                                           
                                    }

                                    // $facility_participants[$participant_no] = array(
                                    //     'data'  => array(round($pass_rate, 2))
                                    // );

                                    foreach ($facility_participants as $partkey => $partvalue) {
                                                                                
                                        if($partvalue['label'] == $participant_no){
                                           
                                            array_push($facility_participants[$partkey]['data'], round($pass_rate, 2));
                                            
                                        }
                                    }

                                    

                                    $color++;
                                    $counter++;
                                }
                            }else{
                                foreach ($facility_participants as $partkey => $partvalue) {
                                    
                                    // echo "<pre>";print_r("not equal");echo "</pre>";die();
                                    array_push($facility_participants[$partkey]['data'], round(0, 2));
                                }
                                
                            }
                        }

                        $round_number = count($rounds);
                        

                        if(!(empty($facility_participants))){
                            foreach ($facility_participants as $partkey => $partvalue) {
                                if ($round_number != count($partvalue['data'])) {
                                    // echo "<pre>";print_r("not equal");echo "</pre>";die();

                                    for ($i=0; $i < $round_number-1; $i++) { 
                                        array_unshift($facility_participants[$partkey]['data'], 0);
                                    }
                                }  
                            }
                        }

                        
                    }else{
                        $labels[] = 'No previous round';

                    }
                }
            }
        }

        if($facility_id != 0){
            $graph_data['round'] = $round_name;
            $graph_data['y_axis_name'] = "Score (%)";
            $graph_data['x_axis_name'] = "Rounds";
            $graph_data['round'] = $round_name;
            $graph_data['labels'] = $labels;
            $graph_data['datasets'] = $facility_participants;
        }else{
            $graph_data['round'] = $round_name;
            $graph_data['labels'] = $labels;
            $graph_data['datasets'] = [$no_participants, $pass, $fail];
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function PassFailGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = $pass_rate = 0;
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];

        $rounds = $this->Program_m->getLatestRounds();

        $no_participants = [
            'label'         =>  'No. of Participants',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)'
        ];

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $subcount = $partcount = $counter = 0;
                
                // $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();

                if($facility_id){
                    $facility = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row();
                    $county_id = $facility->county_id;
                    $title = $facility->facility_name;
                }else if($county_id != 0 && $facility_id == 0){
                    $county = $this->db->get_where('county_v', ['id' => $county_id])->row();
                    $title = $county->county_name;
                }else{
                    $title = 'National';
                }

                $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);

                if($submissions){
                    
                    foreach ($submissions as $submission) {
                        $partcount++;
                        // $samp_counter = $acceptable = $unacceptable = 0;
                        // $tabledata = [];
             
                        // $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

                        // if($facilityid){
                        //     $facil_id = $facilityid->facility_id;

                        //     $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facil_id])->row();

                        //     if($faci_name){
                        //         $facility_name = $faci_name->facility_name;
                        //         $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                        //         if($county){
                        //             $county_name = $county->county_name;
                        //         }else{
                        //             $county_name = "No County";
                        //         }
                        //     }else{
                        //         $facility_name = "No Facility";
                        //         $county_name = "No County";
                        //     }
                        // }else{
                        //     $facility_name = "No Facility";
                        //     $county_name = "No County";
                        // }

                        // foreach ($samples as $sample) {
                        //     $samp_counter++;
                            
                        //     $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' => $round->id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                        //     if($cd4_values){
                        //         $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                        //         $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                        //     }else{
                        //         $upper_limit = 0;
                        //         $lower_limit = 0;
                        //     } 
                            
                        //     $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$submission->equipment_id,$sample->id,$submission->participant_id);
                           
                        //     if($part_cd4){

                        //         if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                        //             $acceptable++;

                        //         }    
                        //     }      
                        // }

                        // $grade = (($acceptable / $samp_counter) * 100);

                        // if($grade == 100){
                        //     $passed++;
                        // }else{
                        //     $failed++;
                        // }
                    }

                    

                    // $no_of_participants = $this->Program_m->ParticipatingParticipants($round->uuid, $county_id, $facility_id)->participants;

                    // $pass_rate = round((($passed / $partcount) * 100) , 2);

                }else{
                    $partcount = 0;
                    // $pass_rate = round(0, 2);
                    // $passed = 0;
                    // $failed = 0;
                }
                $labels[] = $round->pt_round_no;
                $no_participants['data'][] = $partcount;
                
                // $pass['data'][] = $passed;
                // $fail['data'][] = $failed; 

                $subcount++;   
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['title'] = $title;
        $graph_data['datasets'] = [$no_participants];
        // $graph_data['datasets'] = [$pass, $fail];

        // echo "<pre>";print_r($subcount);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function PassFailRateGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = $pass_rate = 0;
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $accept = $unaccept = $passed = $failed = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];

        $rounds = $this->Program_m->getLatestRounds();

        $no_participants = [
                    'label'         =>  'Score (%)',
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter],
                    'yAxisID' => 'y-axis-1',
                    'type' => 'line'
                ];

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $partcount = $counter = 0;

                // $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();

                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }

                $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);

                if($submissions){
                
                    foreach ($submissions as $submission) {
                        $partcount++;
                        $samp_counter = $acceptable = $unacceptable = 0;
                        $tabledata = [];
             
                        $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

                        if($facilityid){
                            $facil_id = $facilityid->facility_id;

                            $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facil_id])->row();

                            if($faci_name){
                                $facility_name = $faci_name->facility_name;
                                $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                                if($county){
                                    $county_name = $county->county_name;
                                }else{
                                    $county_name = "No County";
                                }
                            }else{
                                $facility_name = "No Facility";
                                $county_name = "No County";
                            }
                        }else{
                            $facility_name = "No Facility";
                            $county_name = "No County";
                        }

                        foreach ($samples as $sample) {
                            $samp_counter++;
                            
                            $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round->id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                            if($cd4_values){
                                $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                            }else{
                                $upper_limit = 0;
                                $lower_limit = 0;
                            } 
                            
                            $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$submission->equipment_id,$sample->id,$submission->participant_id);
                           
                            if($part_cd4){

                                if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                    $acceptable++;

                                }    
                            }      
                        }

                        $grade = (($acceptable / $samp_counter) * 100);

                        if($grade == 100){
                            $passed++;
                        }else{
                            $failed++;
                        }
                    }
                    // echo "<pre>";print_r($no_of_participants);die;

                    $failed = $partcount - $passed;
                    $pass_rate = round((($passed / $partcount) * 100), 2);

                }else{
                    $pass_rate = round(0, 2);
                }


                $labels[] = $round->pt_round_no;
                $no_participants['data'][] = $pass_rate;

                $counter++;
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants];

        // echo "<pre>";print_r($graph_data);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function ResondentNonRateGraph($round_id,$county_id,$facility_id){
        $responses = $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = $respondent_rate = 0;
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $partcount = $accept = $unaccept = $passed = $failed = $no_non_responsive = $no_responsive = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];


        $rounds = $this->Program_m->getLatestRounds();
        $no_participants = [
                    'label'         =>  'Score (%)',
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter],
                    'yAxisID' => 'y-axis-1',
                    'type' => 'line'
                ];

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $counter = 0;

                

                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();
                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }
                $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);

                if($submissions){
                    // echo "<pre>";print_r($submissions);echo "</pre>";
        
                    foreach ($submissions as $submission) {
                        $partcount++;
                        $novalue = $sampcount = $acceptable = $unacceptable = 0;
                        $tabledata = [];
             

                        $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

                        if($facilityid){
                            $facil_id = $facilityid->facility_id;

                            $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facil_id])->row();

                            if($faci_name){
                                $facility_name = $faci_name->facility_name;
                                $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                                if($county){
                                    $county_name = $county->county_name;
                                }else{
                                    $county_name = "No County";
                                }
                            }else{
                                $facility_name = "No Facility";
                                $county_name = "No County";
                            }
                        }else{
                            $facility_name = "No Facility";
                            $county_name = "No County";
                        }

                        foreach ($samples as $sample) {
                            $sampcount++;
                            
                            $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round->id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                            if($cd4_values){
                                $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                            }else{
                                $upper_limit = 0;
                                $lower_limit = 0;
                            } 
                            
                            $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$submission->equipment_id,$sample->id,$submission->participant_id);
                           
                            if($part_cd4){

                                if($part_cd4->cd4_absolute == 0){
                                    $novalue++;
                                }
                            }      
                        }

                        if($novalue == $sampcount){
                            $no_non_responsive++;
                        }
                    }


                    $no_responsive = $partcount - $no_non_responsive;

                    $respondent_rate = round((($no_responsive / $partcount) * 100), 2);
                }else{
                    // echo "<pre>";print_r("reaching here");die;
                    $respondent_rate = round(0, 2);
                }

                $no_participants['data'][] = $respondent_rate;
                 
                $labels[] = $round->pt_round_no;

                // array_push($no_participants['data'], $respondent_rate);

                $counter++;
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants];

        

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function ResondentNonGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = $respondent_rate = 0;
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $partcount = $accept = $unaccept = $passed = $failed = $no_non_responsive = $no_responsive = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];

        $rounds = $this->Program_m->getLatestRounds();

        $responsive = [
            'label'         =>  'Submitted Results',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)'
        ];


        $non_responsive = [
            'label'         =>  ' Not Responded',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)'
        ];

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $partcount = $counter = 0;

                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();
                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }

                $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);

                if($submissions){
        
                    foreach ($submissions as $submission) {
                        $partcount++;
                        $novalue = $sampcount = $acceptable = $unacceptable = 0;
                        $tabledata = [];
             

                        $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

                        if($facilityid){
                            $facil_id = $facilityid->facility_id;

                            $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facil_id])->row();

                            if($faci_name){
                                $facility_name = $faci_name->facility_name;
                                $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                                if($county){
                                    $county_name = $county->county_name;
                                }else{
                                    $county_name = "No County";
                                }
                            }else{
                                $facility_name = "No Facility";
                                $county_name = "No County";
                            }
                        }else{
                            $facility_name = "No Facility";
                            $county_name = "No County";
                        }

                        foreach ($samples as $sample) {
                            $sampcount++;
                            
                            $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round->id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                            if($cd4_values){
                                $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                            }else{
                                $upper_limit = 0;
                                $lower_limit = 0;
                            } 
                            
                            $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$submission->equipment_id,$sample->id,$submission->participant_id);
                           
                            if($part_cd4){

                                if($part_cd4->cd4_absolute == 0){
                                    $novalue++;
                                }
                            }      
                        }

                        if($novalue == $sampcount){
                            $no_non_responsive++;
                        }

                    // $no_of_participants = $this->Program_m->ParticipatingParticipants($round->uuid, $county_id, $facility_id)->participants;

                        $no_responsive = $partcount - $no_non_responsive;

                    }

                }else{
                    $no_responsive = 0;
                    $no_non_responsive = 0;
                }

                $labels[] = $round->pt_round_no;

                $responsive['data'][] = $no_responsive;
                $non_responsive['data'][] = $no_non_responsive;

            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$responsive, $non_responsive];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function OverallInfo($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $responsive = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round = $this->db->get_where('pt_round_v', ['id' => $round_id])->row();
        $round_uuid = $round->uuid;
        $round_name = $round->pt_round_no;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }
        $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
        $equipments = $this->Program_m->Equipments();

        if($facility_id == 0){
            foreach ($equipments as $key => $equipment) {
                $counter++;
                
                $equipment_id = $equipment->id;

                foreach ($participants as $participant) {
                    $partcount ++;
                    $novalue = $sampcount = $acceptable = $unacceptable = 0;

                    foreach ($samples as $sample) {
                        $sampcount++;

                        $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                        if($cd4_values){

                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                        }else{
                            $upper_limit = 0;
                            $lower_limit = 0;
                        } 

                        $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                        if($part_cd4){
                            
                            if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                $acceptable++;    
                            } else{
                                $unacceptable++;    
                            } 

                            if($part_cd4->cd4_absolute == 0){
                                $novalue++;
                            }
                        } 
                    } 

                    if($novalue == $sampcount){
                        $non_responsive++;
                    }

                    if($acceptable == $sampcount) {
                        $passed++;
                    }

                }
            }
        }else{
            foreach ($participants as $participant) {
                $partcount ++;
                $novalue = $sampcount = $acceptable = $unacceptable = 0;

                foreach ($equipments as $key => $equipment) {
                    $counter++;
                    
                    $equipment_id = $equipment->id;

                    foreach ($samples as $sample) {
                        $sampcount++;

                        $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                        if($cd4_values){

                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                        }else{
                            $upper_limit = 0;
                            $lower_limit = 0;
                        } 

                        $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                        if($part_cd4){
                            
                            if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                $acceptable++;    
                            } else{
                                $unacceptable++;    
                            } 

                            if($part_cd4->cd4_absolute == 0){
                                $novalue++;
                            }
                        } 
                    } 

                    if($novalue == $sampcount){
                        $non_responsive++;
                    }

                    if($acceptable == $sampcount) {
                        $passed++;
                    }

                }
            }

            $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
        }

        $unable = $this->Program_m->getUnableParticipants($round_uuid, $county_id, $facility_id)->participants;
        $disqualified = $this->Program_m->getRoundVerdict($round_uuid, $county_id, $facility_id)->participants;
        $total_facilities = $this->Program_m->TotalFacilities($round_uuid, $county_id, $facility_id)->facilities;
        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid, $county_id, $facility_id)->participants;
        $failed = $no_of_participants - $passed;
        $responsive = $no_of_participants - $non_responsive;

        // echo "<pre>";print_r($total_facilities);echo "</pre>";die();

        $datasets7 = [
            'label'         =>  'Total No. of Facilities Enrolled',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$total_facilities]
        ];
        $datasets1 = [
            'label'         =>  'No. of Participants (Current Round)',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$no_of_participants]
        ];
        $datasets2 = [
            'label'         =>  'Passed',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$passed]
        ];
        
        $datasets3 = [
            'label'         =>  'Non-Responsive',
            'backgroundColor' => 'rgba(127,140,141,0.5)',
            'borderColor' => 'rgba(127,140,141,0.8)',
            'highlightFill' => 'rgba(127,140,141,0.75)',
            'highlightStroke' => 'rgba(127,140,141,1)',
            'data' => [$non_responsive]
        ];
        $datasets4 = [
            'label'         =>  'Unable to Report',
            'backgroundColor' => 'rgba(241,196,15,0.5)',
            'borderColor' => 'rgba(241,196,15,0.8)',
            'highlightFill' => 'rgba(241,196,15,0.75)',
            'highlightStroke' => 'rgba(241,196,15,1)',
            'data' => [$unable]
        ];
        $datasets5 = [
            'label'         =>  'Failed',
            'backgroundColor' => 'rgba(52,73,94,0.5)',
            'borderColor' => 'rgba(52,73,94,0.8)',
            'highlightFill' => 'rgba(52,73,94,0.75)',
            'highlightStroke' => 'rgba(52,73,94,1)',
            'data' => [$failed]
        ];
        $datasets6 = [
            'label'         =>  'Disqualified',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$disqualified]
        ];

        // echo "<pre>";print_r($unable);echo "</pre>";die();
        $graph_data['round'] = $round_name;
        $graph_data['responsive'] = $responsive;
        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets7, $datasets1, $datasets3, $datasets4, $datasets6, $datasets2, $datasets5];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }



    public function OverallResponses($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }
        $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
        $equipments = $this->Program_m->Equipments();



        if($facility_id == 0){
            foreach ($equipments as $key => $equipment) {
                $counter++;
                
                $equipment_id = $equipment->id;

                foreach ($participants as $participant) {
                    $partcount ++;
                    $novalue = $sampcount = $acceptable = $unacceptable = 0;

                    foreach ($samples as $sample) {
                        $sampcount++;

                        $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                        if($cd4_values){

                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                        }else{
                            $upper_limit = 0;
                            $lower_limit = 0;
                        } 

                        $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                        if($part_cd4){
                            
                            if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                $acceptable++;    
                            } else{
                                $unacceptable++;    
                            } 

                            if($part_cd4->cd4_absolute == 0){
                                $novalue++;
                            }
                        } 
                    } 

                    if($novalue == $sampcount){
                        $non_responsive++;
                    }

                    if($acceptable == $sampcount) {
                        $passed++;
                    }

                }
            }
        }else{
            foreach ($participants as $participant) {
                $partcount ++;
                $novalue = $sampcount = $acceptable = $unacceptable = 0;

                foreach ($equipments as $key => $equipment) {
                    $counter++;
                    
                    $equipment_id = $equipment->id;

                    foreach ($samples as $sample) {
                        $sampcount++;

                        $cd4_values = $this->Program_m->getRoundResults($round_id, $equipment_id, $sample->id);

                        if($cd4_values){

                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                        }else{
                            $upper_limit = 0;
                            $lower_limit = 0;
                        } 

                        $part_cd4 = $this->Program_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->participant_id);

                        if($part_cd4){
                            
                            if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                                $acceptable++;    
                            } else{
                                $unacceptable++;    
                            } 

                            if($part_cd4->cd4_absolute == 0){
                                $novalue++;
                            }
                        } 
                    } 

                    if($novalue == $sampcount){
                        $non_responsive++;
                    }

                    if($acceptable == $sampcount) {
                        $passed++;
                    }

                }
            }

            $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
        }  



        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid, $county_id, $facility_id)->participants;
        $responsive = $no_of_participants - $non_responsive;

        // $datasets = [
        //     'label'         =>  ['NO OF PARTICIPANTS','RESPONSIVE','NON RESPONSIVE'],
        //     'backgroundColor' => ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
        //     'data' => [$no_of_participants, $responsive, $non_responsive]
        // ];
        $datasets = [
            'label'         =>  ['Responsive','Non-Responsive'],
            'backgroundColor' => ['rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
            'data' => [$responsive, $non_responsive]
        ];
        $labels = ['Responsive','Non-Responsive'];

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets];
        // echo "<pre>";print_r($no_of_participants);echo "</pre>";die();

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function getName($county_id, $facility_id){
        $data = "National";

        if($facility_id){
            $name = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->facility_name;
            $data = $name . ' Facility ';
        }elseif($county_id){
            $name = $this->db->get_where('county_v', ['id' => $county_id])->row()->county_name;
            $data = $name . ' County ';
        }else{
            $data = "National";
        }


        return $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }


    public function createFailedParticipants($round_id,$county_id,$facility_id){
        // $data = 'checking';
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = $failCount = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $heading = [
            "No.",
            "Participant ID",
            "County",
            "Facility"
        ];

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        // $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id])->result();
        $submissions = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);

        foreach ($submissions as $submission) {
            $sub_counter++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $faci_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row();

                if($faci_name){
                    $facility_name = $faci_name->facility_name;
                    $county = $this->db->get_where('county_v', ['id' =>  $facilityid->county_id])->row();
                    if($county){
                        $county_name = $county->county_name;
                    }else{
                        $county_name = "No County";
                    }
                }else{
                    $facility_name = "No Facility";
                    $county_name = "No County";
                }
            }else{
                $facility_name = "No Facility";
                $county_name = "No County";
            }

            foreach ($samples as $sample) {
                $samp_counter++;
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $submission->equipment_id, 'sample_id'  =>  $sample->id])->row();

                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 

                
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,$submission->participant_id);

               
                if($part_cd4){

                    // $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';

                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
                        $acceptable++;
                        // $comment = "Acceptable";

                    }else{
                        $unacceptable++;
                        // $comment = "Unacceptable";
                    }   

                    if($part_cd4->cd4_absolute == 0 || $part_cd4->cd4_absolute == null){
                        $zerocount++;
                    }

                    // array_push($tabledata, $part_cd4->cd4_absolute, $comment);
                    
                }else{
                    // array_push($tabledata, 0, "Unacceptable");
                }   
                
            }

            $grade = (($acceptable / $samp_counter) * 100);


            $overall_grade = round($grade, 2) . ' %';

            if($grade == 100){
                $review = "Satisfactory Performance";
            }else if($grade > 0 && $grade < 100){
                $failCount++;
                $review = "Unsatisfactory Performance";
            }else{
                $failCount++;
                $review = "Non-responsive";
            }

            $ready_part = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row();

            $facility_code = $ready_part->facility_code;

            array_push($tabledata, $failCount, $ready_part->participant_id, $county_name, $ready_part->facility_name);

            if($review != "Satisfactory Performance"){

                $table[$count] = $tabledata;
            }

            $count++;

        }

        $this->table->set_template($template);
        $this->table->set_heading($heading);

        return $this->output->set_content_type('application/json')->set_output(json_encode($this->table->generate($table)));
    }




}

/* End of file Program.php */
/* Location: ./application/modules/Program.php */