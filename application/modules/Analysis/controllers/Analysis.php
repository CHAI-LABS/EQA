<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis extends DashboardController {

	public function __construct(){
		parent::__construct();

		$this->load->library('table');
        $this->load->config('table');
		$this->load->model('Analysis_m');

		

	}
	
	public function index()
	{	
		$data = [];
        $title = "Analysis";
        // $pt_count = $this->db->count_all('pt_rounds');

            $data = [
                'table_view'    =>  $this->createPTTable()
            ];

        

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/analysis_v', $data)
                ->adminTemplate();
	}


	

	public function createPTTable()
	{
		$template = $this->config->item('default');

        $heading = [
            "No.",
            "PT Round No.",
            "From",
            "To",
            "Tag",
            "Lab Unit",
            "Status",
            "Actions"
        ];
        $tabledata = [];

        
        $rounds = $this->db->get('pt_round_v')->result();
        // echo "<pre>";print_r($rounds);echo "</pre>";die();

        if($rounds){
            $counter = 0;
            foreach($rounds as $round){
                $counter ++;
                $round_uuid = $round->uuid;

                if($round->type == "ongoing"){
                    $status = "<label class = 'tag tag-warning tag-sm'>Ongoing</label>"; 
                }else{
                    $status = "<label class = 'tag tag-success tag-sm'>Done</label>";
                }
                
                $tabledata[] = [
                    $counter,
                    $round->pt_round_no,
                    $round->from,
                    $round->to,
                    $round->tag,
                    $round->lab_unit,
                    $status,
                    '<a href = ' . base_url("Analysis/Results/$round_uuid") . ' class = "btn btn-primary btn-sm"><i class = "icon-eye"></i>&nbsp;View </a>
                    '
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
	}


	public function createParticipantResultsTable($round_id,$equipment_id,$sample_id)
	{
		$template = $this->config->item('default');

        $heading = [
            "No.",
            "Facility",
            "CD4 Absolute Result"
        ];
		$tabledata = [];

        
        $part_results = $this->db->get_where('pt_participant_review_v',['round_id'=> $round_id, 'equipment_id' => $equipment_id, 'sample_id' => $sample_id])->result();
         //echo "<pre>";print_r($part_results);echo "</pre>";die();

        if($part_results){
            $counter = 0;
            foreach($part_results as $part_result){
                $counter ++;

                // $participant_id = $this->db->get_where('participant_readiness_v', ['p_id' => $part_result->participant_id])->row()->username;

                // if($round->type == "ongoing"){
                //     $status = "<label class = 'tag tag-warning tag-sm'>Ongoing</label>"; 
                // }else{
                //     $status = "<label class = 'tag tag-success tag-sm'>Done</label>";
                // }
                
                $tabledata[] = [
                    $counter,
                    0,
                    $part_result->cd4_absolute
                ];
            }
        }
        $this->table->set_heading($heading);
        $this->table->set_template($template);

        return $this->table->generate($tabledata);
	}


	public function Results($round_uuid){
		$data = [];
        $title = "Analysis";

        $where_array = [
                            'uuid'   => $round_uuid
                        ];


        $pt_id = $this->db->get_where('pt_round', $where_array)->row()->id;
		// $equipments = $this->db->get_where('equipment', ['equipment_status'=>1])->result();
		//echo "<pre>";print_r($equipments);echo "</pre>";die();

		
			$data = [
				'sample_tabs' => $this->createTabs($pt_id)
			];
            

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                        ->addJs('dashboard/js/libs/moment.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/nhrl_peer_results_v', $data)
                ->adminTemplate();
	}


	public function createNHRLTable($round_id, $equipment_id){
		$template = $this->config->item('default');

		$where = ['pt_round_id' =>  $round_id];
        $samples = $this->db->get_where('pt_samples', $where)->result();
        $testers = $this->db->get_where('pt_testers', $where)->result();
        $labs = $this->db->get_where('pt_labs', $where)->result();
        $equipments = $this->db->get_where('equipment', ['equipment_status'=>1])->result();

		$where_array = [
                            'pt_round_id'   => $round_id,
                            'equipment_id'  => $equipment_id
                        ];

        $heading = [
            "Sample ID",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit"
        ];
        $tabledata = [];

// echo "<pre>";print_r($nhrl_results);echo "</pre>";die();
        foreach($samples as $sample){
                    $table_body = [];
                    $table_body[] = $sample->sample_name;
                    

                    $calculated_values = $this->db->get_where('pt_testers_calculated_v', ['pt_round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'pt_sample_id'  =>  $sample->id])->row(); 

                    $tabledata[] = [
	                    $sample->sample_name,
	                    ($calculated_values) ? $calculated_values->mean : 0,
	                    ($calculated_values) ? $calculated_values->sd : 0,
	                    ($calculated_values) ? $calculated_values->doublesd : 0,
	                    ($calculated_values) ? $calculated_values->upper_limit : 0,
	                    ($calculated_values) ? $calculated_values->lower_limit : 0
                	];

                }

                // echo "<pre>";print_r($tabledata);echo "</pre>";die();

                $this->table->set_template($template);
                $this->table->set_heading($heading);

        return $this->table->generate($tabledata);
	}

	public function ParticipantInfo($round_id,$equipment_id,$sample_id)
	{

		$round_id_name = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->pt_round_no;
		$equipment_name = $this->db->get_where('equipment', ['id' =>  $equipment_id])->row()->equipment_name;
		$sample_name = $this->db->get_where('pt_samples', ['id' =>  $sample_id])->row()->sample_name;

		

		$cd4_calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample_id])->row();

		$mean = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_mean : 0;
        $sd = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_sd : 0;
        $sd2 = ($cd4_calculated_values) ? $cd4_calculated_values->double_cd4_absolute_sd : 0;
        $upper = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_upper_limit : 0;
        $lower = ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_lower_limit : 0;


		// echo "<pre>";print_r($round_id_name);echo "</pre>";die();
		$part_info = '';

		$part_info .= '<div class = "row">
								    <div class="col-md-6">
								        <div class = "card card-outline-danger">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    General Details

								            </div>

								            <div class = "card-block">
								            Round ID : <strong>';

									        $part_info .= $round_id_name;

									        $part_info .= '</strong> <br/> Equipment : <strong>';

									        $part_info .= $equipment_name;

									        $part_info .= '</strong> <br/> Sample : <strong>';

									        $part_info .= $sample_name;

        $part_info .= '	</strong> <br/></div>
				       </div>
				    </div>

				    <div class="col-md-6">
								        <div class = "card card-outline-info">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    Participants Information

								            </div>
								            <div class = "card-block">
									            <div class="col-md-6">
									            Mean : <strong>';

									            $part_info .= $mean;

										        $part_info .= '</strong> <br/> SD : <strong>';

										        $part_info .= $sd;

										        $part_info .= '</strong> <br/> 2SD : <strong>';

										        $part_info .= $sd2;

		$part_info .= '	</strong> <br/>
						</div>
						<div class="col-md-6">
							Upper Limit: <strong>';

							$part_info .= $upper;

					        $part_info .= '</strong> <br/> Lower Limit : <strong>';

					        $part_info .= $lower;

							$part_info .= '	</strong> <br/> 
						</div>
				       </div>
				    </div>
				  </div>
				  </div>';
  		
        return $part_info;
	}

	public function ParticipantResults($round_id,$equipment_id,$sample_id)
	{	
		$data = [];
        $title = "Participant Results";

        $round_uuid = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->uuid;

            $data = [
            	'round_uuid' => $round_uuid,
            	'participants_info' => $this->ParticipantInfo($round_id,$equipment_id,$sample_id),
                'results_table'    =>  $this->createParticipantResultsTable($round_id,$equipment_id,$sample_id)
            ];

        $this->assets
                ->addJs("dashboard/js/libs/jquery.dataTables.min.js")
                ->addJs("dashboard/js/libs/dataTables.bootstrap4.min.js")
                ->addJs('dashboard/js/libs/jquery.validate.js')
                ->addJs('dashboard/js/libs/select2.min.js');
        $this->assets->setJavascript('Analysis/analysis_js');
        $this->template
                ->setPageTitle($title)
                ->setPartial('Analysis/participant_analysis_v', $data)
                ->adminTemplate();
	}

	public function createPeerTable($round_id, $equipment_id){
		$template = $this->config->item('default');

		$where = ['pt_round_id' =>  $round_id];
        $samples = $this->db->get_where('pt_samples', $where)->result();
        $equipments = $this->db->get_where('equipment', ['equipment_status'=>1])->result();

		$where_array = [
                            'round_id'   => $round_id,
                            'equipment_id'  => $equipment_id
                        ];

	
        $heading = [
            "Sample ID",
            "Mean",
            "SD",
            "2SD",
            "Upper Limit",
            "Lower Limit",
            "Actions"
        ];
        $tabledata = [];

        


        foreach($samples as $sample){
                    $table_body = [];
                    $table_body[] = $sample->sample_name;
                    
                    $view = "<a class = 'btn btn-success btn-sm dropdown-item' href = '".base_url('Analysis/ParticipantResults/' . $round_id . '/' . $equipment_id . '/' . $sample->id)."'><i class = 'fa fa-eye'></i>&nbsp;View Log</a>";

                    $cd4_calculated_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row(); 

                    // echo "<pre>";print_r($cd4_calculated_values);echo "</pre>";die();

                    $tabledata[] = [
	                    $sample->sample_name,
	                    ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_mean : 0,
	                    ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_sd : 0,
	                    ($cd4_calculated_values) ? $cd4_calculated_values->double_cd4_absolute_sd : 0,
	                    ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_upper_limit : 0,
	                    ($cd4_calculated_values) ? $cd4_calculated_values->cd4_absolute_lower_limit : 0,
	                    "<div class = 'dropdown'>
                            <button class = 'btn btn-secondary dropdown-toggle' type = 'button' id = 'dropdownMenuButton1' data-toggle = 'dropdown' aria-haspopup='true' aria-expanded = 'true'>
                                Act
                            </button>
                            <div class = 'dropdown-menu' aria-labelledby= = 'dropdownMenuButton'>
                                $view
                            </div>
                        </div>"
                	];

                }

                $this->table->set_template($template);
                $this->table->set_heading($heading);

        return $this->table->generate($tabledata);
	}

    public function createParticipantTable($round_id, $equipment_id){
        $template = $this->config->item('default');
        $tablevalues = $tablebody = $table = [];
        $count = $zerocount = $acceptable = $unacceptable = $sub_counter = $samp_counter = 0;

        $heading = [
            "No.",
            "Facility",
            "Batch No"
        ];
        
        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

        foreach ($samples as $sample) {
            array_push($heading, $sample->sample_name,"Comment");
        }

        array_push($heading, 'Overall Grade', "Review Comment",'Participant','Cell','Email');


        $submissions = $this->db->get_where('pt_data_submission', ['round_id' =>  $round_id, 'equipment_id' => $equipment_id])->result();

        

        foreach ($submissions as $submission) {
            $sub_counter++;

            $tabledata = [];

            array_push($tabledata, $sub_counter, 0, 0);

            foreach ($samples as $sample) {
                $samp_counter++;
                
                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

                


                if($cd4_values){
                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                } 

                // echo "<pre>";print_r($submission->participant_id);echo "</pre>";die();
                $part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$submission->participant_id);



               // echo "<pre>";print_r($part_cd4);echo "</pre>";
               
                if($part_cd4){

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
            }

            

            $grade = (($acceptable / $samp_counter) * 100);

            $overall_grade = $grade . ' %';

            if($grade == 100){
                $review = "Satisfactory Performance";
            }else if($zerocount == $samp_counter){
                $review = "Non-responsive";
            }else{
                $review = "Incomplete Submission";
            }

            $part_details = $this->db->get_where('users_v', ['username' =>  $submission->participant_id])->row();
            // echo "<pre>";print_r($part_details->firstname);echo "</pre>";die();
            

            array_push($tabledata, $overall_grade,$review,$part_details->firstname,$part_details->phone,$part_details->email_address);

            $table[$count] = $tabledata;
           
            $count++;
                      
        }

        // echo "<pre>";print_r("end");echo "</pre>";die();
     

        $this->table->set_template($template);
        $this->table->set_heading($heading);

        return $this->table->generate($table);
    }

	// public function createParticipantTable($round_id, $equipment_id){
	// 	$template = $this->config->item('default');

 //        $heading = [
 //            "No.",
 //            "Facility",
 //            "Batch No"
 //        ];
 //        $tabledata = $tablebody = $table = [];

 //        $samples = $this->db->get_where('pt_samples', ['pt_round_id' =>  $round_id])->result();

            // foreach ($samples as $sample) {
            //     array_push($heading, $sample->sample_name,"Comment");
            // }

 //        $participants = $this->db->get_where('participant_readiness_v', ['status' =>  1, 'approved' => 1, 'user_type' => 'participant'])->result();

 //        $round_uuid = $this->db->get_where('pt_round', ['id' =>  $round_id])->row()->uuid;


 //        $zerocount = $acceptable = $unacceptable = $part_counter = $samp_counter = 0;

 //        // echo "<pre>";print_r($participants);echo "</pre>";die();

 //        foreach ($participants as $key => $participant) {
	// 	$part_counter++;

        

 //       	$parti = $this->db->get_where('pt_ready_participants', ['p_id' => $participant->p_id, 'pt_round_uuid' => $round_uuid, 'lab_result' => 1])->row();

		

 //        	foreach ($samples as $sample) {
 //        		$samp_counter++;
			
 //                $cd4_values = $this->db->get_where('pt_participants_calculated_v', ['round_id' =>  $round_id, 'equipment_id'   =>  $equipment_id, 'sample_id'  =>  $sample->id])->row();

 //                if($cd4_values){
 //                    $upper_limit = $cd4_values->cd4_absolute_upper_limit;
 //                    $lower_limit = $cd4_values->cd4_absolute_lower_limit;
 //                }else{
 //                    $upper_limit = 0;
 //                    $lower_limit = 0;
 //                } 

 //        		$part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->p_id);
		 		
 //        		if($part_cd4){
 //        			if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
	// 				 	$acceptable++;
	// 				 	$comment = "Acceptable";
	//         		}else{
	//         			$unacceptable++;
	//         			$comment = "Unacceptable";
	//         		}   

	//         		if($part_cd4->cd4_absolute == 0 || $part_cd4->cd4_absolute == null){
	//         			$zerocount++;
	//         		}

	//         		array_push($tablebody, $part_cd4->cd4_absolute, $comment);
					
 //        		}else{


 //        			array_push($tablebody, 0, "Unacceptable");
 //        		}				
 //        	}

 //        	$grade = (($acceptable / $samp_counter) * 100);

 //        	$overall_grade = $grade . ' %';

 //        	if($grade == 100){
 //        		$review = "Satisfactory Performance";
 //        	}else if($zerocount == $samp_counter){
 //        		$review = "Non-responsive";
 //        	}else{
 //        		$review = "Incomplete Submission";
 //        	}

 //        	array_push($tabledata, $part_counter,$parti->facility_name,$parti->batch);

 //        	foreach ($tablebody as $key => $value) {
        		
 //        		array_push($tabledata, $value);
 //        	}

 //        	array_push($tabledata, $overall_grade,$review);

 //        	array_push($table, $tabledata);
 //        }

 //        array_push($heading, 'Overall Grade', "Review Comment");

        

 //        $this->table->set_template($template);
 //        $this->table->set_heading($heading);

 //        return $this->table->generate($table);
	// }



	public function createTabs($round_id){
        
        $datas=[];

        $samp_counter = $acceptable = $counter = $tab = 0;
        
        $where = ['pt_round_id' =>  $round_id];
        $samples = $this->db->get_where('pt_samples', $where)->result();

        
        $equipments = $this->Analysis_m->Equipments();
        // echo "<pre>";print_r($equipments);echo "</pre>";die();
        
        $equipment_tabs = '';

        $equipment_tabs .= "<ul class='nav nav-tabs' role='tablist'>";

        foreach ($equipments as $key => $equipment) {
            $tab++;

            $equipment_tabs .= "<li class='nav-item'>";
            if($tab == 1){
                $equipment_tabs .= "<a class='nav-link active' data-toggle='tab'";
            }else{
                $equipment_tabs .= "<a class='nav-link' data-toggle='tab'";
            }

            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);
            
            $equipment_tabs .= " href='#".$equipmentname."' role='tab' aria-controls='home'><i class='icon-calculator'></i>&nbsp;";
            $equipment_tabs .= $equipment->equipment_name;
            $equipment_tabs .= "&nbsp;";
            // $equipment_tabs .= "<span class='tag tag-success'>Complete</span>";
            $equipment_tabs .= "</a>
                                </li>";
        }

        $equipment_tabs .= "</ul>

                            <div class='tab-content'>";

        


        foreach ($equipments as $key => $equipment) {
            $counter++;
            $passed = $failed = 0;
            $equipment_id = $equipment->id;
            $equipmentname = $equipment->equipment_name;
            $equipmentname = str_replace(' ', '_', $equipmentname);

            if($counter == 1){
            	
                $equipment_tabs .= "<div class='tab-pane active' id='". $equipmentname ."' role='tabpanel'>";
            }else{

                $equipment_tabs .= "<div class='tab-pane' id='". $equipmentname ."' role='tabpanel'>";
            }

            $round_uuid = $this->db->get_where('pt_round', ['id' => $round_id])->row()->uuid;

            $submissions = $this->Analysis_m->getSubmissionsNumber($round_id, $equipment_id);
            $registrations = $this->Analysis_m->getRegistrationsNumber($round_uuid, $equipment_id);
            $participants = $this->Analysis_m->getReadyParticipants($round_id, $equipment_id);


	        foreach ($samples as $sample) {
	    		$samp_counter++;


	    		$nhrl_values = $this->db->get_where('pt_testers_calculated_v', ['pt_round_id' => $round_id, 'equipment_id' => $equipment_id, 'pt_sample_id' => $sample->id])->row(); 
                // echo "<pre>";print_r($nhrl_values);echo "</pre>";die();

                if($nhrl_values){
                    $upper_limit = $nhrl_values->upper_limit;
                    $lower_limit = $nhrl_values->lower_limit;
                }else{
                    $upper_limit = 0;
                    $lower_limit = 0;
                }
	    		

	    		foreach ($participants as $participant) {
	    			$part_cd4 = $this->Analysis_m->absoluteValue($round_id,$equipment_id,$sample->id,$participant->p_id);

		    		if($part_cd4){
		    			if($part_cd4->cd4_absolute >= $lower_limit && $part_cd4->cd4_absolute <= $upper_limit){
						 	$acceptable++;
		        		}  
		    		}	
	    		}	
	        }

	    	$grade = (($acceptable / $samp_counter) * 100);

	    	if($grade == 100){
	    		$passed++;
	    	}else{
	    		$failed++;
	    	}

            // echo "<pre>";print_r($registrations);echo "</pre>";die();

            $equipment_tabs .= '<div class = "row">
								    <div class="col-md-12">
								        <div class = "card card-outline-info">
								            <div class="card-header col-4">
								                <i class = "icon-chart"></i>
								                &nbsp;

								                    Equipment Info

								            </div>

								            <div class = "card-block">
								            No. of Registrations : <strong>';

            if($registrations){
            	$equipment_tabs .= $registrations->register_count;
            }else{
            	$equipment_tabs .= 0;
            }

            $equipment_tabs .= ' </strong><br/>
                No. of Submissions : <strong>';

                if($submissions){
                	$equipment_tabs .= $submissions->submissions_count;
                }else{
                	$equipment_tabs .= 0;
                }

            
            $equipment_tabs .= ' </strong><br/>
                No. of Passes : <strong>';

            $equipment_tabs .= $passed;

            $equipment_tabs .= ' </strong><br/>
                No. of Failed : <strong>';

            $equipment_tabs .= $failed;

            $equipment_tabs .= ' </strong><br/>
				            </div>
				        </div>
				    </div>
				</div>';
            
            $equipment_tabs .= '<div class = "row">
				  
						<div class="col-md-6">
							<div class = "card">
					            <div class="card-header col-6">
					            	<i class = "icon-chart"></i>
				                &nbsp;

				                    NHRL Results
					            </div>
					            <div class = "card-block">';

            $equipment_tabs .= $this->createNHRLTable($round_id, $equipment_id);

            $equipment_tabs .= '</div>
						    </div>
					    </div>

						<div class="col-md-6">
							<div class = "card ">
					            <div class="card-header col-6">
					            	<i class = "icon-chart"></i>
				                &nbsp;
				                Peer Results
					            </div>
					            <div class = "card-block">';

            $equipment_tabs .= $this->createPeerTable($round_id, $equipment_id);

            $equipment_tabs .= '</div>
						    </div>
					    </div>
				</div>


				<div class = "row">
				    <div class="col-md-12">
				        <div class = "card card-outline-danger">
				            <div class="card-header col-4">
				                <i class = "icon-chart"></i>
				                &nbsp;
				                    Participant Results
				            </div>

				            <div class = "card-block col-12">';

            $equipment_tabs .= $this->createParticipantTable($round_id, $equipment_id);

            $equipment_tabs .= '</div>


				        </div>
				    
					</div>
			    </div>
			</div>';
               
        }

        $equipment_tabs .= "</div>";
  // echo "<pre>";print_r($equipment_tabs);echo "</pre>";die();
        return $equipment_tabs;

    }

	







}

/* End of file Analysis.php */
/* Location: ./application/modules/Home/controllers/Analysis.php */
