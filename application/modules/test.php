<?php

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

                $round_uuid = $this->db->get_where('pt_round_v', ['id' => $round->id])->row()->uuid;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round->id])->result();

                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }

                $submissions = $this->Program_m->RespondedParticipants($round->id, $round_uuid, $county_id, $facility_id);

                $no_of_participants = COUNT($submissions);

                $passed = $failed = $partcount = 0;
                if($submissions){
                    foreach ($submissions as $participant) {
                        $partcount++;
                        // echo "<pre>";print_r($participant);echo "</pre>";die();
                        $tabledata = [];
                        $cd3abs_samples = $cd4abs_samples = $cd3per_samples = $cd4per_samples = $final_score = $samp_counter =  0;
                        $cd3abs_acceptable = $cd3abs_unacceptable = 0;
                        $cd4abs_acceptable = $cd4abs_unacceptable = 0;
                        $cd3per_acceptable = $cd3per_unacceptable = 0;
                        $cd4per_acceptable = $cd4per_unacceptable = 0;

                        //cd4 abs
                        $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
                        foreach ($samples as $sample) {
                            $comment = '';
                            $samp_counter++;

                            $cd4_abs_values = $this->getEvaluationResults($round->id, $participant->equipment_id, $sample->id,'cd4','absolute');

                            $mean_2 = ($cd4_abs_values->cd4_absolute_mean) ? $cd4_abs_values->cd4_absolute_mean : 0;
                            $sd_2 = ($cd4_abs_values->cd4_absolute_sd) ? $cd4_abs_values->cd4_absolute_sd : 0;
                            $upper_limit_2 = $mean_2 + $sd_2;
                            $lower_limit_2 = $mean_2 - $sd_2;
                            

                            $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$participant->equipment_id,$sample->id,$participant->p_id);

                            
                            if($part_cd4){
                                if($part_cd4->cd4_absolute != 0){
                                
                                    $zerocheck = $part_cd4->cd4_absolute - $mean_2;
                                    $cd4abs_samples++;

                                    if($zerocheck == 0 || $sd_2 == 0){
                                        $sdi = 3;
                                    }else{
                                        $sdi = (($part_cd4->cd4_absolute - $mean_2) / $sd_2);
                                    }

                                    if($part_cd4->cd4_absolute == 0){
                                        $cd4abs_acceptable++;
                                    }
                                    
                                    if($sdi > -2 && 2 > $sdi){
                                        $cd4abs_acceptable++;
                                    }else{
                                        $cd4abs_unacceptable++;
                                    }

                                    $cd4abs_grade = (($cd4abs_acceptable / $cd4abs_samples) * 100); 

                                }else{
                                    $cd4abs_grade = 0;
                                }
                                    
                            }else{
                                $cd4abs_grade = 0;
                            }     
                        }

                        
                        //cd4 abs

                        //cd3 per
                        $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
                        foreach ($samples as $sample) {
                            $comment = '';
                            $samp_counter++;
                            
                            $cd3_per_values = $this->getEvaluationResults($round->id, $participant->equipment_id, $sample->id,'cd3','percent');

                            $mean_2 = ($cd3_per_values->cd3_percent_mean) ? $cd3_per_values->cd3_percent_mean : 0;
                            $sd_2 = ($cd3_per_values->cd3_percent_sd) ? $cd3_per_values->cd3_percent_sd : 0;
                            $upper_limit_2 = $mean_2 + $sd_2;
                            $lower_limit_2 = $mean_2 - $sd_2;
                            
                            $part_cd3 = $this->Analysis_m->absoluteValue($round->id,$participant->equipment_id,$sample->id,$participant->p_id);
                            
                            if($part_cd3){
                                if($part_cd3->cd3_percent != 0){
                                
                                    $zerocheck = $part_cd3->cd3_percent - $mean_2;
                                    $cd3per_samples++;

                                    if($zerocheck == 0 || $sd_2 == 0){
                                        // echo "<pre>";print_r($zerocheck);echo "</pre>";die();
                                        $sdi = 3;
                                    }else{
                                        $sdi = (($part_cd3->cd3_percent - $mean_2) / $sd_2);
                                        
                                    }
                                    
                                    if($sdi > -2 && 2 > $sdi){
                                        $cd3per_acceptable++;
                                        // $comment = "Acceptable";

                                    }else{
                                        $cd3per_unacceptable++;
                                        // $comment = "Unacceptable";
                                    }

                                    $cd3per_grade = (($cd3per_acceptable / $cd3per_samples) * 100);
                                }else{
                                    $cd3per_grade = 0;
                                }
                                
                            }else{
                                $cd3per_grade = 0;
                            }            
                        }

                        //cd3 per

                        //cd4 per
                        $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
                        foreach ($samples as $sample) {
                            $comment = '';
                            $samp_counter++;
                            
                            $cd4_per_values = $this->getEvaluationResults($round->id, $participant->equipment_id, $sample->id,'cd4','percent');
 
                            $mean_2 = ($cd4_per_values->cd4_percent_mean) ? $cd4_per_values->cd4_percent_mean : 0;
                            $sd_2 = ($cd4_per_values->cd4_percent_sd) ? $cd4_per_values->cd4_percent_sd : 0;
                            $upper_limit_2 = $mean_2 + $sd_2;
                            $lower_limit_2 = $mean_2 - $sd_2;
                            
                            $part_cd4 = $this->Analysis_m->absoluteValue($round->id,$participant->equipment_id,$sample->id,$participant->p_id);

                            if($part_cd4){
                                if($part_cd4->cd4_percent != 0){
                                
                                    $zerocheck = $part_cd4->cd4_percent - $mean_2;
                                    $cd4per_samples++;

                                    if($zerocheck == 0 || $sd_2 == 0){
                                        $sdi = 3;
                                        
                                    }else{
                                        
                                        $sdi = (($part_cd4->cd4_percent - $mean_2) / $sd_2);
                                    }
                      
                                    if($sdi > -2 && 2 > $sdi){
                                        $cd4per_acceptable++;
                                        // $comment = "Acceptable";

                                    }else{
                                        $cd4per_unacceptable++;
                                        // $comment = "Unacceptable";
                                    } 

                                    $cd4per_grade = (($cd4per_acceptable / $cd4per_samples) * 100);
                                }else{
                                    $cd4per_grade = 0;
                                }
                                
                            }else{
                                $cd4per_grade = 0;
                            }  
                                
                        }
                        //cd4 per

                        //cd3 abs
                        $total_grade = $final_score = $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
                        foreach ($samples as $sample) {
                            $comment = '';
                            $samp_counter++;
                              
                            $cd3_abs_values = $this->getEvaluationResults($round->id, $participant->equipment_id, $sample->id,'cd3','absolute');

                            
                            $mean_2 = ($cd3_abs_values->cd3_absolute_mean) ? $cd3_abs_values->cd3_absolute_mean : 0;
                            $sd_2 = ($cd3_abs_values->cd3_absolute_sd) ? $cd3_abs_values->cd3_absolute_sd : 0;
                            $upper_limit_2 = $mean_2 + $sd_2;
                            $lower_limit_2 = $mean_2 - $sd_2;
                          
                            $part_cd3 = $this->Analysis_m->absoluteValue($round->id,$participant->equipment_id,$sample->id,$participant->p_id);

                            
                            if($part_cd3){
                                if($part_cd3->cd3_absolute != 0){
                                
                                    $zerocheck = $part_cd3->cd3_absolute - $mean_2;
                                    $cd3abs_samples++;

                                    if($zerocheck == 0 || $sd_2 == 0){
                                        $sdi = 5;
                                        // echo "<pre>";print_r($zerocheck);echo "</pre>";die();
                                    }else{
                                        $sdi = (($part_cd3->cd3_absolute - $mean_2) / $sd_2);
                                    }
                                    
                                    if($sdi > -2 && 2 > $sdi){
                                        $cd3abs_acceptable++;
                                        // $comment = "Acceptable";

                                    }else{
                                        $cd3abs_unacceptable++;
                                        // $comment = "Unacceptable";
                                    }

                                    $cd3abs_grade = (($cd3abs_acceptable / $cd3abs_samples) * 100);
                                }else{
                                    $cd3abs_grade = 0;
                                }
                                   
                            }else{
                                // array_push($tabledata, 0, "Unacceptable");
                            }  
                        }

                        $total_samp = $cd3abs_samples + $cd4abs_samples + $cd3per_samples + $cd4per_samples;
                        $total_accept_grade = $cd3abs_acceptable + $cd4abs_acceptable + $cd3per_acceptable + $cd4per_acceptable;

                        if($total_samp == 0){
                            $final_score = 0;
                        }else{
                            $final_score = (($total_accept_grade / $total_samp) * 100);
                        }


                // echo "<pre>";print_r($final_score);echo "</pre>";die();
                        
                        if($final_score >= 80){
                            $passed++;
                        }else{
                            $failed++;
                        }       
                    }

                    $pass_rate = round((($passed / $no_of_participants) * 100), 2);


                }else{
                    $pass_rate = round(0, 2);
                }
                // echo "<pre>";print_r($passed);echo "</pre>";

                

                $labels[] = $round->pt_round_no;

                if($facility_id == 0){
                    $no_participants['data'][] = $pass_rate;
                }else{
                    $no_participants['data'][] = $final_score;
                }
                

                $counter++;

            }
        }



        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants];

        // echo "<pre>";print_r($graph_data);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }




?>