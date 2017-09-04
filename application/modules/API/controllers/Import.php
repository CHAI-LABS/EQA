<?php

class Import extends DashboardController{
	function __construct()
	{
		parent::__construct();
		$this->load->library('Excel');
	}

	// pharmacies sub section


function importPTEquipmentResultData(){
		$file_path = './uploads/data/R17_Results.xlsx';

		$data = $this->excel->readExcel($file_path);
		if (count($data) > 0) {
			foreach ($data as $item => $itemData) {

				// echo "<pre>"; print_r($itemData);echo "</pre>";die();
				$headers = $itemData[0];
				
				$insertData = $datas = [];
				
				$count = 1;
				for ($i=4; $i < 72; $i++) {
						// echo "<pre>"; print_r(count($itemData));echo "</pre>";die();
						$counter = 1;

							$insertdata1 = [
								'equip_result_id'    =>  $count,
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
			            		'equip_result_id'    =>  $count,
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
			            		'equip_result_id'    =>  $count,
				                'sample_id'    =>  $counter,
				                'cd3_absolute'    =>  $itemData[$i][18] ? $itemData[$i][18] : 0,
				                'cd3_percent'    =>  $itemData[$i][19] ? $itemData[$i][19] : 0,
				                'cd4_absolute'    =>  $itemData[$i][20] ? $itemData[$i][20] : 0,
				                'cd4_percent'    =>  $itemData[$i][21] ? $itemData[$i][21] : 0,
				                'other_absolute'    =>  0,
				                'other_percent'    =>  0
			            	];

			            	$this->db->insert('pt_equipment_results', $insertdata3);
						

						
						$count ++;
				}

				echo "<pre>"; print_r("Check your DB to view TABLE -> pt_equipment_results");echo "</pre>";die();



			}
		}
	}


	function importPTDataSubmissionData(){
		$file_path = './uploads/data/R17_Results.xlsx';

		$data = $this->excel->readExcel($file_path);
		if (count($data) > 0) {
			foreach ($data as $item => $itemData) {

				// echo "<pre>"; print_r($itemData);echo "</pre>";die();
				$headers = $itemData[0];
				
				$insertData = $datas = [];
				
				$count = 1;
				for ($i=4; $i < 72; $i++) {
						// echo "<pre>"; print_r($itemData[$i]);echo "</pre>";die();

					if($itemData[$i][7] == 0 || $itemData[$i][7] == null){
						$equip_id = 0;
					}else{
						$equip_id = $this->db->get_where('equipment', ['equipment_name'=>$itemData[$i][7]])->row()->id;
					}

						// echo "<pre>"; print_r($equip_id);echo "</pre>";die();

						$insertdata = [
								'round_id'    =>  1,
				                'participant_id'    =>  $count,
				                'equipment_id'    =>  $equip_id,
				                'status'    =>  1,
				                'verdict'    =>  2
			            	];

			            	$this->db->insert('pt_data_submission', $insertdata);
						$count ++;
				}

				echo "<pre>"; print_r("Check your DB to view TABLE pt_data_submission results");echo "</pre>";die();



			}
		}
	}


}