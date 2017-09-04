<?php

class Import extends DashboardController{
	function __construct()
	{
		parent::__construct();
		$this->load->library('Excel');
	}

	// pharmacies sub section

	function importPTDataSubmissionData(){
		$file_path = './uploads/data/R17_Results.xlsx';

		$data = $this->excel->readExcel($file_path);
		if (count($data) > 0) {
			foreach ($data as $item => $itemData) {

				// echo "<pre>"; print_r($itemData);echo "</pre>";die();
				$headers = $itemData[0];
				
				$insertData = $datas = [];
				
				
				for ($i=4; $i < 72; $i++) {

					if($itemData[$i][7] != '' && $itemData[$i][7] != null && $itemData[$i][7] != 'not indicated'){
						
						$equip = $this->db->get_where('equipment', ['equipment_name'=>$itemData[$i][7]])->row();
						if($equip){
							$equip_id = $equip->id;
						}else{
							$equip_id = 0;
						}

						$facility = $this->db->get_where('facility_v', ['facility_name'=>$itemData[$i][0]])->row();
						if($facility){
							$facility_id = $facility->facility_id;
						}else{
							$facility_id = 0;
						}
						

						$insertdata = [
								'round_id'    =>  1,
				                'participant_id'    =>  $facility_id,
				                'equipment_id'    =>  $equip_id,
				                'status'    =>  1,
				                'verdict'    =>  2
			            ];

			            // $this->db->insert('pt_data_submission', $insertdata);
						$counter = 1;
			            if($this->db->insert('pt_data_submission', $insertdata)){
                        $submission_id = $this->db->insert_id();

                        $insertdata1 = [
								'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $counter,
				                'cd3_absolute'    =>  $itemData[$i][10] ? $itemData[$i][10] : 0,
				                'cd3_percent'    =>  $itemData[$i][11] ? $itemData[$i][11] : 0,
				                'cd4_absolute'    =>  $itemData[$i][12] ? $itemData[$i][12] : 0,
				                'cd4_percent'    =>  $itemData[$i][13] ? $itemData[$i][13] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata1);
			            	$counter ++;
			            	

			            	$insertdata2 = [
			            		'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $counter,
				                'cd3_absolute'    =>  $itemData[$i][14] ? $itemData[$i][14] : 0,
				                'cd3_percent'    =>  $itemData[$i][15] ? $itemData[$i][15] : 0,
				                'cd4_absolute'    =>  $itemData[$i][16] ? $itemData[$i][16] : 0,
				                'cd4_percent'    =>  $itemData[$i][17] ? $itemData[$i][17] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata2);
			            	$counter ++;
			            	

			            	$insertdata3 = [
			            		'equip_result_id'    =>  $submission_id,
				                'sample_id'    =>  $counter,
				                'cd3_absolute'    =>  $itemData[$i][18] ? $itemData[$i][18] : 0,
				                'cd3_percent'    =>  $itemData[$i][19] ? $itemData[$i][19] : 0,
				                'cd4_absolute'    =>  $itemData[$i][20] ? $itemData[$i][20] : 0,
				                'cd4_percent'    =>  $itemData[$i][21] ? $itemData[$i][21] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata3);


                   		}
					}
				}

				echo "<pre>"; print_r("Check your DB to view TABLE -> pt_data_submission and pt_equipment_results");echo "</pre>";die();



			}
		}
	}

}