<?php

public function PassFailGraph($facility_id){
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

        $pass = [
            'label'         =>  'Pass',
            'backgroundColor' => 'rgba(46,204,113,0.5)',
            'borderColor' => 'rgba(46,204,113,0.8)',
            'highlightFill' => 'rgba(46,204,113,0.75)',
            'highlightStroke' => 'rgba(46,204,113,1)'
        ];

        $fail = [
            'label'         =>  'Fail',
            'backgroundColor' => 'rgba(211,84,0,0.5)',
            'borderColor' => 'rgba(211,84,0,0.8)',
            'highlightFill' => 'rgba(211,84,0,0.75)',
            'highlightStroke' => 'rgba(211,84,0,1)'
        ];


        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $partcount = $counter = 0;

                $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();
                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }
                
                $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);
                $equipments = $this->Program_m->Equipments();

                // echo "<pre>";print_r($participants);die;

                if($participants){
                
	                foreach ($participants as $participant) {
                        $partcount++;
                        $novalue = $sampcount = $acceptable = $unacceptable = $novalue = $sampcount = 0;

                        foreach ($samples as $sample) {
                            $sampcount++;

                            $cd4_values = $this->Program_m->getRoundResults($round_id, $participant->equipment_id, $sample->id);

                            if($cd4_values){

                                $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                                $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                            }else{
                                $upper_limit = 0;
                                $lower_limit = 0;
                            } 

                            $part_cd4 = $this->Program_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->participant_id);

                            if($part_cd4){
			                    if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
			                        $acceptable++;
			                    }    
			                } 
                        } 

                        if($acceptable == $sampcount) {
                            $passed++;
                        }
	                    
	                }

	                $failed = $partcount - $passed;

	            }else{
	            	$passed = 0;
	            	$failed = 0;
	            }
                

                $labels[] = $round->pt_round_no;
  
                $pass['data'][] = $passed;
                $fail['data'][] = $failed;
                
            }
        }

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$pass, $fail];

        

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }
    


    public function PassFailRateGraph($facility_id){
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
            'label'         =>  'Pass Rate (%)',
            'borderColor' => 'rgba(52,152,219,0.8)',
            'highlightFill' => 'rgba(52,152,219,0.75)',
            'highlightStroke' => 'rgba(52,152,219,1)',
            'yAxisID' => 'y-axis-1',
            'type' => 'line'
        ];

        if($rounds){
            foreach ($rounds as $round) {
                $data = [];
                $partcount = $counter = 0;



                $round_id = $this->db->get_where('pt_round', ['uuid' => $round->uuid])->row()->id;
                $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

                if($facility_id){
                    $county_id = $this->db->get_where('facility_v', ['facility_id' => $facility_id])->row()->county_id;
                }

                $participants = $this->Program_m->getReadyParticipants($round_id, $county_id, $facility_id);

                
	            // echo "<pre>";print_r($no_of_participants);die;
		        if($participants){

		            foreach ($participants as $participant) {
		                    $partcount ++;
		                    $novalue = $sampcount = $acceptable = $unacceptable = $novalue = $sampcount = 0;


		                    foreach ($samples as $sample) {
		                        $sampcount++;

		                        $cd4_values = $this->Program_m->getRoundResults($round_id, $participant->equipment_id, $sample->id);

		                        if($cd4_values){

		                            $upper_limit = $cd4_values->cd4_absolute_upper_limit;
		                            $lower_limit = $cd4_values->cd4_absolute_lower_limit;
		                        }else{
		                            $upper_limit = 0;
		                            $lower_limit = 0;
		                        } 

		                        $part_cd4 = $this->Program_m->absoluteValue($round_id,$participant->equipment_id,$sample->id,$participant->participant_id);

		                        if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
	                                $acceptable++;    
	                            }
		                    } 

		                    if($acceptable == $sampcount) {
		                        $passed++;
		                    }
		            }

		            // $no_of_participants = $this->Program_m->ParticipatingParticipants($round->uuid, $county_id, $facility_id)->participants;

		            $pass_rate = round((($passed / $partcount) * 100), 2);

		        }else{
		        	$pass_rate = 0;
		        }


                $labels[] = $round->pt_round_no;

                $no_participants['data'][] = $pass_rate;
   
            }
        }

        // $no_participants['yAxisID'] = 'y-axis-2';

        $graph_data['labels'] = $labels;
        $graph_data['datasets'] = [$no_participants];

        // echo "<pre>";print_r($graph_data);die;

        return $this->output->set_content_type('application/json')->set_output(json_encode($graph_data));
    }



    













?>