<?php


public function createAbsolutePeerTable($form, $round_id, $equipment_id, $type){
        $template = $this->config->item('default');

        $column_data = $row_data = array();

        $mean = $sd = $sd2 = $upper_limit = $lower_limit = $counter = 0;

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        // echo "<pre>";print_r($equipment_name);echo "</pre>";die();


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
            <th>Sample</th>
            <th>Mean</th>
            <th>SD</th>
            <th>Double SD</th>
            <th>Upper Limit</th>
            <th>Lower Limit</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

    
        $heading = [
            "Sample",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit",
            "Actions"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            // $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            $calculated_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd3_absolute_mean) ? $calculated_values->cd3_absolute_mean : 0;
                        $sd = ($calculated_values->cd3_absolute_sd) ? $calculated_values->cd3_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_cd3_absolute_sd) ? $calculated_values->double_cd3_absolute_sd : 0;
                        $upper_limit = ($calculated_values->cd3_absolute_upper_limit) ? $mean + $sd : 0;
                        $lower_limit = ($calculated_values->cd3_absolute_lower_limit) ? $mean - $sd : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd4_absolute_mean) ? $calculated_values->cd4_absolute_mean : 0;
                        $sd = ($calculated_values->cd4_absolute_sd) ? $calculated_values->cd4_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_cd4_absolute_sd) ? $calculated_values->double_cd4_absolute_sd : 0;
                        $upper_limit = ($calculated_values->cd4_absolute_upper_limit) ? $mean + $sd : 0;
                        $lower_limit = ($calculated_values->cd4_absolute_lower_limit) ? $mean - $sd : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'other':
                    // echo "<pre>";print_r($calculated_values->other_absolute_mean);echo "</pre>";die();

                    if($calculated_values){
                        $mean = ($calculated_values->other_absolute_mean) ? $calculated_values->other_absolute_mean : 0;
                        $sd = ($calculated_values->other_absolute_sd) ? $calculated_values->other_absolute_sd : 0;
                        $sd2 = ($calculated_values->double_other_absolute_sd) ? $calculated_values->double_other_absolute_sd : 0;
                        $upper_limit = ($calculated_values->other_absolute_upper_limit) ? $mean + $sd : 0;
                        $lower_limit = ($calculated_values->other_absolute_lower_limit) ? $mean - $sd : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }

                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/absolute')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

                    $tabledata[] = [
                                $sample->sample_name,
                                $mean,
                                $sd,
                                $sd2,
                                $upper_limit,
                                $lower_limit,
                                "<div class = 'dropdown'>
                                    <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                        Act
                                    </button>
                                    <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                        $view
                                    </div>
                                </div>"
                            ];
                break;

                case 'excel':
                    array_push($row_data, array($counter, $sample->sample_name, $mean, $sd, $sd2,$upper_limit, $lower_limit));
                break;

                case 'pdf':
                    $html_body .= '<tr>';
                    $html_body .= '<td class="spacings">'.$counter.'</td>';
                    $html_body .= '<td class="spacings">'.$sample->sample_name.'</td>';
                    $html_body .= '<td class="spacings">'.$mean.'</td>';
                    $html_body .= '<td class="spacings">'.$sd.'</td>';
                    $html_body .= '<td class="spacings">'.$sd2.'</td>';
                    $html_body .= '<td class="spacings">'.$upper_limit.'</td>';
                    $html_body .= '<td class="spacings">'.$lower_limit.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $equipment_name.'_'.$type.'_absolute');
            // $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'excel_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

            $column_data = array('No.','Sample','Mean','SD','Double SD','Upper Limit','Lower Limit');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute', 'pdf_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_absolute');

            $this->export->create_pdf($html_body,$pdf_data);

        }              
    }



    public function createPercentPeerTable($form, $round_id, $equipment_id, $type){
        $template = $this->config->item('default');

        $column_data = $row_data = array();

        $mean = $sd = $sd2 = $upper_limit = $lower_limit = $counter = 0;

        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

        // echo "<pre>";print_r($equipment_name);echo "</pre>";die();


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
            <th>Sample</th>
            <th>Mean</th>
            <th>SD</th>
            <th>Double SD</th>
            <th>Upper Limit</th>
            <th>Lower Limit</th>
        </tr> 
        </thead>
        <tbody>
        <ol type="a">';

    
        $heading = [
            "Sample",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit",
            "Actions"
        ];
        $tabledata = [];

        foreach($samples as $sample){
            $counter++;
            $table_body = [];
            $table_body[] = $sample->sample_name;

            // $calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

            $calculated_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

            switch ($type) {
                case 'cd3':

                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd3_percent_mean) ? $calculated_values->cd3_percent_mean : 0;
                        $sd = ($calculated_values->cd3_percent_sd) ? $calculated_values->cd3_percent_sd : 0;
                        $sd2 = ($calculated_values->double_cd3_percent_sd) ? $calculated_values->double_cd3_percent_sd : 0;
                        $upper_limit = ($calculated_values->cd3_percent_upper_limit) ? $calculated_values->cd3_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd3_percent_lower_limit) ? $calculated_values->cd3_percent_lower_limit : 0;

                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'cd4':
                    // echo "<pre>";print_r($calculated_values);echo "</pre>";die();
                    if($calculated_values){
                        $mean = ($calculated_values->cd4_percent_mean) ? $calculated_values->cd4_percent_mean : 0;
                        $sd = ($calculated_values->cd4_percent_sd) ? $calculated_values->cd4_percent_sd : 0;
                        $sd2 = ($calculated_values->double_cd4_percent_sd) ? $calculated_values->double_cd4_percent_sd : 0;
                        $upper_limit = ($calculated_values->cd4_percent_upper_limit) ? $calculated_values->cd4_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->cd4_percent_lower_limit) ? $calculated_values->cd4_percent_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;

                case 'other':
                    if($calculated_values){
                        $mean = ($calculated_values->other_percent_mean) ? $calculated_values->other_percent_mean : 0;
                        $sd = ($calculated_values->other_percent_sd) ? $calculated_values->other_percent_sd : 0;
                        $sd2 = ($calculated_values->double_other_percent_sd) ? $calculated_values->double_other_percent_sd : 0;
                        $upper_limit = ($calculated_values->other_percent_upper_limit) ? $calculated_values->other_percent_upper_limit : 0;
                        $lower_limit = ($calculated_values->other_percent_lower_limit) ? $calculated_values->other_percent_lower_limit : 0;
                    }else{
                        $mean = 0;
                        $sd = 0;
                        $sd2 = 0;
                        $upper_limit = 0;
                        $lower_limit = 0;
                    }
                    
                break;
                
                default:
                    echo "<pre>";print_r("Something went wrong");echo "</pre>";die();
                break;
            }

            switch ($form) {
                case 'table':

                $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id . '/'.$type.'/percent')."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

                    $tabledata[] = [
                                $sample->sample_name,
                                $mean,
                                $sd,
                                $sd2,
                                $upper_limit,
                                $lower_limit,
                                "<div class = 'dropdown'>
                                    <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                        Act
                                    </button>
                                    <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                        $view
                                    </div>
                                </div>"
                            ];
                break;

                case 'excel':
                    array_push($row_data, array($counter, $sample->sample_name, $mean, $sd, $sd2,$upper_limit, $lower_limit));
                break;

                case 'pdf':
                    $html_body .= '<tr>';
                    $html_body .= '<td class="spacings">'.$counter.'</td>';
                    $html_body .= '<td class="spacings">'.$sample->sample_name.'</td>';
                    $html_body .= '<td class="spacings">'.$mean.'</td>';
                    $html_body .= '<td class="spacings">'.$sd.'</td>';
                    $html_body .= '<td class="spacings">'.$sd2.'</td>';
                    $html_body .= '<td class="spacings">'.$upper_limit.'</td>';
                    $html_body .= '<td class="spacings">'.$lower_limit.'</td>';
                    $html_body .= "</tr></ol>";
                break;
                    
                
                default:
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
                break;
            }
        }

        if($form == 'table'){

            $this->table->set_template($template);
            $this->table->set_heading($heading);

            return $this->table->generate($tabledata);

        }else if($form == 'excel'){

            $excel_data = array();
            $excel_data = array('doc_creator' => 'External_Quality_Assurance', 'doc_title' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'excel_topic' => $equipment_name.'_'.$type.'_percent');

            $column_data = array('No.','Sample','Mean','SD','Double SD','Upper Limit','Lower Limit');
            $excel_data['column_data'] = $column_data;
            $excel_data['row_data'] = $row_data;

            $this->export->create_excel($excel_data);

        }else if($form == 'pdf'){

            $html_body .= '</tbody></table>';
            $pdf_data = array("pdf_title" => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'pdf_html_body' => $html_body, 'pdf_view_option' => 'download', 'file_name' => $round_name.'_'.$equipment_name.'_'.$type.'_percent', 'pdf_topic' => $round_name.'_'.$equipment_name.'_'.$type.'_percent');

            $this->export->create_pdf($html_body,$pdf_data);

        }              
    }







    public function createParticipantTable($form, $round_id, $equipment_id){
        $template = $this->config->item('default');
        $column_data = $row_data = $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $sub_counter = 0;

        $rounds = $this->db->get_where('pt_round_v', ['id'=>$round_id])->row();
        $round_name = str_replace(' ', '_', $rounds->pt_round_no);
        $round_uuid = $rounds->uuid;

        $equipments = $this->db->get_where('equipment', ['id'=>$equipment_id,'equipment_status'=>1])->row();
        $equipment_name = str_replace(' ', '_', $equipments->equipment_name);

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
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

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

        // echo "<pre>";print_r(count($submissions));echo "</pre>";die();

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

                // echo "<pre>";print_r($facility_name);echo "</pre>";die();
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
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                // echo "<pre>";print_r($cd4_values);echo "</pre>";die();

                
                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 

                
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$submission->participant_id);

               
                if($part_cd4){

                    $html_body .= '<td class="spacings">'.$part_cd4->cd4_absolute.'</td>';

                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
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

            if($grade == 100){
                $review = "Satisfactory Performance";
            }else if($grade > 0 && $grade < 100){
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
                    echo "<pre>";print_r("Something went wrong... PLease contact the administrator");echo "</pre>";die();
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