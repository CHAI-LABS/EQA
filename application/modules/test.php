<?php

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
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        if($county_id == 0 && $facility_id == 0){
            $counties = $this->Program_m->getCounties();

            // echo "<pre>";print_r(count($counties));echo "</pre>";die();
            foreach ($counties as $county) {
                $partcount = $no_of_participants = $passed = $failed = 0;

                $labels[] = $county->county_name;

                $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$county->county_id)->participants;

                if($no_of_participants == 0){
                    $failed = $passed = 0;
                    $pass_rate = 0;

                }else{

                    // $submissions = $this->Program_m->getReadyParticipants($round_id, $county->county_id);
                    $submissions = $this->Program_m->RespondedParticipants($round_id, $round_uuid, $county->county_id);
                    
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
                    
                } 

                if($partcount == 0){
                    $pass_rate = 0;
                }else{
                    $pass_rate = (($passed / $partcount) * 100);
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
                    $partcount = $no_of_participants = $passed = $failed = 0;

                    $labels[] = $facility->facility_name;

                    $no_of_participants = $this->Program_m->ParticipatingParticipants($round_uuid,$facility->county_id,$facility->facility_id)->participants;


                    if($no_of_participants == 0){
                        $failed = $passed = 0;
                        $pass_rate = 0;

                    }else{
                        // $participants = $this->Program_m->getReadyParticipants($round_id, $facility->county_id, $facility->facility_id);

                        $submissions = $this->Program_m->RespondedParticipants($round_id, $round_uuid, $facility->county_id, $facility->facility_id);
                
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
                         

                        $failed = $partcount - $passed;
                        $pass_rate = (($passed / $partcount) * 100);

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
                            
                            // $submissions = $this->Program_m->getReadyParticipants($round->id, $county_id, $facility_id);

                            $submissions = $this->Program_m->RespondedParticipants($round_id, $round_uuid, $county_id, $facility_id);
                            
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
                                    
                                    $pass_rate = (($passed / $no_of_participants) * 100);

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
?>