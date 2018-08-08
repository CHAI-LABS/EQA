<?php

public function generateReport($type,$round_uuid){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $color = $count = $zerocount = $partcount = 0;

        $facility_participants = $participating = $data = array();

        $backgroundColor = ['rgba(52,152,219,0.5)','rgba(46,204,113,0.5)','rgba(211,84,0,0.5)','rgba(231,76,60,0.5)','rgba(127,140,141,0.5)','rgba(241,196,15,0.5)','rgba(52,73,94,0.5)'
                ];

        $borderColor = ['rgba(52,152,219,0.8)','rgba(46,204,113,0.8)','rgba(211,84,0,0.8)','rgba(231,76,60,0.8)','rgba(127,140,141,0.8)','rgba(241,196,15,0.8)','rgba(52,73,94,0.8)'
        ];

        $highlightFill = ['rgba(52,152,219,0.75)','rgba(46,204,113,0.75)','rgba(211,84,0,0.75)','rgba(231,76,60,0.75)','rgba(127,140,141,0.75)','rgba(241,196,15,0.75)','rgba(52,73,94,0.75)'
        ];

        $highlightStroke = ['rgba(52,152,219,1)','rgba(46,204,113,1)','rgba(211,84,0,1)','rgba(231,76,60,1)','rgba(127,140,141,1)','rgba(241,196,15,1)','rgba(52,73,94,1)'
        ];


        $rounds = $this->db->get_where('pt_round_v', ['uuid'=>$round_uuid])->row();
        $round_name = str_replace('/', '-', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;
        $round_id = $rounds->id;

        // $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        // $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>Mfl Code</th>
            <th>Lab</th>';
            
        $heading = [
            "Mfl Code",
            "Lab"
        ];

        $column_data = array('Mfl Code','Lab');
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        array_push($heading, 'Final Score (%)', 'Grade');
        array_push($column_data, 'Final Score (%)', 'Grade');

        $html_body .= ' 
        <th>Grade (%)</th>
            <th>Review</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        // $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id])->result();
        $submissions = $this->Program_m->RespondedParticipantsData($round_id, $round_uuid);
        $no_of_participants = COUNT($submissions);

        foreach ($submissions as $participant) {
            $partcount++;
            $tabledata = [];
            $passed = $failed = 0;
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

                $cd4_abs_values = $this->getEvaluationResults($round_id, $participant->equipment_id, $sample->id,'cd4','absolute');

                $mean_2 = ($cd4_abs_values->cd4_absolute_mean) ? $cd4_abs_values->cd4_absolute_mean : 0;
                $sd_2 = ($cd4_abs_values->cd4_absolute_sd) ? $cd4_abs_values->cd4_absolute_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;

                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->p_id);

                
                
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

            // echo "<pre>";print_r($cd4_abs_values);echo "</pre>";die();
            //cd4 abs

            //cd3 per
            $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;
                
                $cd3_per_values = $this->getEvaluationResults($round_id, $participant->equipment_id, $sample->id,'cd3','percent');

                $mean_2 = ($cd3_per_values->cd3_percent_mean) ? $cd3_per_values->cd3_percent_mean : 0;
                $sd_2 = ($cd3_per_values->cd3_percent_sd) ? $cd3_per_values->cd3_percent_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;

                $part_cd3 = $this->Analysis_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->p_id);
                
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
                
                $cd4_per_values = $this->getEvaluationResults($round_id, $participant->equipment_id, $sample->id,'cd4','percent');

                $mean_2 = ($cd4_per_values->cd4_percent_mean) ? $cd4_per_values->cd4_percent_mean : 0;
                $sd_2 = ($cd4_per_values->cd4_percent_sd) ? $cd4_per_values->cd4_percent_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;
                

                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->p_id);

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
                  
                $cd3_abs_values = $this->getEvaluationResults($round_id, $participant->equipment_id, $sample->id,'cd3','absolute');

                $mean_2 = ($cd3_abs_values->cd3_absolute_mean) ? $cd3_abs_values->cd3_absolute_mean : 0;
                $sd_2 = ($cd3_abs_values->cd3_absolute_sd) ? $cd3_abs_values->cd3_absolute_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;

                $part_cd3 = $this->Analysis_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->p_id);

                
                if($part_cd3){
                    if($part_cd3->cd3_absolute != 0){
                    
                        $zerocheck = $part_cd3->cd3_absolute - $mean_2;
                        $cd3abs_samples++;

                        if($zerocheck == 0 || $sd_2 == 0){
                            $sdi = 5;
                            
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
                    $cd3abs_grade = 0;
                }  
            }

            $total_samp = $cd3abs_samples + $cd4abs_samples + $cd3per_samples + $cd4per_samples;
            $total_accept_grade = $cd3abs_acceptable + $cd4abs_acceptable + $cd3per_acceptable + $cd4per_acceptable;

            if($total_samp == 0){
                $final_score = 0;
            }else{
                $final_score = (($total_accept_grade / $total_samp) * 100);
            }
            
            if($final_score >= 80){
                $passed++;
            }else{
                $failed++;
            }
            
            $pass_rate = (($passed / $no_of_participants) * 100);

            if($color == 7){
                $color = 0;
            }

            if (!(array_key_exists($participant->participant_id, $participating))) {

                $facility_participants[$count] = [
                    'label'         =>  $participant->participant_id,
                    // 'backgroundColor' => $backgroundColor[$color],
                    'borderColor' => $borderColor[$color],
                    'highlightFill' => $highlightFill[$color],
                    'highlightStroke' => $highlightStroke[$color],
                    'data' => []
                ];

                $participating[$participant->participant_id] = $count;
                
                   
            }

            // $facility_participants[$participant_no] = array(
            //     'data'  => array(round($pass_rate, 2))
            // );

            foreach ($facility_participants as $partkey => $partvalue) {
                                                        
                if($partvalue['label'] == $participant->participant_id){
                   
                    array_push($facility_participants[$partkey]['data'], round($pass_rate, 2));
                    
                }
            }

            

            $count++;
        }
  

        if($type == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($type == 'excel'){
            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Round_'.$round_name.'_', 'file_name' => 'Round_'.$round_name.'_', 'excel_topic' => 'Round_'.$round_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($type == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Round_'.$round_name.'_', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Round_'.$round_name.'_', 'pdf_topic' => 'Round_'.$round_name.'_');

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }











    public function generateReport($type,$round_uuid){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['uuid'=>$round_uuid])->row();
        $round_name = str_replace('/', '-', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;
        $round_id = $rounds->id;

        // $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        // $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $html_body = '
        <div class="centered">
            <div>
                <p> 
                    <img height="50px" width="50px" src="'. $this->config->item("server_url") . '"assets/frontend/images/files/gok.png";?>" alt="Ministry of Health" />
                </p>
            </div> 
            <div>
                <th>
                     MINISTRY OF HEALTH <br/>
                     NATIONAL PUBLIC HEALTH LABORATORY SERVICES <br/>
                     NATIONAL HIV REFERENCE LABORATORY <br/>
                     P. O. BOX 20750-00202, NAIROBI <br/>
                </th>
            </div><br/><br/>

            <div><th>
                Round No : ' .$round_name. ' <br/> 
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>Mfl Code</th>
            <th>Lab</th>';
            
        $heading = [
            "Mfl Code",
            "Lab"
        ];

        $column_data = array('Mfl Code','Lab');
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        array_push($heading, 'Final Score (%)', 'Grade');
        array_push($column_data, 'Final Score (%)', 'Grade');

        $html_body .= ' 
        <th>Grade (%)</th>
            <th>Review</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        // $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id])->result();
        $submissions = $this->Program_m->RespondedParticipantsData($round_id, $round_uuid);
        

        foreach ($submissions as $submission) {
            $sub_counter++;
            $cd3abs_acceptable = $cd3abs_unacceptable = $cd3abs_samples = 0;
            $cd3per_acceptable = $cd3per_unacceptable = $cd3per_samples = 0;
            $cd4abs_acceptable = $cd4abs_unacceptable = $cd4abs_samples = 0;
            $cd4per_acceptable = $cd4per_unacceptable = $cd4per_samples = 0;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            
            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facil = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row();

                if($facil){
                    $facility_name = $facil->facility_name;
                    $facility_code = $facil->facility_code;
                }else{
                    $facility_name = "No Facility";
                }

            }else{
                $facility_name = "No Facility";
            }


            array_push($tabledata, $facility_code, $facility_name);

            $html_body .= '<tr>
                            <td class="spacings">'.$facility_code.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';

            $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;

  
            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;


                $cd3_abs_values = $this->getEvaluationResults($round_id, $submission->equipment_id, $sample->id,'cd3','absolute');

                $mean_2 = ($cd3_abs_values->cd3_absolute_mean) ? $cd3_abs_values->cd3_absolute_mean : 0;
                $sd_2 = ($cd3_abs_values->cd3_absolute_sd) ? $cd3_abs_values->cd3_absolute_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;
                

                $part_cd3 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,97);

                
                if($part_cd3){
                    if($part_cd3->cd3_absolute != 0){
                        $html_body .= '<td class="spacings">'.$part_cd3->cd3_absolute.'</td>';
                        $zerocheck = $part_cd3->cd3_absolute - $mean_2;
                        $cd3abs_samples++;

                        // echo "<pre>";print_r($submissions);echo "</pre>";die();

                        if($zerocheck == 0 || $sd_2 == 0){
                            $sdi = 3;
                        }else{
                            $sdi = (($part_cd3->cd3_absolute - $mean_2) / $sd_2);
                        }

                        if($part_cd3->cd3_absolute == 0){
                            $cd3abs_acceptable++;
                        }
                        
                        if($sdi > -2 && 2 > $sdi){
                            $cd3abs_acceptable++;
                            $comment = "Acceptable";
                        }else{
                            $cd3abs_unacceptable++;
                            $comment = "Unacceptable";
                        }

                        // array_push($tabledata, $part_cd3->cd3_absolute, $comment);

                    }else{
                        $cd3abs_unacceptable++;
                        // array_push($tabledata, 0, "Unacceptable");
                    }
                        
                }else{
                    $cd3abs_unacceptable++;
                    // array_push($tabledata, 0, "Unacceptable");
                }
                $html_body .= '<td class="spacings">'.$comment.'</td>';      
            }

            
            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;


                $cd3_per_values = $this->getEvaluationResults($round_id, $submission->equipment_id, $sample->id,'cd3','percent');

                $mean_2 = ($cd3_per_values->cd3_percent_mean) ? $cd3_per_values->cd3_percent_mean : 0;
                $sd_2 = ($cd3_per_values->cd3_percent_sd) ? $cd3_per_values->cd3_percent_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;
                

                $part_cd3 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,$submission->participant_id);

                
                if($part_cd3){
                    if($part_cd3->cd3_percent != 0){
                        $html_body .= '<td class="spacings">'.$part_cd3->cd3_percent.'</td>';
                        $zerocheck = $part_cd3->cd3_percent - $mean_2;
                        $cd3per_samples++;

                        if($zerocheck == 0 || $sd_2 == 0){
                            $sdi = 3;
                        }else{
                            $sdi = (($part_cd3->cd3_percent - $mean_2) / $sd_2);
                        }

                        if($part_cd3->cd3_percent == 0){
                            $cd3per_acceptable++;
                        }
                        
                        if($sdi > -2 && 2 > $sdi){
                            $cd3per_acceptable++;
                            $comment = "Acceptable";
                        }else{
                            $cd3per_unacceptable++;
                            $comment = "Unacceptable";
                        }

                        // array_push($tabledata, $part_cd3->cd3_percent, $comment);

                    }else{
                        $cd3per_unacceptable++;
                        // array_push($tabledata, 0, "Unacceptable");
                    }
                        
                }else{
                    $cd3per_unacceptable++;
                    // array_push($tabledata, 0, "Unacceptable");
                }
                $html_body .= '<td class="spacings">'.$comment.'</td>';      
            }

                         
            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;


                $cd4_abs_values = $this->getEvaluationResults($round_id, $submission->equipment_id, $sample->id,'cd4','absolute');

                $mean_2 = ($cd4_abs_values->cd4_absolute_mean) ? $cd4_abs_values->cd4_absolute_mean : 0;
                $sd_2 = ($cd4_abs_values->cd4_absolute_sd) ? $cd4_abs_values->cd4_absolute_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;
                

                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,$submission->participant_id);

                
                if($part_cd4){
                    if($part_cd4->cd4_absolute != 0){
                        $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';
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
                            $comment = "Acceptable";
                        }else{
                            $cd4abs_unacceptable++;
                            $comment = "Unacceptable";
                        }

                        // array_push($tabledata, $part_cd4->cd4_absolute, $comment);

                    }else{
                        $cd4abs_unacceptable++;
                        // array_push($tabledata, 0, "Unacceptable");
                    }
                        
                }else{
                    $cd4abs_unacceptable++;
                    // array_push($tabledata, 0, "Unacceptable");
                }
                $html_body .= '<td class="spacings">'.$comment.'</td>';      
            }

            

            
            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;


                $cd4_per_values = $this->getEvaluationResults($round_id, $submission->equipment_id, $sample->id,'cd4','percent');

                $mean_2 = ($cd4_per_values->cd4_percent_mean) ? $cd4_per_values->cd4_percent_mean : 0;
                $sd_2 = ($cd4_per_values->cd4_percent_sd) ? $cd4_per_values->cd4_percent_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;
                

                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$submission->equipment_id,$sample->id,$submission->participant_id);

                
                if($part_cd4){
                    if($part_cd4->cd4_percent != 0){
                        $html_body .= '<td class="spacings">'.$part_cd4->cd4_percent.'</td>';
                        $zerocheck = $part_cd4->cd4_percent - $mean_2;
                        $cd4per_samples++;

                        if($zerocheck == 0 || $sd_2 == 0){
                            $sdi = 3;
                        }else{
                            $sdi = (($part_cd4->cd4_percent - $mean_2) / $sd_2);
                        }

                        if($part_cd4->cd4_percent == 0){
                            $cd4per_acceptable++;
                        }
                        
                        if($sdi > -2 && 2 > $sdi){
                            $cd4per_acceptable++;
                            $comment = "Acceptable";
                        }else{
                            $cd4per_unacceptable++;
                            $comment = "Unacceptable";
                        }

                        // array_push($tabledata, $part_cd4->cd4_percent, $comment);

                    }else{
                        $cd4per_unacceptable++;
                        // array_push($tabledata, 0, "Unacceptable");
                    }
                        
                }else{
                    $cd4per_unacceptable++;
                    // array_push($tabledata, 0, "Unacceptable");
                }
                $html_body .= '<td class="spacings">'.$comment.'</td>';      
            }


            $total_samp = $cd3abs_samples + $cd4abs_samples + $cd3per_samples + $cd4per_samples;
            $total_accept_grade = $cd3abs_acceptable + $cd4abs_acceptable + $cd3per_acceptable + $cd4per_acceptable;

            if($total_samp == 0){
                $final_score = 0;
            }else{
                $final_score = round((($total_accept_grade / $total_samp) * 100), 2);
            }


            if($final_score >= 80){
                $review = "PASS";
            }else{
                $review = "FAIL";
            }


            array_push($tabledata, $final_score, $review);

            switch ($type) {
                case 'table':
                    
                    $table[$count] = $tabledata;

                break;

                case 'excel':
                    array_push($row_data, $tabledata);
                break;

                case 'pdf':
                 
                    
                    $html_body .= '<td class="spacings">'.$overall_grade.' %</td>';
                    $html_body .= '<td class="spacings">'.$review.'</td>';
                    $html_body .= '<td class="spacings">'.$name.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->phone.'</td>';
                    $html_body .= '<td class="spacings">'.$part_details->email_address.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... Please contact the administrator");echo "</pre>";die();
                break;
            }

            $count++;
                      
        }
  

        if($type == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($type == 'excel'){
            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Round_'.$round_name.'_', 'file_name' => 'Round_'.$round_name.'_', 'excel_topic' => 'Round_'.$round_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($type == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Round_'.$round_name.'_', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Round_'.$round_name.'_', 'pdf_topic' => 'Round_'.$round_name.'_');

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }




?>