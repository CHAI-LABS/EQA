<?php

        public function createParticipantTable($form, $round_id, $equipment_id, $type, $type2){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

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
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility</th>
            <th>Batch</th>';
            
        $heading = [
            "No.",
            "Facility",
            "Batch"
        ];

        $column_data = array('No.','Facility','Batch');
        
        

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
            array_push($column_data, $sample->sample_name,"Comment");
            $html_body .= '<th>'.$sample->sample_name.'</th> <th>Comment</th>';
        }

        array_push($heading, 'Overall Grade', "Review Comment",'Participant','Cell','Email');
        array_push($column_data, 'Overall Grade', "Review Comment",'Participant','Cell','Email');

        $html_body .= ' 
        <th>Overall Grade</th>
            <th>Review Comment</th>
            <th>Participant</th>
            <th>Cell</th>
            <th>Email</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();

        $submissions = $this->Program_m->RespondedParticipantsData($round_id, $round_uuid, $equipment_id);

        foreach ($submissions as $submission) {
            $sub_counter++;
            $cd4abs_acceptable = $cd4abs_unacceptable = $cd4abs_samples = $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            
            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facil = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row();

                if($facil){
                    $facility_name = $facil->facility_name;
                }else{
                    $facility_name = "No Facility";
                }

            }else{
                $facility_name = "No Facility";
            }

            
            $batches = $this->db->get_where('pt_ready_participants', ['p_id' => $submission->participant_id, 'pt_round_uuid' => $round_uuid])->row();

            if($batches){
                $batch = $batches->batch;
            }else{
                $batch = '';
            }

            array_push($tabledata, $sub_counter, $facility_name, $batch);

            $html_body .= '<tr>
                            <td class="spacings">'.$sub_counter.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<td class="spacings">'.$batch.'</td>';

            $lower_limit_2 = $upper_limit_2 = $sd_2 = $mean_2 = $samp_counter = 0;
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

            // $grade = (($acceptable / $samp_counter) * 100);

            $overall_grade = round($cd4abs_grade, 2) . ' %';

            if($cd4abs_grade >= 80){
                $review = "Satisfactory Performance";
            }else if($cd4abs_grade > 0 && $cd4abs_grade < 80){
                $review = "Unsatisfactory Performance";
            }else{
                $review = "Non-responsive";
            }

            $part = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row();

            if($part){
                $username = $part->participant_id;
                $part_details = $this->db->get_where('users_v', ['username' =>  $username])->row();
                $name = $part_details->firstname . ' ' . $part_details->lastname;
                $phone = $part_details->phone;
                $email = $part_details->email_address;
            }else{
                $name = "No firstname and lastname";
                $phone = "No phone number";
                $email = "No email address";
            }

            array_push($tabledata, $overall_grade,$review,$name,$phone,$email);

            switch ($form) {
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


        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participants_'.$round_name.'_'.$equipment_name, 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'excel_topic' => 'Participants_'.$equipment_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_topic' => 'Participants_'.$round_name.'_'.$equipment_name);

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }





    public function createParticipantTable($form, $round_id, $equipment_id, $type, $type2){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

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
                Equipment Name : ' . $equipment_name . '
                </th>
            </div>
            <br/><br/>

        </div>
        <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Facility</th>
            <th>Batch</th>';
            
        $heading = [
            "No.",
            "Facility",
            "Batch"
        ];

        $column_data = array('No.','Facility','Batch');
        
        

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
            array_push($column_data, $sample->sample_name,"Comment");
            $html_body .= '<th>'.$sample->sample_name.'</th> <th>Comment</th>';
        }

        array_push($heading, 'Overall Grade', "Review Comment",'Participant','Cell','Email');
        array_push($column_data, 'Overall Grade', "Review Comment",'Participant','Cell','Email');

        $html_body .= ' 
        <th>Overall Grade</th>
            <th>Review Comment</th>
            <th>Participant</th>
            <th>Cell</th>
            <th>Email</th>
            </tr> 
        </thead>
        <tbody>
        <ol type="a">';


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();

        

        

        foreach ($submissions as $submission) {
            $sub_counter++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            
            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facil = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row();

                if($facil){
                    $facility_name = $facil->facility_name;
                }else{
                    $facility_name = "No Facility";
                }

            }else{
                $facility_name = "No Facility";
            }

            
            $batches = $this->db->get_where('pt_ready_participants', ['p_id' => $submission->participant_id, 'pt_round_uuid' => $round_uuid])->row();

            if($batches){
                $batch = $batches->batch;
            }else{
                $batch = '';
            }

            array_push($tabledata, $sub_counter, $facility_name, $batch);

            $html_body .= '<tr>
                            <td class="spacings">'.$sub_counter.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<td class="spacings">'.$batch.'</td>';

            foreach ($samples as $sample) {
                $comment = '';
                $samp_counter++;
                $accepted = $unaccepted = [];

                $calculated_values_2 = $this->getEvaluationResults($round_id, $equipment_id, $sample->id,$type,$type2);

                $mean_2 = ($calculated_values_2->cd4_absolute_mean) ? $calculated_values_2->cd4_absolute_mean : 0;
                $sd_2 = ($calculated_values_2->cd4_absolute_sd) ? $calculated_values_2->cd4_absolute_sd : 0;
                $sd2_2 = ($calculated_values_2->double_cd4_absolute_sd) ? $calculated_values_2->double_cd4_absolute_sd : 0;
                $upper_limit_2 = $mean_2 + $sd_2;
                $lower_limit_2 = $mean_2 - $sd_2;


                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$submission->participant_id);
                $sdi = '';
                if($part_cd4){

                    $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';

                    
                    $sdi = (($part_cd4->cd4_absolute - $mean_2) / $sd_2);
                    

                    // echo "<pre>";print_r($mean_2);echo "</pre>";

                    if($sdi > -2 && 2 > $sdi){
                        $acceptable++;
                        $comment = "Acceptable";

                    }else{
                        $unacceptable++;
                        $comment = "Unacceptable";
                    }   

                    if($part_cd4->cd4_absolute == 0 || $part_cd4->cd4_absolute == null){
                        $zerocount++;
                    }

                    array_push($tabledata, $part_cd4->cd4_absolute, $comment);
                    
                }else{
                    array_push($tabledata, 0, "Unacceptable");
                }   
                $html_body .= '<td class="spacings">'.$comment.'</td>'; 
            }

            $grade = (($acceptable / $samp_counter) * 100);

            $overall_grade = round($grade, 2) . ' %';

            if($grade >= 80){
                $review = "Satisfactory Performance";
            }else if($grade > 0 && $grade < 80){
                $review = "Unsatisfactory Performance";
            }else{
                $review = "Non-responsive";
            }

            $part = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row();

            if($part){
                $username = $part->participant_id;
                $part_details = $this->db->get_where('users_v', ['username' =>  $username])->row();
                $name = $part_details->firstname . ' ' . $part_details->lastname;
                $phone = $part_details->phone;
                $email = $part_details->email_address;
            }else{
                $name = "No firstname and lastname";
                $phone = "No phone number";
                $email = "No email address";
            }

            array_push($tabledata, $overall_grade,$review,$name,$phone,$email);

            switch ($form) {
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


        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($table);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => 'Participants_'.$round_name.'_'.$equipment_name, 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'excel_topic' => 'Participants_'.$equipment_name);

            
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => 'Participants_'.$round_name.'_'.$equipment_name, 'pdf_topic' => 'Participants_'.$round_name.'_'.$equipment_name);

            $this->export->create_pdf($html_body,$pdf_data);

        }
    }
?>