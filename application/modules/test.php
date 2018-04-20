<?php

$submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();


        foreach ($submissions as $submission) {
            $sub_counter++;
            $samp_counter = $acceptable = $unacceptable = 0;
            $tabledata = [];
 

            $facilityid = $this->db->get_where('participant_readiness_v', ['p_id' => $submission->participant_id])->row();

            if($facilityid){
                $facility_id = $facilityid->facility_id;

                $facility_name = $this->db->get_where('facility_v', ['facility_id' =>  $facility_id])->row()->facility_name;
            }else{
                $facility_name = "No Facility";
            }

            
            $batch = $this->db->get_where('pt_ready_participants', ['p_id' => $submission->participant_id, 'pt_round_uuid' => $round_uuid])->row();

            array_push($tabledata, $sub_counter, $facility_name, $batch->batch);

            $html_body .= '<tr><td class="spacings">'.$sub_counter.'</td>';
            $html_body .= '<td class="spacings">'.$facility_name.'</td>';
            $html_body .= '<td class="spacings">'.$batch->batch.'</td>';

            foreach ($samples as $sample) {
                $samp_counter++;
                
                // $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                $cd4_calculated_values = $this->Analysis_m->getRoundResults($round_id, $equipment_id, $sample->id);

                if($cd4_calculated_values){
                    $upper_limit = $cd4_calculated_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_calculated_values->cd4_absolute_lower_limit;
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

            $ready_part = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->participant_id])->row();

            $username = $ready_part->participant_id;
            $facility_code = $ready_part->facility_code;
            // echo "<pre>";print_r($part_cd4);echo "</pre>";die();
            $part_details = $this->db->get_where('users_v', ['username' =>  $username])->row();
            
            $name = $part_details->firstname . ' ' . $part_details->lastname;

            $capa = '<a href = ' . base_url("Analysis/newCAPAMessage/$round_uuid/$part_details->email_address/$facility_code") . ' class = "btn btn-warning btn-sm"><i class = "icon-envelope"></i>&nbsp;Send Capa </a>';

            array_push($tabledata, $overall_grade,$review,$name,$part_details->phone,$part_details->email_address, $capa);
    
            switch ($form) {
                case 'table':
                    if($review == "Unsatisfactory Performance"){
                        $table[$count] = $tabledata;
                    }

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

    


?>