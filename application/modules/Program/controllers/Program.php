<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Program extends MY_Controller {
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Program_m');
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
                $round_list .= '<option selected = "selected" value='.$round->uuid.'>'.$round->pt_round_no.'</option>';
                $round = $round->id;
            }else{
                $round_list .= '<option value='.$round->uuid.'>'.$round->pt_round_no.'</option>';
            }
        }
        $round_list .= '</select>';


        $counties = $this->db->get('county_v')->result();
        $county_list = '<select id="county-select" class="form-control select2-single">
                        <option selected = "selected" value="0">Select a County</option>';
        foreach ($counties as $county) {
            $county_list .= '<option value='.$county->id.'>'.$county->county_name.'</option>';
        }
        $county_list .= '</select>';

        // $facilities = $this->db->get('facility_v')->result();
        $facility_list = '<option selected = "selected" value="0">Select a Facility</option>';


        $data = [
            'page_title' => "Program Graphs",
            'round_option' => $round_list,
            'county_option' => $county_list,
            'facility_option' => $facility_list,
            'round' => $round
        ];


        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js')
                ->addJs('js/Chart.min.js')
                ->addJs('js/chartsjs-plugin-data-labels.js')
                ->addJs('js/Chart.PieceLabel.js');
        $this->assets->setJavascript('Program/program_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Program/program_view', $data)
                ->adminTemplate();
    }


    public function getFacilities($county_id){
        $counties = $this->db->get_where('facility_v', ['county_id' => $county_id])->result();

        return $this->output->set_content_type('application/json')->set_output(json_encode($counties));
    }


    public function ParticipantPass($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
        $equipments = $this->Program_m->Equipments();

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

        

        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid)->participants;
        $responsive = $no_of_participants - $non_responsive;
        $participants = $responsive + $non_responsive;

        $datasets = [
            'label'         =>  ['NO OF PARTICIPANTS','RESPONSIVE','NON RESPONSIVE'],
            'backgroundColor' => ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
            'data' => [$participants, $responsive, $non_responsive]
        ];
        $labels = ['NO OF PARTICIPANTS','PASSED','FAILED'];

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function DisqualifiedParticipants($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $equipment_breakdown = $reagent_stock_out = $analyst_unavailable = $pending_capa = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;

        $equipment_breakdown = $this->Program_m->getEquipmentBreakdown($round_uuid)->equipments;
        $reagent_stock_out = $this->Program_m->getReagentStock($round_uuid)->reagents;
        // $analyst_unavailable = $this->Program_m->getUnavailableAnalyst($round_uuid)->analysts;
        $pending_capa = $this->Program_m->getPendingCapa($round_uuid)->capas;

        // echo "<pre>";print_r($pending_capa);echo "</pre>";die();

        $datasets1 = [
            'label'         =>  'EQUIPMENT BREAKDOWN',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$equipment_breakdown]
        ];
        $datasets2 = [
            'label'         =>  'REAGENT STOCK-OUT',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$reagent_stock_out]
        ];
        $datasets3 = [
            'label'         =>  'ANALYST UNAVAILABLE',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$analyst_unavailable]
        ];
        $datasets4 = [
            'label'         =>  'PENDING CAPA',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$pending_capa]
        ];

        

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets1, $datasets2, $datasets3, $datasets4];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function OverallOutcomeGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = $pass = $fail = array();
        $counter = 0;

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
        ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];


        $no_participants = [
            'label'         =>  'NO. OF PARTICIPANTS',
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter],
            'type' => 'line'
        ];

        $counter++;

        $pass = [
            'label'         =>  'PASS',
            'backgroundColor' => $backgroundColor[$counter],
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter]
        ];

        $counter++;

        $fail = [
            'label'         =>  'FAIL',
            'backgroundColor' => $backgroundColor[$counter],
            'borderColor' => $borderColor[$counter],
            'highlightFill' => $highlightFill[$counter],
            'highlightStroke' => $highlightStroke[$counter]
        ];

        $counties = $this->Program_m->getCounties();

        foreach ($counties as $county) {
            $no_of_participants = $passed = $failed = 0;

            $labels[] = $county->county_name;

            $round_uuid = $this->db->get_where('pt_round_v', ['id' =>  $round_id])->row()->uuid;

            $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$county->county_id)->participants;

            // $parttotal += $no_of_participants; 

            if($no_of_participants == 0){
                $failed = $passed = 0;

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

               

            } 



            $no_participants['data'][] = $no_of_participants;
            $pass['data'][] = $passed;
            $fail['data'][] = $failed;
        }



        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants, $pass, $fail];
        // echo "<pre>";print_r($nopart);echo "</pre>";die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function PassFailGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = 0;
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

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $counter = 0;

                $no_participants = [
                    'label'         =>  'NO. OF PARTICIPANTS',
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter],
                    'type' => 'line'
                ];

                $counter++;

                $pass = [
                    'label'         =>  'PASS',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];

                $counter++;

                $fail = [
                    'label'         =>  'FAIL',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];


                $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
                $equipments = $this->Program_m->Equipments();

                foreach ($equipments as $key => $equipment) {
                    
                    
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

                
                $labels[] = $round->pt_round_no;

                $no_of_participants = $this->Program_m->ParticipatingParticipants($round->uuid)->participants;
                $failed = $no_of_participants - $passed;

                $no_participants['data'][] = $no_of_participants;
                $pass['data'][] = $passed;
                $fail['data'][] = $failed;

                
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants, $pass, $fail];

        // echo "<pre>";print_r($graph_data);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function ResondentNonGraph($round_id,$county_id,$facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $participants = $pass = $fail = 0;
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

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $counter = 0;

                $no_participants = [
                    'label'         =>  'NO. OF PARTICIPANTS',
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter],
                    'type' => 'line'
                ];

                $counter++;

                $responsive = [
                    'label'         =>  'RESPONSIVE',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];

                $counter++;

                $non_responsive = [
                    'label'         =>  'NON RESPONSIVE',
                    'backgroundColor' => $backgroundColor[$counter],
                    'borderColor' => $borderColor[$counter],
                    'highlightFill' => $highlightFill[$counter],
                    'highlightStroke' => $highlightStroke[$counter]
                ];


                // $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
                $equipments = $this->Program_m->Equipments();

                foreach ($equipments as $key => $equipment) {
                    
                    
                    $equipment_id = $equipment->id;

                    foreach ($participants as $participant) {
                        $partcount ++;
                        $novalue = $sampcount = 0;

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

                                if($part_cd4->cd4_absolute == 0){
                                    $novalue++;
                                }
                            } 
                        } 

                        if($novalue == $sampcount){
                            $no_non_responsive++;
                        }

                    }
                }

                
                $labels[] = $round->pt_round_no;

                // echo "<pre>";print_r($no_non_responsive);die;

                $no_of_participants = $this->Program_m->ParticipatingParticipants($round->uuid)->participants;
                $no_responsive = $no_of_participants - $no_non_responsive;

                

                $no_participants['data'][] = $no_of_participants;
                $responsive['data'][] = $no_responsive;
                $non_responsive['data'][] = $no_non_responsive;

                
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants, $responsive, $non_responsive];

        

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }


    public function OverallInfo($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
        $equipments = $this->Program_m->Equipments();

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

        $unable = $this->Program_m->getUnableParticipants($round_uuid)->participants;
        $disqualified = $this->Program_m->getRoundVerdict($round_uuid)->participants;
        $total_facilities = $this->Program_m->TotalFacilities()->facilities;
        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid)->participants;
        $failed = $no_of_participants - $passed;
        $responsive = $no_of_participants - $non_responsive;

        $datasets7 = [
            'label'         =>  'TOTAL N0. OF FACILITIES ENROLLED',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)',
            'data' => [$total_facilities]
        ];
        $datasets1 = [
            'label'         =>  'N0. OF PARTICIPANTS (CURRENT ROUND)',
            'backgroundColor' => 'rgba(52,152,219,0.5)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'data' => [$no_of_participants]
        ];
        $datasets2 = [
            'label'         =>  'PASSED',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)',
            'data' => [$passed]
        ];
        
        $datasets3 = [
            'label'         =>  'NON-RESPONSIVE',
            'backgroundColor' => 'rgba(127,140,141,0.5)',
            'borderColor' => 'rgba(127,140,141,0.8)',
            'highlightFill' => 'rgba(127,140,141,0.75)',
            'highlightStroke' => 'rgba(127,140,141,1)',
            'data' => [$non_responsive]
        ];
        $datasets4 = [
            'label'         =>  'UNABLE TO REPORT',
            'backgroundColor' => 'rgba(241,196,15,0.5)',
            'borderColor' => 'rgba(241,196,15,0.8)',
            'highlightFill' => 'rgba(241,196,15,0.75)',
            'highlightStroke' => 'rgba(241,196,15,1)',
            'data' => [$unable]
        ];
        $datasets5 = [
            'label'         =>  'FAILED',
            'backgroundColor' => 'rgba(52,73,94,0.5)',
            'borderColor' => 'rgba(52,73,94,0.8)',
            'highlightFill' => 'rgba(52,73,94,0.75)',
            'highlightStroke' => 'rgba(52,73,94,1)',
            'data' => [$failed]
        ];
        $datasets6 = [
            'label'         =>  'DISQUALIFIED',
            'backgroundColor' => 'rgba(231,76,60,0.5)',
            'borderColor' => 'rgba(231,76,60,0.8)',
            'highlightFill' => 'rgba(231,76,60,0.75)',
            'highlightStroke' => 'rgba(231,76,60,1)',
            'data' => [$disqualified]
        ];

        // echo "<pre>";print_r($unable);echo "</pre>";die();

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets7, $datasets1, $datasets6, $datasets4, $datasets3, $datasets2, $datasets5];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }



    public function OverallResponses($round_id, $county_id, $facility_id){
        $labels = $graph_data = $datasets = $data = array();
        $counter = $unsatisfactory = $satisfactory = $disqualified = $unable = $non_responsive = $partcount = $accept = $unaccept = $passed = $failed = 0;

        $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round_id])->row()->uuid;
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
        $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
        $equipments = $this->Program_m->Equipments();

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

        

        $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid)->participants;
        $responsive = $no_of_participants - $non_responsive;
        $participants = $responsive + $non_responsive;

        $datasets = [
            'label'         =>  ['NO OF PARTICIPANTS','RESPONSIVE','NON RESPONSIVE'],
            'backgroundColor' => ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(231,76,60,0.5)'],
            'data' => [$participants, $responsive, $non_responsive]
        ];
        $labels = ['NO OF PARTICIPANTS','RESPONSIVE','NON RESPONSIVE'];

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$datasets];

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }




}

/* End of file Program.php */
/* Location: ./application/modules/Program.php */