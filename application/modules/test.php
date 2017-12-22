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

                    if($part_cd4->cd4_absolute == 0){
                        $novalue++;
                    }
                }      
            }

            if($novalue == $sampcount){
                $no_non_responsive++;
            }
        }