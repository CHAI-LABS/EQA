<?php

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
        $submissions = $this->Program_m->RespondedParticipants($round_id, $round_uuid, $county_id, $facility_id);

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

            $ready_part = $this->db->get_where('pt_ready_participants', ['p_id' =>  $submission->p_id])->row();

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
?>